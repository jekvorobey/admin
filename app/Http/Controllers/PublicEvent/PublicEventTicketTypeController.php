<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\PublicEventTicketTypeDto;
use Pim\Services\PublicEventTicketTypeService\PublicEventTicketTypeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventTicketTypeController extends Controller
{
    public function list(Request $request, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        $ticketTypes = $publicEventPublicEventTicketTypeService->getBySprint($request->input('sprint_id'));

        return response()->json([
            'ticketTypes' => $ticketTypes['items']
        ]);
        // $page = $request->get('page', 1);
        // [$total, $publicEventTicketTypes]= $this->loadPublicEventTicketTypes($publicEventPublicEventTicketTypeService, $page);
        
        // return $this->render('PublicEvent/PublicEventTicketTypeList', [
        //     'iPublicEventTicketTypes' => $publicEventTicketTypes,
        //     'iTotal' => $total['total'],
        //     'iCurrentPage' => $page,
        // ]);
    }
    
    public function page(Request $request, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        $page = $request->get('page', 1);
        [$total, $publicEventTicketTypes] = $this->loadPublicEventTicketTypes($publicEventPublicEventTicketTypeService, $page);
        
        return response()->json([
            'publicEventTicketTypes' => $publicEventTicketTypes,
            'total' => $total['total'],
        ]);
    }
    
    public function save(Request $request, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        $id = $request->get('id');
        $publicEventTicketType = $request->get('ticketType');
        
        if (!$publicEventTicketType) {
            throw new BadRequestHttpException('publicEventTicketType required');
        }
        
        $publicEventTicketType = new PublicEventTicketTypeDto($publicEventTicketType);
        
        if ($id) {
            $publicEventPublicEventTicketTypeService->update($id, $publicEventTicketType);
        } else {
            $publicEventPublicEventTicketTypeService->create($publicEventTicketType);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $publicEventPublicEventTicketTypeService->delete($id);
        }
        
        return response()->json();
    }

    public function getBySprint(int $sprint_id, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        $ticketTypes = $publicEventPublicEventTicketTypeService->getBySprint($sprint_id);

        return response()->json([
            'ticketTypes' => $ticketTypes
        ]);
    }

    public function createBySprint(int $sprint_id, Request $request, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        $publicEventTicketType = $request->get('publicEventTicketType');
        
        if (!$publicEventTicketType) {
            throw new BadRequestHttpException('publicEventTicketType required');
        }
        
        $publicEventTicketType = new PublicEventTicketTypeDto($publicEventTicketType);
        
        $publicEventPublicEventTicketTypeService->createBySprint($sprint_id, $publicEventTicketType);
        
        return response()->json();
    }

    public function attachStage(Request $request, int $stage_id, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        if(!$request->has('id')) {
            throw new BadRequestHttpException('id is required');
        }

        $publicEventPublicEventTicketTypeService->attachStage($request->input('id'), $stage_id);

        return response()->json(['status' => 'ok']);
    }

    public function detachStage(Request $request, int $stage_id, PublicEventTicketTypeService $publicEventPublicEventTicketTypeService)
    {
        if(!$request->has('id')) {
            throw new BadRequestHttpException('id is required');
        }

        $publicEventPublicEventTicketTypeService->detachStage($request->input('id'), $stage_id);

        return response()->json(['status' => 'ok']);
    }
    
    /**
     * @param PublicEventTicketTypeService $publicEventPublicEventTicketTypeService
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadPublicEventTicketTypes(PublicEventTicketTypeService $publicEventPublicEventTicketTypeService, $page): array
    {
        $query = $publicEventPublicEventTicketTypeService->query()->pageNumber($page, 10);
        
        $total = $publicEventPublicEventTicketTypeService->count($query);
        $publicEventTicketTypes = $publicEventPublicEventTicketTypeService->find($query);
        return [$total, $publicEventTicketTypes];
    }
}