<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use IBT\Reports\Dto\GenerateReportForm;
use IBT\Reports\Dto\ReportDto;
use IBT\Reports\Services\ReportService\ReportService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MerchantManagement\Dto\MerchantSettingDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingController extends Controller
{
    /**
     * Скачать биллинговый отчет
     */
    public function billingReportDownload(
        int $entityId,
        int $reportId,
        Request $request,
        FileService $fileService,
        ReportService $reportService
    ): ?StreamedResponse {
        $this->checkViewRights($request->type);

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
    public function billingCycle(int $entityId, Request $request, MerchantService $merchantService): Response
    {
        $this->checkUpdateRights($request->type);

        $data = $this->validate(request(), [
            'billing_cycle' => 'integer',
        ]);
        $merchantService->setSetting($entityId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);

        return response('', 204);
    }

    /**
     * Создать биллинговый отчет
     */
    public function billingReportCreate(int $entityId, Request $request, ReportService $reportService): Response
    {
        $this->checkUpdateRights($request->type);

        $reportFormParams = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'type' => $request->type,
            'entity_id' => $entityId,
        ];

        $reportForm = new GenerateReportForm($reportFormParams);

        $reportService->generate($reportForm);

        return response('', 204);
    }

    /**
     * Удалить биллинговый отчет
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function deleteBillingReport(int $entityId, int $reportId, Request $request, ReportService $reportService)
    {
        $this->checkUpdateRights($request->type);

        $reportService->delete($reportId);

        return response('', 204);
    }

    /**
     * Обновить статус у биллингового отчета
     * @return Application|Response|ResponseFactory
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function billingReportStatusUpdate(
        int $entityId,
        int $reportId,
        Request $request,
        ReportService $reportService
    ) {
        $this->checkUpdateRights($request->type);

        $reportForm = new GenerateReportForm(['status' => $request->status]);
        $reportService->update($reportId, $reportForm);

        return response('', 204);
    }

    /**
     * Получить биллинговые отчеты
     * @throws \Exception
     */
    public function billingReports(Request $request, int $entityId, ReportService $reportService): JsonResponse
    {
        $this->checkViewRights($request->type);

        $restQuery = $this->makeRestQuery($request, $entityId);
        $billingReports = $reportService->reports($restQuery);
        $billingReports->transform(static function (ReportDto $report): ReportDto {
            $reportUpdated = clone $report;
            $reportUpdated->date_from = Carbon::parse($report->date_from)->format('d.m.Y');
            $reportUpdated->date_to = Carbon::parse($report->date_to)->format('d.m.Y');
            $reportUpdated->created_at = Carbon::parse($report->created_at)->format('d.m.Y H:i:s');
            $reportUpdated->updated_at = Carbon::parse($report->updated_at)->format('d.m.Y H:i:s');

            return $reportUpdated;
        });

        return response()->json([
            'billing_reports' => $billingReports,
        ]);
    }

    public function load(int $entityId, Request $request, MerchantService $merchantService): JsonResponse
    {
        $this->checkViewRights($request->type);

        $settings = $merchantService->getSetting($entityId, MerchantSettingDto::BILLING_CYCLE)->first();
        $billingCycle = $settings ? $settings->value : null;

        return response()->json([
            'billing_cycle' => (int) $billingCycle,
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function makeRestQuery(Request $request, int $entityId): RestQuery
    {
        $page = $request->get('page', 1);
        $type = $request->type;

        return (new RestQuery())
            ->pageNumber($page, 15)
            ->setFilter('entity_id', $entityId)
            ->setFilter('type', $type)
            ->addSort('id', 'desc');
    }

    private function checkViewRights(string $type): bool
    {
        switch ($type) {
            case 'BILLING':
            case 'PUBLIC_EVENTS':
                return $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);
            case 'REFERRAL_PARTNER':
                return $this->canView(BlockDto::ADMIN_BLOCK_REFERRALS);
            default:
                abort(403, 'Недостаточно прав');
        }
    }

    private function checkUpdateRights(string $type): bool
    {
        switch ($type) {
            case 'BILLING':
            case 'PUBLIC_EVENTS':
                return $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);
            case 'REFERRAL_PARTNER':
                return $this->canUpdate(BlockDto::ADMIN_BLOCK_REFERRALS);
            default:
                abort(403, 'Недостаточно прав');
        }
    }
}
