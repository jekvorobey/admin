<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Illuminate\Http\JsonResponse;

class ChannelController extends Controller
{
    public function channels(CommunicationService $communicationService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_COMMUNICATIONS);

        return response()->json([
            'channels' => $communicationService->channels(),
        ]);
    }
}
