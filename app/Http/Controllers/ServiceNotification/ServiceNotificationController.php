<?php

namespace App\Http\Controllers\ServiceNotification;

use App\Http\Controllers\Controller;
use Greensight\Message\Dto\ServiceNotification\ServiceNotificationDto;
use Greensight\Message\Services\ServiceNotificationService\ServiceNotificationService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServiceNotificationController extends Controller
{
    public function page(Request $request, ServiceNotificationService $serviceNotificationService)
    {
        $page = $request->input('page') ?? 1;

        $notifications = $serviceNotificationService
            ->notifications($serviceNotificationService->newQuery()->pageNumber($page, 10)->include('templates'))
            ->map(function ($notification) {
                $notification['channels'] = collect($notification->templates)->pluck('channel');
                return $notification;
            });

        return $this->render('Communication/ServiceNotification', [
            'iNotifications' => $notifications,
            'iTotal' => $serviceNotificationService->count($serviceNotificationService->newQuery())['total'],
            'iCurrentPage' => $request->input('page'),
        ]);
    }

    public function list(Request $request, ServiceNotificationService $serviceNotificationService)
    {
        $page = $request->input('page') ?? 1;

        $notifications = $serviceNotificationService
            ->notifications($serviceNotificationService->newQuery()->pageNumber($page, 10)->include('templates'))
            ->map(function ($notification) {
                $notification['channels'] = collect($notification->templates)->pluck('channel');
                return $notification;
            });

        return response()->json([
            'notifications' => $notifications,
            'total' => $serviceNotificationService->count($serviceNotificationService->newQuery())['total']
        ]);
    }
    
    public function save(Request $request, ServiceNotificationService $serviceNotificationService)
    {
        $id = $request->get('id');
        $notification = $request->get('notification');
        
        if (!$notification) {
            throw new BadRequestHttpException('notification required');
        }
        
        $notificationDto = new ServiceNotificationDto($notification);
        
        if ($id) {
            $serviceNotificationService->update($id, $notificationDto);
        } else {
            $serviceNotificationService->create($notificationDto);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, ServiceNotificationService $serviceNotificationService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $serviceNotificationService->delete($id);
        }
        
        return response()->json();
    }

    public function send(Request $request, ServiceNotificationService $serviceNotificationService)
    {
        $user_id = $request->get('user_id');
        $type = $request->get('type');
        $attributes = $request->get('attributes', []);

        $serviceNotificationService->send($user_id, $type, $attributes);

        return response()->json();
    }
}