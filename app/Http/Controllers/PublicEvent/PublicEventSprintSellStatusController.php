<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\PublicEventSprintSellStatusDto;
use Pim\Dto\PublicEvent\PublicEventTicketTypeDto;
use Pim\Services\PublicEventSprintSellStatusService\PublicEventSprintSellStatusService;
use Pim\Services\PublicEventTicketTypeService\PublicEventTicketTypeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventSprintSellStatusController extends Controller
{
    public function save(Request $request, PublicEventSprintSellStatusService $publicEventPublicEventTicketTypeService)
    {
        $id = $request->get('id');
        $publicEventSprintSellStatus = ['status_id' => $request->input('status_id'), 'sprint_id' => $request->input('sprint_id')];
        
        if (!$publicEventSprintSellStatus) {
            throw new BadRequestHttpException('sellStatus required');
        }
        
        $publicEventSprintSellStatus = new PublicEventSprintSellStatusDto($publicEventSprintSellStatus);
        
        if ($id) {
            $publicEventPublicEventTicketTypeService->update($id, $publicEventSprintSellStatus);
        } else {
            $publicEventPublicEventTicketTypeService->create($publicEventSprintSellStatus);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, PublicEventSprintSellStatusService $publicEventPublicEventTicketTypeService)
    {
        $sprint_id = $request->input('sprint_id');
        $status_id = $request->input('status_id');
        
        $model = $publicEventPublicEventTicketTypeService->find(
            $publicEventPublicEventTicketTypeService
                ->query()
                ->setFilter('sprint_id', '=', $sprint_id)
                ->setFilter('status_id', '=', $status_id)
        )->first()->id;

        $publicEventPublicEventTicketTypeService->delete($model);
        
        return response()->json();
    }
}