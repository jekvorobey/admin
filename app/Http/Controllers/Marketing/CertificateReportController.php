<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Pim\Dto\Certificate\CertificateReportDto;
use Pim\Services\CertificateService\CertificateService;

class CertificateReportController extends Controller
{
    private function service(): CertificateService
    {
        return resolve(CertificateService::class);
    }

    public function create(): JsonResponse
    {
        $id = $this->service()->createReport();
        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function download($id)
    {
        /** @var CertificateReportDto $report */
        $report = $this->service()->reportQuery()->id($id)->reports()->first();

        if (!$report) {
            abort(404, 'Отчет не найден');
        }

        $report->sendToBrowser();
    }
}
