<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Dto\MerchantSettingDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Carbon;

class TabBillingController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function load(int $merchantId, MerchantService $merchantService)
    {
        $settings = $merchantService->getSetting($merchantId, MerchantSettingDto::BILLING_CYCLE)->first();
        $billingCycle = $settings ? $settings->value : null;
        return response()->json([
            'billing_cycle' => (int) $billingCycle,
        ]);
    }

    /**
     * Сохранить биллинговый период
     * @return Application|ResponseFactory|Response
     */
    public function billingCycle(int $merchantId, MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'billing_cycle' => 'integer',
        ]);
        $merchantService->setSetting($merchantId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);
        return response('', 204);
    }

    /**
     * Сохранить биллинговый период
     * @return Application|ResponseFactory|Response
     */
    public function addReturn(int $merchantId, int $operationId, MerchantService $merchantService)
    {
        $merchantService->addReturn($merchantId, $operationId);
        return response('', 204);
    }

    /**
     * Сохранить биллинговый период
     * @return Application|ResponseFactory|Response
     */
    public function deleteOperation(int $merchantId, int $operationId, MerchantService $merchantService)
    {
        $merchantService->deleteOperation($merchantId, $operationId);
        return response('', 204);
    }

    /**
     * Получить биллинговые отчеты
     * @return JsonResponse
     * @throws Exception
     */
    public function billingReports(Request $request, int $merchantId, MerchantService $merchantService)
    {
        $restQuery = $this->makeRestQuery($request, $merchantId);
        $billingReports = $merchantService->merchantBillingReports($restQuery, $merchantId);
        return response()->json([
            'billing_reports' => $billingReports,
        ]);
    }

    /**
     * Удалить биллинговый отчет
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function deleteBillingReport(int $merchantId, int $reportId, MerchantService $merchantService)
    {
        $merchantService->deleteBillingReport($merchantId, $reportId);
        return response('', 204);
    }

    /**
     * Обновить статус у биллингового отчета
     * @return Application|ResponseFactory|JsonResponse|Response
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function billingReportStatusUpdate(
        int $merchantId,
        int $reportId,
        Request $request,
        MerchantService $merchantService
    ) {
        $status = $request->status;

        $merchantService->billingReportStatusUpdate($reportId, $status);
        return response('', 204);
    }

    /**
     * Создать биллинговый отчет
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function billingReportCreate(int $merchantId, Request $request)
    {
        $dates = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $merchantService->billingReportCreate($merchantId, $dates);
        return response('', 204);
    }

    /**
     * Скачать биллинговый отчет
     */
    public function billingReportDownload(
        int $merchantId,
        int $reportId,
        FileService $fileService,
        MerchantService $merchantService
    ): ?StreamedResponse {
        $report = $merchantService->getBillingReport($merchantId, $reportId);

        if (!$report || !isset($report['file'])) {
            return null;
        }
        $reportFileId = $report['file'];

        $reportDto = $fileService->getFiles([$reportFileId])->first();
        if (!$reportDto) {
            return null;
        }

        $domain = config('common-lib.showcaseHost');
        return response()->streamDownload(function () use ($reportDto, $domain) {
            echo file_get_contents($domain . $reportDto->url);
        }, $reportDto->original_name);
    }

    /**
     * Скачать документ о корректировке
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function correctionDownload(int $merchantId, int $fileId, FileService $fileService): ?StreamedResponse
    {
        $reportDto = $fileService->getFiles([$fileId])->first();
        $domain = config('common-lib.showcaseHost');

        return response()->streamDownload(function () use ($reportDto, $domain) {
            echo file_get_contents($domain . $reportDto->url);
        }, $reportDto->original_name);
    }

    /**
     * AJAX добавление корректировки биллинга
     *
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function addCorrection(int $merchantId, Request $request)
    {
        $data = $this->validate($request, [
            'correction_sum' => 'integer',
            'correction_type' => 'integer',
            'date' => 'date',
        ]);

        $merchantService = resolve(MerchantService::class);
        $merchantService->addCorrection($merchantId, $data);

        return response('', 204);
    }

     /**
     * AJAX пагинация списка операций биллинга
     *
     * @throws Exception
     */
    public function billingList(int $merchantId, Request $request, MerchantService $merchantService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($request, $merchantId);
        $billingList = $this->loadBillingList($merchantId, $restQuery);
        $result = [
            'billingList' => $billingList,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $merchantService->merchantBillingListCount($merchantId, $restQuery);
        }

        return response()->json($result);
    }

    /**
     * @param $merchantId
     * @return array
     */
    protected function loadBillingList($merchantId, DataQuery $restQuery): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        return $merchantService->merchantBillingList($restQuery, $merchantId);
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(Request $request, int $merchantId): DataQuery
    {
        $page = $request->get('page', 1);
        $restQuery = (new RestQuery())
            ->pageNumber($page, 15)
            ->setFilter('merchant_id', $merchantId)
            ->addSort('id', 'desc');

        $filter = $this->getFilter();
        if ($filter) {
            foreach ($filter as $key => $value) {
                if ($value) {
                    switch ($key) {
                        case 'commission_from':
                            $restQuery->setFilter('commission', '>=', $value);
                            break;
                        case 'commission_to':
                            $restQuery->setFilter('commission', '<=', $value);
                            break;
                        case 'discount_from':
                            $restQuery->setFilter('discount', '>=', $value);
                            break;
                        case 'discount_to':
                            $restQuery->setFilter('discount', '<=', $value);
                            break;
                        case 'price_from':
                            $restQuery->setFilter('price', '>=', $value);
                            break;
                        case 'price_to':
                            $restQuery->setFilter('price', '<=', $value);
                            break;
                        case 'name':
                            $restQuery->setFilter($key, 'like', "%{$value}%");
                            break;
                        case 'status_at':
                            $value = array_filter($value);
                            if ($value) {
                                $restQuery->setFilter($key, '>=', $value[0]);
                                $restQuery->setFilter($key, '<=', (new Carbon($value[1]))->modify('+1 day')->format('Y-m-d'));
                            }
                            break;
                        default:
                            $restQuery->setFilter($key, $value);
                    }
                }
            }
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
                'commission_from' => 'integer|someone',
                'commission_to' => 'integer|someone',
                'discount_from' => 'integer|someone',
                'discount_to' => 'integer|someone',
                'price_from' => 'integer|someone',
                'price_to' => 'integer|someone',
                'name' => 'string|someone',
                'status_at' => 'string|someone',
            ]
        )->attributes();
    }
}
