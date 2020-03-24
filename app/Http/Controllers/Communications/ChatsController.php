<?php

namespace App\Http\Controllers\Communications;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Message\Dto\Communication\CommunicationChatDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationThemeService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;
use Greensight\Message\Services\CommunicationService\Constructors\ListConstructor;


class ChatsController extends Controller
{
    public function unread()
    {
        $this->title = 'Непрочитанные сообщения';
        return $this->render('Communication/ChatsUnread');
    }

    public function broadcast()
    {
        $this->loadCommunicationChannelTypes = true;
        $this->loadCommunicationChannels = true;
        $this->loadCommunicationThemes = true;
        $this->loadCommunicationStatuses = true;
        $this->loadCommunicationTypes = true;

        $this->title = 'Массовая рассылка';

        return $this->render('Communication/Broadcast', [
            'roles' => UserDto::rolesByFrontId([
                Front::FRONT_MAS,
                Front::FRONT_SHOWCASE,
            ]),
        ]);
    }

    public function filter(CommunicationService $communicationService)
    {
        $listConstructor = $communicationService->chats();
        if (request('user_id')) {
            $listConstructor->setUserIds([request('user_id')]);
        }
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

    public function create(CommunicationService $communicationService, RequestInitiator $user)
    {
        $userIds = request('user_ids');
        $chatIds = $communicationService->createChat(
            request('channel_id'),
            request('theme'),
            $userIds,
            CommunicationChatDto::DIRECTION_OUT,
            request('status_id'),
            request('type_id')
        );

        $message = request('message');
        $files = request('files');

        if ($message || $files) {
            $communicationService->createMessage($chatIds, $user->userId(), $message, $files);
        }

        [$chats, $users, $files] = $this->loadChats($communicationService->chats()->setIds($chatIds));
        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
        ]);
    }

    public function update(CommunicationService $communicationService, RequestInitiator $user)
    {
        $chatId = request('chat_id');

        $communicationService->updateChat(
            $chatId,
            request('theme'),
            request('status_id'),
            request('type_id')
        );

        [$chats, $users, $files] = $this->loadChats($communicationService->chats()->setIds([$chatId]));
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