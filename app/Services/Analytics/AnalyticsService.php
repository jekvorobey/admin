<?php

namespace App\Services\Analytics;

use Exception;
use Illuminate\Http\Request;
use Greensight\Oms\Services\AnalyticsService\DashboardsAnalyticsService;

class AnalyticsService
{
    public function salesAllPeriodByDay(): array
    {
        /** @var DashboardsAnalyticsService $analyticsService */
        $analyticsService = app(DashboardsAnalyticsService::class);

        return $analyticsService->salesAllPeriodByDay();
    }

    /**
     * @throws Exception
     */
    public function salesDayByHour(Request $request): array
    {
        /** @var DashboardsAnalyticsService $analyticsService */
        $analyticsService = app(DashboardsAnalyticsService::class);

        $start = $request->get('start');
        $start = (new \DateTime($start))->format('Y-m-d 00:00:00');

        if (!$request->get('start')) {
            $end = $request->get('end', (new \DateTime())->format('Y-m-d 23:59:59'));
        } else {
            $end = (new \DateTime($start))->format('Y-m-d 23:59:59');
        }

        return $analyticsService->salesDayByHour($start, $end);
    }

    /**
     * @throws Exception
     */
    public function salesMonthByDay(Request $request): array
    {
        /** @var DashboardsAnalyticsService $analyticsService */
        $analyticsService = app(DashboardsAnalyticsService::class);

        $start = $request->get('start');
        $start = (new \DateTime($start))->format('Y-m-01 00:00:00');

        if (!$request->get('start')) {
            $end = $request->get('end', (new \DateTime())->format('Y-m-t 23:59:59'));
        } else {
            $end = (new \DateTime($start))->format('Y-m-t 23:59:59');
        }

        return $analyticsService->salesMonthByDay($start, $end);
    }

    /**
     * @throws Exception
     */
    public function salesYearByMonth(Request $request): array
    {
        /** @var DashboardsAnalyticsService $analyticsService */
        $analyticsService = app(DashboardsAnalyticsService::class);

        $start = $request->get('start');
        $start = (new \DateTime($start))->format('Y-01-01 00:00:00');

        if (!$request->get('start')) {
            $end = $request->get('end', (new \DateTime())->format('Y-12-31 23:59:59'));
        } else {
            $end = (new \DateTime($start))->format('Y-12-31 23:59:59');
        }

        return $analyticsService->salesYearByMonth($start, $end);
    }
}
