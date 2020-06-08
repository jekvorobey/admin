<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\SprintDto;
use Pim\Services\PublicEventSprintService\PublicEventSprintService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventSprintController extends Controller
{
    public function list(Request $request, PublicEventSprintService $publicEventPublicEventSprintService)
    {
        $sprints = $publicEventPublicEventSprintService->getByEvent($request->input('event_id'));

        return response()->json([
            'sprints' => $sprints['items']
        ]);
    }
    
    public function page(Request $request, PublicEventSprintService $publicEventPublicEventSprintService)
    {
        $page = $request->get('page', 1);
        [$total, $publicEventSprints] = $this->loadPublicEventSprints($publicEventPublicEventSprintService, $page);
        
        return response()->json([
            'publicEventSprints' => $publicEventSprints,
            'total' => $total['total'],
        ]);
    }
    
    public function save(Request $request, PublicEventSprintService $publicEventPublicEventSprintService)
    {
        $id = $request->get('id');
        $publicEventSprint = $request->get('sprint');
        
        if (!$publicEventSprint) {
            throw new BadRequestHttpException('publicEventSprint required');
        }
        
        $publicEventSprint = new SprintDto($publicEventSprint);
        
        if ($id) {
            $publicEventPublicEventSprintService->update($id, $publicEventSprint);
        } else {
            $publicEventPublicEventSprintService->create($publicEventSprint);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, PublicEventSprintService $publicEventPublicEventSprintService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $publicEventPublicEventSprintService->delete($id);
        }
        
        return response()->json();
    }
    
    /**
     * @param PublicEventSprintService $publicEventPublicEventSprintService
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadPublicEventSprints(PublicEventSprintService $publicEventPublicEventSprintService, $page): array
    {
        $query = $publicEventPublicEventSprintService->query()->pageNumber($page, 10);
        
        $total = $publicEventPublicEventSprintService->count($query);
        $publicEventSprints = $publicEventPublicEventSprintService->find($query);
        return [$total, $publicEventSprints];
    }
}