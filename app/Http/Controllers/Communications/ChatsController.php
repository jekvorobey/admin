<?php

namespace App\Http\Controllers\Communications;


use App\Http\Controllers\Controller;
use Greensight\Message\Services\CommunicationService\CommunicationService;

class ChatsController extends Controller
{
    public function unread()
    {

    }

    public function unreadCount(CommunicationService $communicationService)
    {
        $count = $communicationService->unreadCount();
        return response()->json([
            'count' => $count
        ]);
    }
}