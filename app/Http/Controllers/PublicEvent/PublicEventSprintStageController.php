<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\StageDto;
use Pim\Services\PublicEventSprintStageService\PublicEventSprintStageService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventSprintStageController extends Controller
{
    public function list(Request $request, PublicEventSprintStageService $publicEventPublicEventSprintStageService)
    {
        $sprintStages = $publicEventPublicEventSprintStageService->getBySprint($request->input('sprint_id'));

        return response()->json([
            'sprintStages' => $sprintStages['items'],
        ]);
    }
    
    public function page(Request $request, PublicEventSprintStageService $publicEventPublicEventSprintStageService)
    {
        $page = $request->get('page', 1);
        [$total, $publicEventSprintStages] = $this->loadPublicEventSprintStages($publicEventPublicEventSprintStageService, $page);
        
        return response()->json([
            'publicEventSprintStages' => $publicEventSprintStages,
            'total' => $total['total'],
        ]);
    }
    
    public function save(Request $request, PublicEventSprintStageService $publicEventPublicEventSprintStageService)
    {
        $id = $request->get('id');
        $publicEventSprintStage = $request->get('sprintStage');
        
        if (!$publicEventSprintStage) {
            throw new BadRequestHttpException('publicEventSprintStage required');
        }
        
        $publicEventSprintStage = new StageDto($publicEventSprintStage);
        
        if ($id) {
            $publicEventPublicEventSprintStageService->update($id, $publicEventSprintStage);
        } else {
            $publicEventPublicEventSprintStageService->create($publicEventSprintStage);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, PublicEventSprintStageService $publicEventPublicEventSprintStageService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $publicEventPublicEventSprintStageService->delete($id);
        }
        
        return response()->json();
    }

    public function getBySprint(int $sprint_id, PublicEventSprintStageService $publicEventPublicEventSprintStageService)
    {
        $sprintStages = $publicEventPublicEventSprintStageService->getBySprint($sprint_id);

        return response()->json([
            'sprintStages' => $sprintStages
        ]);
    }

    public function createBySprint(int $sprint_id, Request $request, PublicEventSprintStageService $publicEventPublicEventSprintStageService)
    {
        $publicEventSprintStage = $request->get('publicEventSprintStage');
        
        if (!$publicEventSprintStage) {
            throw new BadRequestHttpException('publicEventSprintStage required');
        }
        
        $publicEventSprintStage = new StageDto($publicEventSprintStage);
        
        $publicEventPublicEventSprintStageService->createBySprint($sprint_id, $publicEventSprintStage);
        
        return response()->json();
    }
    
    /**
     * @param PublicEventSprintStageService $publicEventPublicEventSprintStageService
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadPublicEventSprintStages(PublicEventSprintStageService $publicEventPublicEventSprintStageService, $page): array
    {
        $query = $publicEventPublicEventSprintStageService->query()->pageNumber($page, 10);
        
        $total = $publicEventPublicEventSprintStageService->count($query);
        $publicEventSprintStages = $publicEventPublicEventSprintStageService->find($query);
        return [$total, $publicEventSprintStages];
    }
}