<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnalyticsDashboardController extends Controller
{
    public function saleAllPeriodByMonth(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $resultData = $analyticsService->saleAllPeriodByMonth($request);

        $callback = $request->get('$callback');
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        if (isset($callback) && $callback) {
            $response->setCallback($callback);
            $data = array(
                'd' => array(
                    'results' => $resultData,
                    '__count' => count($resultData),
                )
            );
        } else {
            $data = $resultData;
        }
        $response->setData($data ?? []);

        return $response;
    }

    public function saleLastYearByMonth(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $resultData = $analyticsService->saleLastYearByMonth($request);

        $callback = $request->get('$callback');
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        if (isset($callback) && $callback) {
            $response->setCallback($callback);
            $data = array(
                'd' => array(
                    'results' => $resultData,
                    '__count' => count($resultData),
                )
            );
        } else {
            $data = $resultData;
        }
        $response->setData($data ?? []);

        return $response;
    }

    public function saleLastMonthByDay(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $resultData = $analyticsService->saleLastMonthByDay($request);

        $callback = $request->get('$callback');
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        if (isset($callback) && $callback) {
            $response->setCallback($callback);
            $data = array(
                'd' => array(
                    'results' => $resultData,
                    '__count' => count($resultData),
                )
            );
        } else {
            $data = $resultData;
        }
        $response->setData($data ?? []);

        return $response;
    }
}