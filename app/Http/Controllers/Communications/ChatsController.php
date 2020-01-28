<?php

namespace App\Http\Controllers\Communications;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;
use Greensight\Message\Services\CommunicationService\Constructors\ListConstructor;

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
        $listConstructor = $communicationService->chats();
        if (request('theme')) {
            $listConstructor->setTheme(request('theme'));
        }
        if (request('channel_id')) {
            $listConstructor->setChannelIds([request('channel_id')]);
        }
        if (request('status_id')) {
            $listConstructor->setStatusIds([request('status_id')]);
        }
        if (request('type_id')) {
            $listConstructor->setTypeIds([request('type_id')]);
        }
        if (!is_null(request('unread_admin'))) {
            $listConstructor->setUnreadAdmin((bool)request('unread_admin'));
        }

        [$chats, $users, $files] = $this->loadChats($listConstructor);

        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
        ]);
    }

    public function unreadCount(CommunicationService $communicationService)
    {
        $count = $communicationService->unreadCount();
        return response()->json([
            'count' => $count
        ]);
    }

    public function read(CommunicationService $communicationService)
    {
        $communicationService->readChat(request('id'), 0);
        return response()->json([], 204);
    }

    public function send(CommunicationService $communicationService, RequestInitiator $user)
    {
        $chatIds = request('chat_ids');
        $communicationService->createMessage($chatIds, $user->userId(), request('message'), request('files'));

        [$chats, $users, $files] = $this->loadChats($communicationService->chats()->setIds($chatIds));
        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
        ]);
    }

    protected function loadChats(ListConstructor $constructor)
    {
        $userService = resolve(UserService::class);
        $fileService = resolve(FileService::class);

        $chats = $constructor->load();

        $userIds = [];
        $fileIds = [];
        foreach ($chats as $chat) {
            $userIds[$chat->user_id] = $chat->user_id;
            foreach ($chat->messages as $message) {
                $userIds[$message->user_id] = $message->user_id;
                foreach ($message->files as $file) {
                    $fileIds[$file] = $file;
                }
            }
        }

        if ($userIds) {
            $users = $userService
                ->users($userService
                    ->newQuery()
                    ->include('profile')
                    ->setFilter('id', 'in', array_values($userIds))
                )
                ->keyBy('id');
        } else {
            $users = [];
        }

        if ($fileIds) {
            $files = $fileService
                ->getFiles($fileIds)
                ->keyBy('id')
                ->map(function (FileDto $file) {
                    return [
                        'name' => $file->original_name,
                        'url' => $file->absoluteUrl(),
                    ];
                });
        } else {
            $files = [];
        }

        return [$chats, $users, $files];
    }
}