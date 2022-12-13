<?php

namespace App\Services\Analytics;

use DateTime;
use Exception;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\Oms\Services\AnalyticsService\AnalyticsApiService;
use Illuminate\Http\Request;
use Greensight\Oms\Services\AnalyticsService\DashboardsAnalyticsService;

class AnalyticsService
{
    public const ANALYTICS_DASHBOARD_VIEW_ROLES = [
        RoleDto::ROLE_ADMINISTRATOR,
        RoleDto::ROLE_CONTENT_MANAGER,
        RoleDto::ROLE_KAM,
        RoleDto::ROLE_LOGISTIC,
        RoleDto::ROLE_MANAGER_MK,
        RoleDto::ROLE_FINANCIER,
        RoleDto::ROLE_MARKETING_MANAGER,
        RoleDto::ROLE_SUPERVISER_KC,
        12 //RoleDto::ROLE_MARKETING, TODO Add to CommonMsa.RoleDto new role const ROLE_MARKETING = 12
    ];

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
        $start = (new DateTime($start))->format('Y-m-d 00:00:00');

        if (!$request->get('start')) {
            $end = $request->get('end', (new DateTime())->format('Y-m-d 23:59:59'));
        } else {
            $end = (new DateTime($start))->format('Y-m-d 23:59:59');
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
        $start = (new DateTime($start))->format('Y-m-01 00:00:00');

        if (!$request->get('start')) {
            $end = $request->get('end', (new DateTime())->format('Y-m-t 23:59:59'));
        } else {
            $end = (new DateTime($start))->format('Y-m-t 23:59:59');
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
        $start = (new DateTime($start))->format('Y-m-01 00:00:00');
        $end = $request->get('end');

        if (!$request->get('start') || $request->get('end')) {
            $end = $request->get('end', (new DateTime($end))->format('Y-m-t 23:59:59'));
        } else {
            $end = (new DateTime($start))->format('Y-m-t 23:59:59');
        }

        return $analyticsService->salesYearByMonth($start, $end);
    }

    /**
     * @throws Exception
     */
    public function competition(Request $request): array
    {
        /** @var AnalyticsApiService $analyticsService */
        $analyticsService = app(AnalyticsApiService::class);

        $start = $request->get('start');
        $start = (new DateTime($start))->format('Y-m-01 00:00:00');
        $end = $request->get('end');

        if (!$request->get('start') || $request->get('end')) {
            $end = $request->get('end', (new DateTime($end))->format('Y-m-t 23:59:59'));
        } else {
            $end = (new DateTime($start))->format('Y-m-t 23:59:59');
        }

        return $analyticsService->competition($start, $end);
    }

    /**
     * @throws Exception
     */
    public function dumpOrders(Request $request): array
    {
        /** @var AnalyticsApiService $analyticsService */
        $analyticsService = app(AnalyticsApiService::class);

        $orderBy = $request->get('$orderby');
        $count = (bool) $request->get('$count');
        $top = $request->get('$top');
        $skip = $request->get('$skip');
        $filter = $request->get('$filter');

        $createdStart = $request->get('createdStart');
        $createdStart = (new DateTime($createdStart))->format('Y-m-d 00:00:00');
        $createdEnd = $request->get('createdEnd');
        $createdEnd = (new DateTime($createdEnd))->format('Y-m-d 23:59:59');

        return $analyticsService->dumpOrders($createdStart, $createdEnd, $filter, $orderBy, $count, $top, $skip);
    }
}
