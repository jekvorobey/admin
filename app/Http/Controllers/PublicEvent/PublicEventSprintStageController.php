<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\StageDto;
use Pim\Services\PublicEventSprintStageService\PublicEventSprintStageService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventSprintStageController extends Controller
{
    public function list(
        Request $request,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $sprintStages = $publicEventPublicEventSprintStageService->getBySprint($request->input('sprint_id'));

        return response()->json([
            'sprintStages' => $sprintStages['items'],
        ]);
    }

    public function page(
        Request $request,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $page = $request->get('page', 1);
        [$total, $publicEventSprintStages] = $this->loadPublicEventSprintStages($publicEventPublicEventSprintStageService, $page);

        return response()->json([
            'publicEventSprintStages' => $publicEventSprintStages,
            'total' => $total['total'],
        ]);
    }

    public function save(
        Request $request,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

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

    public function delete(
        Request $request,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventPublicEventSprintStageService->delete($id);
        }

        return response()->json();
    }

    public function getBySprint(
        int $sprint_id,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $sprintStages = $publicEventPublicEventSprintStageService->getBySprint($sprint_id);

        return response()->json([
            'sprintStages' => $sprintStages,
        ]);
    }

    public function createBySprint(
        int $sprint_id,
        Request $request,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $publicEventSprintStage = $request->get('publicEventSprintStage');

        if (!$publicEventSprintStage) {
            throw new BadRequestHttpException('publicEventSprintStage required');
        }

        $publicEventSprintStage = new StageDto($publicEventSprintStage);

        $publicEventPublicEventSprintStageService->createBySprint($sprint_id, $publicEventSprintStage);

        return response()->json();
    }

    public function attachType(
        Request $request,
        int $type_id,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        if (!$request->has('id')) {
            throw new BadRequestHttpException('id is required');
        }

        $publicEventPublicEventSprintStageService->attachType($request->input('id'), $type_id);

        return response()->json(['status' => 'ok']);
    }

    public function detachType(
        Request $request,
        int $type_id,
        PublicEventSprintStageService $publicEventPublicEventSprintStageService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        if (!$request->has('id')) {
            throw new BadRequestHttpException('id is required');
        }

        $publicEventPublicEventSprintStageService->detachType($request->input('id'), $type_id);

        return response()->json(['status' => 'ok']);
    }

    private function loadPublicEventSprintStages(
        PublicEventSprintStageService $publicEventPublicEventSprintStageService,
        $page
    ): array {
        $query = $publicEventPublicEventSprintStageService->query()->pageNumber($page, 10);

        $total = $publicEventPublicEventSprintStageService->count($query);
        $publicEventSprintStages = $publicEventPublicEventSprintStageService->find($query);

        return [$total, $publicEventSprintStages];
    }
}
