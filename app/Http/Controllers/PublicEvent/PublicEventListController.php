<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\PublicEventSprintStatus;
use Pim\Dto\PublicEvent\PublicEventStatus;
use Pim\Services\PublicEventService\PublicEventService;

class PublicEventListController extends Controller
{
    public function page(Request $request, PublicEventService $publicEventService)
    {
        $this->loadPublicEventStatus = true;
        $this->loadPublicEventSprintStatus = true;

        $page = $request->get('page', 1);
        $publicEvents = $this->loadPublicEvents($publicEventService, $page);
        return $this->render('PublicEvent/PublicEventList', [
            'iPublicEvents' => $publicEvents,
            'iCurrentPage' => $page,
            'iTotal' => $this->loadTotalCount($publicEventService),
            'options' => [
                'eventStatuses' => PublicEventStatus::all(),
                'sprintStatuses' => PublicEventSprintStatus::all(),
            ]
        ]);
    }

    private function loadPublicEvents(PublicEventService $publicEventService, $page)
    {
        return $publicEventService
            ->query()
            ->pageNumber($page, 10)
            ->withActualSprint()
            ->withPlace()
            ->withSprintTicketsCount()
            ->get();
    }

    private function loadTotalCount(PublicEventService $publicEventService): int
    {
        $result = $publicEventService
            ->query()
            ->count();
        return $result['total'] ?? 0;
    }
}