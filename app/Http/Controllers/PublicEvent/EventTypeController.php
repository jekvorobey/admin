<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\PublicEventTypeDto;
use Pim\Services\PublicEventTypeService\PublicEventTypeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EventTypeController extends Controller
{
    public function list(Request $request, PublicEventTypeService $publicEventTypeService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        [$total, $types] = $this->loadTypes($publicEventTypeService, $page);

        return $this->render('PublicEvent/TypeList', [
            'iTypes' => $types,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, PublicEventTypeService $publicEventTypeService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        [$total, $types] = $this->loadTypes($publicEventTypeService, $page);

        return response()->json([
            'types' => $types,
            'total' => $total['total'],
        ]);
    }

    public function save(Request $request, PublicEventTypeService $publicEventTypeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');
        $type = $request->get('type');

        if (!$type) {
            throw new BadRequestHttpException('type required');
        }

        $type = new PublicEventTypeDto($type);

        if ($id) {
            $publicEventTypeService->update($id, $type);
        } else {
            if ($publicEventTypeService->checkExistence($type->code)['existence']) {
                throw new BadRequestHttpException('Provided type already exists.');
            }

            $publicEventTypeService->create($type);
        }

        return response()->json();
    }

    public function delete(Request $request, PublicEventTypeService $publicEventTypeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventTypeService->delete($id);
        }

        return response()->json();
    }

    private function loadTypes(PublicEventTypeService $publicEventTypeService, $page): array
    {
        $query = $publicEventTypeService->query()->pageNumber($page, 10);

        $total = $publicEventTypeService->count($query);
        $types = $publicEventTypeService->find($query);

        return [$total, $types];
    }
}
