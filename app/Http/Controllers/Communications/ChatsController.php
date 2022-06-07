<?php

namespace App\Http\Controllers\Communications;

use App\Core\Helpers;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestClientException;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Message\Dto\Communication\CommunicationChatDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\Constructors\ListConstructor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use MerchantManagement\Dto\OperatorDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use MerchantManagement\Services\OperatorService\OperatorService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ChatsController extends Controller
{
    public function unread(MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_COMMUNICATIONS);

        $this->loadCommunicationChannelTypes = true;
        $this->loadCommunicationChannels = true;
        $this->loadCommunicationThemes = true;
        $this->loadCommunicationStatuses = true;
        $this->loadCommunicationTypes = true;

        $this->title = 'Непрочитанные сообщения';
        return $this->render('Communication/ChatsUnread', [
            'roles' => Helpers::getRoles([
                Front::FRONT_MAS,
                Front::FRONT_SHOWCASE,
            ]),
            'theme' => request('theme', ''),
            'merchants' => $merchantService->merchants(),
        ]);
    }

    public function broadcast(MerchantService $merchantService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_COMMUNICATIONS);

        $this->loadCommunicationChannelTypes = true;
        $this->loadCommunicationChannels = true;
        $this->loadCommunicationThemes = true;
        $this->loadCommunicationStatuses = true;
        $this->loadCommunicationTypes = true;

        $this->title = 'Массовая рассылка';

        return $this->render('Communication/Broadcast', [
            'roles' => Helpers::getRoles([
                Front::FRONT_MAS,
                Front::FRONT_SHOWCASE,
            ]),
            'merchants' => $merchantService->merchants(),
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RestClientException
     */
    public function filter(CommunicationService $communicationService): JsonResponse
    {
        $listConstructor = $communicationService->chats();
        if (request('user_ids')) {
            $listConstructor->setUserIds(request('user_ids'));
        }
        if (request('theme')) {
            $listConstructor->setTheme(request('theme'));
        }
        if (request('channel_ids')) {
            $listConstructor->setChannelIds(request('channel_ids'));
        }
        if (request('status_ids')) {
            $listConstructor->setStatusIds(request('status_ids'));
        }
        if (request('type_ids')) {
            $listConstructor->setTypeIds(request('type_ids'));
        }
        if (!is_null(request('unread_admin'))) {
            $listConstructor->setUnreadAdmin((bool) request('unread_admin'));
        }
        if (request('pageNumber')) {
            Log::info(print_r(request('pageNumber'), true));
            $listConstructor->setPage(request('pageNumber'));
        }

        [$chats, $users, $files, $customers, $operators, $pager] = $this->loadChats($listConstructor);

        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
            'customers' => $customers,
            'operators' => $operators,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
        ]);
    }

    public function unreadCount(CommunicationService $communicationService): JsonResponse
    {
        $count = $communicationService->unreadCount();

        return response()->json([
            'count' => $count,
        ]);
    }

    public function read(CommunicationService $communicationService): JsonResponse
    {
        $communicationService->readChat(request('id'), 0);

        return response()->json([], 204);
    }

    public function send(CommunicationService $communicationService, RequestInitiator $user): JsonResponse
    {
        $chatIds = request('chat_ids');
        $communicationService->createMessage($chatIds, $user->userId(), request('message'), request('files'));

        [$chats, $users, $files, $customers, $operators] = $this->loadChats($communicationService->chats()->setIds($chatIds));

        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
            'customers' => $customers,
            'operators' => $operators,
        ]);
    }

    public function create(CommunicationService $communicationService, RequestInitiator $user): JsonResponse
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

        [$chats, $users, $files, $customers, $operators] = $this->loadChats($communicationService->chats()->setIds($chatIds));

        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
            'customers' => $customers,
            'operators' => $operators,
        ]);
    }

    public function update(CommunicationService $communicationService): JsonResponse
    {
        $chatId = request('chat_id');

        $communicationService->updateChat(
            $chatId,
            request('theme'),
            request('status_id'),
            request('type_id')
        );

        [$chats, $users, $files, $customers, $operators] = $this->loadChats($communicationService->chats()->setIds([$chatId]));

        return response()->json([
            'chats' => $chats,
            'users' => $users,
            'files' => $files,
            'customers' => $customers,
            'operators' => $operators,
        ]);
    }

    /**
     * Привязываем пользователя к чату (LiveTex)
     * @throws RestClientException
     */
    public function updateChatUser(CommunicationService $communicationService): JsonResponse
    {
        $data = $this->validate(request(), [
            'chat_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $communicationService->updateChatUser($data['chat_id'], $data['user_id']);

        $listConstructor = $communicationService->chats();
        $listConstructor->setUserIds('null');
        $chats = $listConstructor->load();

        return response()->json([
            'chats' => $chats,
        ]);
    }

    /**
     * Список чатов из мессенджеров (LiveTex), которые не привязаны к пользователям
     *
     * @return mixed
     * @throws RestClientException
     */
    public function unlinkMessengerChats(CommunicationService $communicationService, UserService $userService)
    {
        $listConstructor = $communicationService->chats();
        $listConstructor->setUserIds('null');
        $chats = $listConstructor->load()->chats;
        $roles = Helpers::getRoles([Front::FRONT_MAS, Front::FRONT_SHOWCASE]);
        $users = $userService
            ->users(
                (new RestQuery())
                    ->setFilter('role', array_keys($roles))
            )
                ->keyBy('id')
                ->map(function (UserDto $user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->getTitle(),
                    ];
                });
        $this->title = 'Неперсонифицированные чаты';
        $this->loadCommunicationChannels = true;

        return $this->render('Communication/UnlinkMessengerChats', [
            'iChats' => $chats,
            'iUsers' => $users,
        ]);
    }

    /**
     * @throws RestClientException
     */
    protected function loadChats(ListConstructor $constructor): array
    {
        $userService = resolve(UserService::class);
        $fileService = resolve(FileService::class);

        $loadedChats = $constructor->load();

        $userIds = [];
        $fileIds = [];
        /** @var CommunicationChatDto $chat */
        foreach ($loadedChats->chats as $chat) {
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
                ->users(
                    $userService
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

        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        $customers = $customerService->customers(
            (new RestQuery())->addFields(CustomerDto::entity(), 'id', 'user_id')
            ->setFilter('user_id', $userIds)
        )->keyBy('user_id');

        /** @var OperatorService $operatorService */
        $operatorService = resolve(OperatorService::class);
        $operators = $operatorService->operators(
            (new RestQuery())->addFields(OperatorDto::entity(), 'id', 'user_id', 'merchant_id')
                ->setFilter('user_id', $userIds)
        )->keyBy('user_id');

        $pager = [
            'total' => $loadedChats->total,
            'pages' => $loadedChats->pages,
            'pageSize' => $loadedChats->pageSize,
        ];

        return [$loadedChats->chats, $users, $files, $customers, $operators, $pager];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getPage(): int
    {
        return request()->get('pageNumber', 1);
    }
}
