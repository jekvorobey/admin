<?php

namespace App\Http\Controllers\BillingReport;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
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
        if (!$report || !isset($report->file_id)) {
            return null;
        }

        /** @var FileDto|null $reportFile */
        $reportFile = $fileService->getFiles([$report->file_id])->first();
        if (!$reportFile) {
            return null;
        }

        return response()->streamDownload(function () use ($reportFile) {
            echo file_get_contents($reportFile->absoluteUrl());
        }, $reportFile->original_name);
    }

    /**
     * Сохранить биллинговый период
     * TODO: сделать общую настройку в reports-lib, пока работая только для типа BILLING
     */
    public function billingCycle(
        string $type,
        int $entityId,
        Request $request,
        MerchantService $merchantService
    ): Response {
        $this->canUpdate($this->getPermissionBlockByType($type));

        $data = $this->validate($request, [
            'billing_cycle' => ['integer'],
        ]);

        if ($type === ReportTypeDto::BILLING) {
            $merchantService->setSetting($entityId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);
        }

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
        $this->canUpdate($this->getPermissionBlockByType($type));

        $data = $this->validate($request, [
            'date_from' => ['date'],
            'date_to' => ['date'],
        ]);

        $reportForm = new GenerateReportForm();
        $reportForm->type = $type;
        $reportForm->entity_id = $entityId;
        $reportForm->date_from = $data['date_from'];
        $reportForm->date_to = $data['data_to'];

        $reportService->generate($reportForm);

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
        $this->canUpdate($this->getPermissionBlockByType($type));

        $data = $this->validate($request, [
            'status' => ['string', 'required', Rule::in(ReportStatusDto::getAll())],
        ]);

        $reportService->updateStatus($reportId, $data['status']);

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

    public function load(string $type, int $entityId, MerchantService $merchantService): JsonResponse
    {
        $this->canView($this->getPermissionBlockByType($type));

        if ($type === ReportTypeDto::BILLING) {
            $settings = $merchantService->getSetting($entityId, MerchantSettingDto::BILLING_CYCLE)->first();
        }

        $billingCycle = $settings->value ?? null;

        return response()->json([
            'billing_cycle' => (int) $billingCycle,
        ]);
    }

    /**
     * @throws Exception
     */
    private function makeRestQuery(string $type, Request $request, int $entityId): RestQuery
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
                return 0;
        }
    }
}
