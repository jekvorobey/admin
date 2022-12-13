<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * @return mixed
     */
    public function competition(): View
    {
        $this->canView(BlockDto::ADMIN_ANALYTICS_REPORT_GROUP_1);

        $this->title = 'Конкуренция';

        return $this->render('Analytics/CompetitionList');
    }

    /**
     * @return mixed
     */
    public function dumpOrders(): View
    {
        $this->canView(BlockDto::ADMIN_ANALYTICS_REPORT_GROUP_1);

        $this->title = 'Массив заказов';

        return $this->render('Analytics/DumpOrders');
    }
}