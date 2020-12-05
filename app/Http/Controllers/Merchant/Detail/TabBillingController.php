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
use phpDocumentor\Reflection\Types\False_;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TabBillingController extends Controller
{
    /**
     * @param int             $merchantId
     * @param MerchantService $merchantService
     *
     * @return JsonResponse
     */
    public function load(int $merchantId, MerchantService $merchantService)
    {
        $settings = $merchantService->getSetting($merchantId, MerchantSettingDto::BILLING_CYCLE)->first();
        $billingCycle = $settings ? $settings->value : MerchantSettingDto::DEFAULT_BILLING_CYCLE;
        return response()->json([
            'billing_cycle' => (int)$billingCycle,
        ]);
    }

    /**
     * Сохранить биллинговый период
     * @param int $merchantId
     * @param MerchantService $merchantService
     * @return Application|ResponseFactory|Response
     */
    public function billingCycle(int $merchantId, MerchantService $merchantService)
    {
        $data = $this->validate(request(),[
            'billing_cycle' => 'integer|gt:0'
        ]);
        $merchantService->setSetting($merchantId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);
        return response('', 204);
    }

    /**
     * Получить биллинговые отчеты
     * @param Request $request
     * @param int $merchantId
     * @param MerchantService $merchantService
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
     * @param int $merchantId
     * @param int $reportId
     * @param MerchantService $merchantService
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function deleteBillingReport(int $merchantId, int $reportId, MerchantService $merchantService)
    {
        $merchantService->deleteBillingReport($merchantId, $reportId);
        return response('', 204);
    }

    /**
     * Обновить статус у биллингового отчета
     * @param int $merchantId
     * @param int $reportId
     * @param Request $request
     * @param MerchantService $merchantService
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function billingReportStatusUpdate(int $merchantId, int $reportId, Request $request, MerchantService $merchantService)
    {
        $status = $request->status;

        $merchantService->billingReportStatusUpdate($merchantId, $reportId, $status);
        return response('', 204);
    }

    /**
     * Создать биллинговый отчет
     * @param int $merchantId
     * @param Request $request
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function billingReportCreate(int $merchantId, Request $request)
    {
        $dates = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to
        ];

        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $merchantService->billingReportCreate($merchantId, $dates);
        return response('', 204);
    }

    /**
     * Создать биллинговый отчет
     * @param int $merchantId
     * @param int $reportId
     * @param FileService $fileService
     * @param MerchantService $merchantService
     * @return StreamedResponse
     */
    public function billingReportDownload(int $merchantId, int $reportId, FileService $fileService, MerchantService $merchantService)
    {
        $report = $merchantService->getBillingReport($merchantId, $reportId);

        if (!$report || !isset($report['file'])) return null;
        $reportFileId = $report['file'];

        $reportDto = $fileService->getFiles([$reportFileId])->first();
        if (!$reportDto) return null;

        if ($report['status'] == 0 ) {
            $merchantService->billingReportStatusUpdate($merchantId, $reportId, 1);
        }

        return response()->streamDownload(function () use ($reportDto) {
            echo file_get_contents($reportDto->absolute_url);
        }, $reportDto->original_name);
    }

    /**
     * AJAX добавление корректировки биллинга
     *
     * @param int $merchantId
     * @param Request $request
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function addCorrection(int $merchantId, Request $request)
    {
        $data = $this->validate(request(),[
            'correction_sum' => 'integer',
            'document_id' => 'integer',
            'date' => 'date',
        ]);

        $merchantService = resolve(MerchantService::class);
        $merchantService->addCorrection($merchantId, $data);

        return response('', 204);
    }

     /**
     * AJAX пагинация списка операций биллинга
     *
     * @param int $merchantId
     * @param Request $request
     * @param  MerchantService $merchantService
     * @return JsonResponse
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
     * @param DataQuery $restQuery
     * @return array
     */
    protected function loadBillingList($merchantId, DataQuery $restQuery): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        return $merchantService->merchantBillingList($restQuery, $merchantId);
    }

    /**
     * @param Request $request
     * @param int $merchantId
     * @return DataQuery
     * @throws Exception
     */
    protected function makeRestQuery( Request $request, int $merchantId ): DataQuery
    {
        $page = $request->get('page', 1);
        $restQuery = (new RestQuery())
            ->pageNumber($page, 15)
            ->setFilter('merchant_id', $merchantId);

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
                                $restQuery->setFilter($key, '<=', (new \Illuminate\Support\Carbon($value[1]))->modify('+1 day')->format('Y-m-d'));
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
        return Validator::make(request('filter') ?? [],
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
