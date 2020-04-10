<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Pim\Dto\PublicEvent\PublicEventDto;
use Pim\Services\PublicEventService\PublicEventService;

class PublicEventDetailController extends Controller
{
    public function index($event_id, PublicEventService $publicEventService)
    {
        /** @var PublicEventDto $publicEvent */
        $publicEvent = $publicEventService
            ->query()
            ->setFilter('id', $event_id)
            ->withOrganizer()
            ->withActualSprint()
            ->withSprintTicketsCount()
            ->withPlace()
            ->get()
            ->first();
        
        return $this->render('PublicEvent/PublicEventDetail', [
            'iPublicEvent' => $publicEvent
        ]);
    }
}