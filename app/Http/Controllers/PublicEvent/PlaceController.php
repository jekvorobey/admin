<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\PlaceDto;
use Pim\Services\PublicEventPlaceService\PublicEventPlaceService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PlaceController extends Controller
{
    public function list(Request $request, PublicEventPlaceService $publicEventPlaceService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        [$total, $places] = $this->loadPlaces($publicEventPlaceService, $page);

        return $this->render('PublicEvent/PlaceList', [
            'iPlaces' => $places,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, PublicEventPlaceService $publicEventPlaceService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        [$total, $places] = $this->loadPlaces($publicEventPlaceService, $page);

        return response()->json([
            'places' => $places,
            'total' => $total['total'],
        ]);
    }

    public function fullList(PublicEventPlaceService $publicEventPlaceService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $query = $publicEventPlaceService->query();

        return response()->json([
            'places' => $publicEventPlaceService->find($query),
        ]);
    }

    public function save(Request $request, PublicEventPlaceService $publicEventPlaceService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');
        $place = $request->get('place');

        if (!$place) {
            throw new BadRequestHttpException('place required');
        }

        $place = new PlaceDto($place);

        if ($id) {
            $publicEventPlaceService->update($id, $place);
        } else {
            $publicEventPlaceService->create($place);
        }

        return response()->json();
    }

    public function delete(Request $request, PublicEventPlaceService $publicEventPlaceService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventPlaceService->delete($id);
        }

        return response()->json();
    }

    public function media(Request $request, PublicEventPlaceService $publicEventPlaceService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');
        $file_id = $request->get('file_id');

        if (!$file_id) {
            throw new BadRequestHttpException('file_id required');
        }

        return response()->json([
            'media' => $publicEventPlaceService->addMedia($id, $file_id)['media'],
        ]);
    }

    private function loadPlaces(PublicEventPlaceService $publicEventPlaceService, $page): array
    {
        $query = $publicEventPlaceService->query()->pageNumber($page, 10);

        $total = $publicEventPlaceService->count($query);
        $places = $publicEventPlaceService->find($query);
        return [$total, $places];
    }
}
