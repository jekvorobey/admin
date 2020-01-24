<?php

namespace App\Http\Controllers\Communications;


use App\Http\Controllers\Controller;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;

class ChatsController extends Controller
{
    public function unread(
        CommunicationService $communicationService,
        CommunicationStatusService $communicationStatusService,
        CommunicationTypeService $communicationTypeService
    )
    {
        $this->title = 'Непрочитанные сообщения';

        $channels = $communicationService->channels()->keyBy('id');
        $statuses = $communicationStatusService->statuses()->keyBy('id');
        $types = $communicationTypeService->types()->keyBy('id');
        return $this->render('Communication/ChatsUnread', [
            'channels' => $channels,
            'statuses' => $statuses,
            'types' => $types,
        ]);
    }

    public function filter(CommunicationService $communicationService)
    {
        $chats = $communicationService->chats();
        return response()->json([
            'chats' => $chats,
        ]);
    }

    public function unreadCount(CommunicationService $communicationService)
    {
        $count = $communicationService->unreadCount();
        return response()->json([
            'count' => $count
        ]);
    }
}