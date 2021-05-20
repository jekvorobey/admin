<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\PublicEventSprintStatus;
use Pim\Dto\PublicEvent\PublicEventStatus;
use Pim\Services\PublicEventService\PublicEventService;
use Pim\Services\ShoppilotService\ShoppilotService;

class PublicEventListController extends Controller
{
    public function list(
        Request $request,
        PublicEventService $publicEventService,
        ShoppilotService $shoppilotService
    ) {
        $this->loadPublicEventStatus = true;
        $this->loadPublicEventSprintStatus = true;

        $page = $request->get('page', 1);
        $publicEvents = $this->loadPublicEvents($publicEventService, $page);

        $publicEventIds = $publicEvents->pluck('id')->all();
        if ($publicEventIds) {
            try {
                $shoppilotPublicEventsExist = $shoppilotService->groupsExist($publicEventIds);
                $publicEvents->transform(function ($publicEvent) use ($shoppilotPublicEventsExist) {
                    $publicEvent['shoppilotExist'] = $shoppilotPublicEventsExist[$publicEvent['id']];
                    return $publicEvent;
                });
            } catch (\Throwable $e) {
            }
        }

        return $this->render('PublicEvent/PublicEventList', [
            'iPublicEvents' => $publicEvents,
            'iCurrentPage' => $page,
            'iTotal' => $this->loadTotalCount($publicEventService),
            'options' => [
                'eventStatuses' => PublicEventStatus::all(),
                'sprintStatuses' => PublicEventSprintStatus::all(),
            ],
        ]);
    }

    public function page(
        Request $request,
        PublicEventService $publicEventService,
        ShoppilotService $shoppilotService
    ) {
        $page = $request->get('page', 1);
        $publicEvents = $this->loadPublicEvents($publicEventService, $page);

        $publicEventIds = $publicEvents->pluck('id')->all();
        if ($publicEventIds) {
            try {
                $shoppilotPublicEventsExist = $shoppilotService->groupsExist($publicEventIds);
                $publicEvents->transform(function ($publicEvent) use ($shoppilotPublicEventsExist) {
                    $publicEvent['shoppilotExist'] = $shoppilotPublicEventsExist[$publicEvent['id']];
                    return $publicEvent;
                });
            } catch (\Throwable $e) {
            }
        }

        return response()->json([
            'publicEvents' => $publicEvents,
            'total' => $this->loadTotalCount($publicEventService),
        ]);
    }

    public function load(PublicEventService $publicEventService)
    {
        return response()->json([
            'events' => $publicEventService->query()->get(),
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
            ->get()
            ->sortBy('actualSprint.date_start');
    }

    private function loadTotalCount(PublicEventService $publicEventService): int
    {
        $result = $publicEventService
            ->query()
            ->count();
        return $result['total'] ?? 0;
    }
}
