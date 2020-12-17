<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Greensight\Marketing\Dto\Certificate\Report;
use Greensight\Marketing\Dto\Certificate\ReportSearchQuery;
use Greensight\Marketing\Services\Certificate\ReportService;
use Illuminate\Http\JsonResponse;

class CertificateReportController extends Controller
{
    public function create(ReportService $reportService): JsonResponse
    {
        return response()->json($reportService->create());
    }

    public function download($id)
    {
        /** @var Report $report */
        $report = (new ReportSearchQuery())
            ->id($id)
            ->prepare(resolve(ReportService::class), 'reports')
            ->get()->items->first();

        if (!$report)
            abort(404, 'Отчет не найден');

        $filename = $report->getFileName();

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename={$filename}");

        echo $report->getCsvContent();
        exit(0);
    }
}
