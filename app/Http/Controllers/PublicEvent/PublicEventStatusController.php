<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Pim\Services\PublicEventStatusService\PublicEventStatusService;

class PublicEventStatusController extends Controller
{
    public function index(PublicEventStatusService $publicEventStatusService)
    {
        return response()->json([
            'statuses' => $publicEventStatusService->get()['statuses'],
        ]);
    }

    public function event(PublicEventStatusService $publicEventStatusService)
    {
        return response()->json([
            'statuses' => $publicEventStatusService->event()['statuses'],
        ]);
    }
}
