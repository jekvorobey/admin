<?php

namespace App\Http\Controllers;

use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Message\Services\NotificationService\NotificationService;
use Illuminate\Http\JsonResponse;

/**
 * Class NotificationsController
 * @package App\Http\Controllers
 */
class NotificationsController extends Controller
{
    public function read(NotificationService $notificationService, RequestInitiator $user): JsonResponse
    {
        $restQuery = new RestQuery();

        $restQuery->setFilter('user_id', $user->userId());
        $restQuery->addSort('created_at', 'desc');
        $restQuery->addSort('status', 'asc');
        $restQuery->pageOffset(0, 30);

        $notifications = $notificationService->notifications($restQuery);

        return response()->json($notifications);
    }

    public function markAll(NotificationService $notificationService, RequestInitiator $user): JsonResponse
    {
        $notificationService->markAll($user->userId());

        return response()->json('ok');
    }
}
