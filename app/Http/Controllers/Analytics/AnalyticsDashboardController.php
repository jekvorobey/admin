<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AnalyticsDashboardController extends Controller
{
    public function salesDayByHour(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $this->hasRole(AnalyticsService::ANALYTICS_DASHBOARD_VIEW_ROLES);

        $resultData = $analyticsService->salesDayByHour($request);

        return $this->getResponse($request, $resultData);
    }

    public function salesMonthByDay(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $this->hasRole(AnalyticsService::ANALYTICS_DASHBOARD_VIEW_ROLES);

        $resultData = $analyticsService->salesMonthByDay($request);

        return $this->getResponse($request, $resultData);
    }

    public function salesYearByMonth(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $this->hasRole(AnalyticsService::ANALYTICS_DASHBOARD_VIEW_ROLES);

        $resultData = $analyticsService->salesYearByMonth($request);

        return $this->getResponse($request, $resultData);
    }

    public function salesAllPeriodByDay(Request $request, AnalyticsService $analyticsService): JsonResponse
    {
        $this->hasRole(AnalyticsService::ANALYTICS_DASHBOARD_VIEW_ROLES);

        $resultData = $analyticsService->salesAllPeriodByDay();

        return $this->getResponse($request, $resultData);
    }

    private function getResponse(Request $request, ?array $resultData = []): JsonResponse
    {
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