<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use Greensight\Message\Services\CommunicationService\CommunicationService;

class ChannelController extends Controller
{
    public function channels(CommunicationService $communicationService)
    {
        return response()->json([
            'channels' => $communicationService->channels()
        ]);
    }
}