<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use IBT\Reports\Dto\Enum\ReportStatusDto;
use IBT\Reports\Dto\Enum\ReportTypeDto;
use IBT\Reports\Services\ReportService\ReportService;
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
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $this->loadBillingReportStatuses = true;
        $this->loadBillingReportTypes = true;

        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        /** @var ReportService $reportService */
        $reportService = resolve(ReportService::class);

        $query = $this->makeRestQuery();
        $payQuery = $this->makeQuery();

        return $this->render('Merchant/Settlements', [
            'iBillingReports' => $this->loadItems($query),
            'iPager' => is_null($query) ? 0 : $reportService->count($query),
            'iCurrentPage' => request()->get('page', 1),
            'iFilter' => request()->get('filter', []),
            'merchants' => $this->getMerchants(),

            'iPayRegisters' => $this->getPayRegistry(),
            'iPayRegistersPager' => is_null($payQuery) ? 0 : $merchantService->payRegistryCount($payQuery),
            'iPayRegistersCurrentPage' => request()->get('payRegistersPage', 1),
        ]);
    }

    public function page(ReportService $reportService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $query = $this->makeRestQuery();
        $data = [
            'items' => $this->loadItems($query),
        ];
        if (request()->get('page', 1) == 1) {
            $data['pager'] = is_null($query) ? 0 : $reportService->count($query);
        }

        return response()->json($data);
    }

    protected function loadItems(RestQuery $query): array
    {
        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);

        return $reportService->reports($query)->all();
    }

    protected function makeRestQuery(): RestQuery
    {
        $page = request()->get('page', 1);

        $restQuery = (new RestQuery())
            ->pageNumber($page, 30)
            ->setFilter('type', ReportTypeDto::BILLING)
            ->addSort('id', 'desc');

        $filter = $this->getFilter();

        if (isset($filter['created_at']) && array_filter($filter['created_at'])) {
            $restQuery->setFilter('date_from', '>=', $filter['created_at'][0]);
            $restQuery->setFilter('date_to', '<=', end_of_day_filter($filter['created_at'][1]));
        }

        if (isset($filter['status'])) {
            $restQuery->setFilter('status', $filter['status']);
        } else {
            $restQuery->setFilter('status', [ReportStatusDto::ACCEPTED]);
        }

        if (isset($filter['merchant_id'])) {
            $restQuery->setFilter('entity_id', $filter['merchant_id']);
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
                'status' => 'string|someone',
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
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

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
     */
    public function createPayRegistry(Request $request, RequestInitiator $user): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        $merchantService->createPayRegistry($request->ids, $user->userId());

        return response('', 204);
    }

    /**
     * Удалить реестр выплат
     */
    public function deletePayRegistry(int $payRegistryId): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);
        $merchantService->deletePayRegistry($payRegistryId);

        return response('', 204);
    }

    /**
     * Скачать биллинговый отчет
     */
    public function downloadPayRegistry(int $registryFileId, FileService $fileService): ?StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $registryDto = $fileService->getFiles([$registryFileId])->first();
        if (!$registryDto) {
            return null;
        }

        return response()->streamDownload(function () use ($registryDto) {
            echo file_get_contents($registryDto->absoluteUrl());
        }, $registryDto->original_name);
    }
}
