<?php

namespace App\Http\Controllers\BillingReport;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use IBT\Reports\Dto\Enum\ReportStatusDto;
use IBT\Reports\Dto\Enum\ReportTypeDto;
use IBT\Reports\Dto\GenerateReportForm;
use IBT\Reports\Services\ReportService\ReportService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\MerchantSettingDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingReportController extends Controller
{
    /**
     * Скачать биллинговый отчет
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function billingReportDownload(
        string $type,
        int $entityId,
        int $reportId,
        FileService $fileService,
        ReportService $reportService
    ): ?StreamedResponse {
        $this->canView($this->getPermissionBlockByType($type));

        $report = $reportService->report($reportId);

        if (!$report || !isset($report['file_id'])) {
            return null;
        }

        $reportDto = $fileService->getFiles([$report['file_id']])->first();
        if (!$reportDto) {
            return null;
        }

        $domain = config('common-lib.showcaseHost');

        return response()->streamDownload(function () use ($reportDto, $domain) {
            echo file_get_contents($domain . $reportDto->url);
        }, $reportDto->original_name);
    }

    /**
     * Сохранить биллинговый период
     */
    public function billingCycle(
        string $type,
        int $entityId,
        Request $request,
        MerchantService $merchantService
    ): Response {
        $data = $this->validate($request, [
            'billing_cycle' => ['integer'],
        ]);

        if ($type === ReportTypeDto::REFERRAL_PARTNER) { //TODO:: Убрать после добавления настройки биллингового периода
            return response('', 204);
        }
        $this->canUpdate($this->getPermissionBlockByType($type));
        $merchantService->setSetting($entityId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);

        return response('', 204);
    }

    /**
     * Создать биллинговый отчет
     */
    public function billingReportCreate(
        string $type,
        int $entityId,
        Request $request,
        ReportService $reportService
    ): Response {
        $data = $this->validate($request, [
            'date_from' => ['date'],
            'date_to' => ['date'],
        ]);
        $this->canUpdate($this->getPermissionBlockByType($type));

        $reportFormParams = [
            'type' => $type,
            'entity_id' => $entityId,
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
        ];

        $reportForm = new GenerateReportForm($reportFormParams);

        $reportService->generate($reportForm);

        return response('', 204);
    }

    /**
     * Удалить биллинговый отчет
     * @return Application|ResponseFactory|JsonResponse|Response
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function deleteBillingReport(string $type, int $entityId, int $reportId, ReportService $reportService)
    {
        $this->canUpdate($this->getPermissionBlockByType($type));

        $reportService->delete($reportId);

        return response('', 204);
    }

    /**
     * Обновить статус у биллингового отчета
     * @return Application|Response|ResponseFactory
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function billingReportStatusUpdate(
        string $type,
        int $entityId,
        int $reportId,
        Request $request,
        ReportService $reportService
    ) {
        $data = $this->validate($request, [
            'status' => ['string', 'required', Rule::in(ReportStatusDto::getAll())],
        ]);
        $this->canUpdate($this->getPermissionBlockByType($type));

        $reportService->updateStatus($reportId, $data['status']);

        return response('', 204);
    }

    /**
     * Получить биллинговые отчеты
     * @throws Exception
     */
    public function billingReports(
        string $type,
        int $entityId,
        Request $request,
        ReportService $reportService
    ): JsonResponse {
        $this->canView($this->getPermissionBlockByType($type));

        $restQuery = $this->makeRestQuery($type, $request, $entityId);
        $billingReports = $reportService->reports($restQuery);

        return response()->json([
            'billing_reports' => $billingReports,
        ]);
    }

    public function load(string $type, int $entityId, MerchantService $merchantService): JsonResponse
    {
        $this->canView($this->getPermissionBlockByType($type));

        if ($type === ReportTypeDto::REFERRAL_PARTNER) { //TODO:: Убрать после добавления настройки биллингового периода
            return response()->json([
                'billing_cycle' => MerchantSettingDto::DEFAULT_BILLING_CYCLE,
            ]);
        }

        $settings = $merchantService->getSetting($entityId, MerchantSettingDto::BILLING_CYCLE)->first();
        $billingCycle = $settings ? $settings->value : null;

        return response()->json([
            'billing_cycle' => (int) $billingCycle,
        ]);
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(string $type, Request $request, int $entityId): RestQuery
    {
        $page = $request->get('page', 1);

        return (new RestQuery())
            ->pageNumber($page, 15)
            ->setFilter('entity_id', $entityId)
            ->setFilter('type', $type)
            ->addSort('id', 'desc');
    }

    private function getPermissionBlockByType(string $type): int
    {
        switch ($type) {
            case ReportTypeDto::BILLING:
            case ReportTypeDto::PUBLIC_EVENTS:
                return BlockDto::ADMIN_BLOCK_MERCHANTS;
            case ReportTypeDto::REFERRAL_PARTNER:
                return BlockDto::ADMIN_BLOCK_REFERRALS;
            default:
                abort(403, 'Недостаточно прав');
        }
    }
}
