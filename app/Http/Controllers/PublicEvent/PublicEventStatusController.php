<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Pim\Services\PublicEventStatusService\PublicEventStatusService;

class PublicEventStatusController extends Controller
{
    public function index(PublicEventStatusService $publicEventStatusService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        return response()->json([
            'statuses' => $publicEventStatusService->get()['statuses'],
        ]);
    }

    public function event(PublicEventStatusService $publicEventStatusService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        return response()->json([
            'statuses' => $publicEventStatusService->event()['statuses'],
        ]);
    }
}
