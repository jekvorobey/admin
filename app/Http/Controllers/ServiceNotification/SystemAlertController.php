<?php

namespace App\Http\Controllers\ServiceNotification;

use App\Http\Controllers\Controller;
use Greensight\Message\Dto\ServiceNotification\SystemAlertDto;
use Greensight\Message\Services\SystemAlertService\SystemAlertService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SystemAlertController extends Controller
{
    public function pageNotification(int $service_notification_id, SystemAlertService $templateService)
    {
        return response()->json([
            'alert' => $templateService->systemAlerts(
                $templateService->newQuery()
                    ->setFilter('service_notification_id', '=', $service_notification_id)
            )[0] ?? null,
            'types' => [
                ['name' => 'TYPE_ORDER_NEW', 'id' => 1],
                ['name' => 'TYPE_ORDER_CHANGED', 'id' => 2],
                ['name' => 'TYPE_ORDER_PAYED', 'id' => 3],
                ['name' => 'TYPE_ORDER_PROBLEM', 'id' => 4],
                ['name' => 'TYPE_ORDER_COMMENT', 'id' => 5],
                ['name' => 'TYPE_ORDER_CANCEL', 'id' => 6],
                ['name' => 'TYPE_SHIPMENT_NEW', 'id' => 20],
                ['name' => 'TYPE_SHIPMENT_PROBLEM', 'id' => 21],
                ['name' => 'TYPE_SHIPMENT_CANCEL', 'id' => 22],
                ['name' => 'TYPE_CATALOG_IMPORT_CHANGE_STATUS', 'id' => 30],
                ['name' => 'TYPE_CLAIM_NEW', 'id' => 40],
                ['name' => 'TYPE_CLAIM_UNDONE', 'id' => 41]
            ]
        ]);
    }
    
    public function save(Request $request, SystemAlertService $systemAlertService)
    {
        $id = $request->get('id');
        $systemAlert = $request->get('alert');
        
        if (!$systemAlert) {
            throw new BadRequestHttpException('alert required');
        }
        
        $systemAlertDto = new SystemAlertDto($systemAlert);
        
        if ($id) {
            $systemAlertService->update($id, $systemAlertDto);
        } else {
            $systemAlertService->create($systemAlertDto);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, SystemAlertService $systemAlertService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $systemAlertService->delete($id);
        }
        
        return response()->json();
    }
}