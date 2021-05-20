<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MerchantSettlementsController extends Controller
{
    public function index()
    {
        $this->title = 'Взаиморасчёты';
        return $this->list();
    }

    protected function list()
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $query = $this->makeRestQuery();
        $payQuery = $this->makeQuery();

        return $this->render('Merchant/Settlements', [
            'iBillingReports' => $this->loadItems(),
            'iPager' => is_null($query) ? 0 : $merchantService->merchantBillingReportsCount($query),
            'iCurrentPage' => request()->get('page', 1),
            'iFilter' => request()->get('filter', []),
            'merchants' => $this->getMerchants(),

            'iPayRegisters' => $this->getPayRegistry(),
            'iPayRegistersPager' => is_null($payQuery) ? 0 : $merchantService->payRegistryCount($payQuery),
            'iPayRegistersCurrentPage' => request()->get('payRegistersPage', 1),
        ]);
    }

    public function page(MerchantService $merchantService): JsonResponse
    {
        $query = $this->makeRestQuery();
        $data = [
            'items' => $this->loadItems(),
        ];
        if (request()->get('page', 1) == 1) {
            $data['pager'] = is_null($query) ? 0 : $merchantService->merchantBillingReportsCount($query);
        }

        return response()->json($data);
    }

    protected function loadItems(): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $restQuery = $this->makeRestQuery();

        return $merchantService->merchantBillingReports($restQuery);
    }

    protected function makeRestQuery(): DataQuery
    {
        $page = request()->get('page', 1);
        $restQuery = (new RestQuery())
            ->pageNumber($page, 30)
            ->addSort('id', 'desc');

        $filter = $this->getFilter();

        if (isset($filter['created_at']) && array_filter($filter['created_at'])) {
            $restQuery->setFilter('date_from', '>=', $filter['created_at'][0]);
            $restQuery->setFilter('date_to', '<=', $filter['created_at'][1]);
        }

        if (isset($filter['status'])) {
            $restQuery->setFilter('status', $filter['status']);
        } else {
            $restQuery->setFilter('status', [2]);
        }

        if (isset($filter['merchant_id'])) {
            $restQuery->setFilter('merchant_id', $filter['merchant_id']);
        }

        return $restQuery;
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        return Validator::make(
            request('filter') ?? [],
            [
                'status' => 'integer|someone',
                'legal_name' => 'string|someone',
                'created_at' => 'string|someone',
            ]
        )->attributes();
    }

    /**
     * Получить реестр выплат
     * @return array
     */
    public function getPayRegistry(): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        $restQuery = $this->makeQuery();

        return $merchantService->payRegistry($restQuery);
    }

    public function getPayRegistryPage(MerchantService $merchantService): JsonResponse
    {
        $query = $this->makeQuery();
        $data = [
            'items' => $this->getPayRegistry(),
        ];
        if (request()->get('payRegistersPage', 1) == 1) {
            $data['iPayRegistersPager'] = is_null($query) ? 0 : $merchantService->payRegistryCount($query);
        }

        return response()->json($data);
    }

    protected function makeQuery(): DataQuery
    {
        $page = request()->get('payRegistersPage', 1);
        return (new RestQuery())
            ->pageNumber($page, 30)
            ->addSort('id', 'desc');
    }

    /**
     * Создать реестр выплат
     * @param Request $request
     * @param RequestInitiator $user
     * @return Response
     */
    public function createPayRegistry(Request $request, RequestInitiator $user): Response
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        $merchantService->createPayRegistry($request->ids, $user->userId());
        return response('', 204);
    }

    /**
     * Удалить реестр выплат
     * @param int $payRegistryId
     * @return Response
     */
    public function deletePayRegistry(int $payRegistryId): Response
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $merchantService->deletePayRegistry($payRegistryId);
        return response('', 204);
    }

    /**
     * Скачать биллинговый отчет
     * @param int $registryFileId
     * @param FileService $fileService
     * @return StreamedResponse
     */
    public function downloadPayRegistry(int $registryFileId, FileService $fileService): ?StreamedResponse
    {
        $registryDto = $fileService->getFiles([$registryFileId])->first();
        if (!$registryDto) {
            return null;
        }

        $domain = env('SHOWCASE_HOST');
        return response()->streamDownload(function () use ($registryDto, $domain) {
            echo file_get_contents($domain . $registryDto->url);
        }, $registryDto->original_name);
    }
}
