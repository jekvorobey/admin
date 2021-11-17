<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialtyRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\SpecialtyDto;
use Pim\Services\PublicEventSpecialtyService\PublicEventSpecialtyService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SpecialtyController extends Controller
{
    public function list(Request $request, PublicEventSpecialtyService $publicEventSpecialtyService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $this->title = 'Направления';

        $page = $request->get('page', 1);
        [$total, $specialties] = $this->loadSpecialties($publicEventSpecialtyService, $page);

        return $this->render('PublicEvent/SpecialtyList', [
            'iSpecialties' => $specialties,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, PublicEventSpecialtyService $publicEventSpecialtyService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        [$total, $specialties] = $this->loadSpecialties($publicEventSpecialtyService, $page);

        return response()->json([
            'specialties' => $specialties,
            'total' => $total['total'],
        ]);
    }

    public function save(
        SpecialtyRequest $request,
        PublicEventSpecialtyService $publicEventSpecialtyService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $data = $request->all();

        $specialty = new SpecialtyDto($data);

        if (isset($data['id'])) {
            $publicEventSpecialtyService->update($data['id'], $specialty);
        } else {
            $publicEventSpecialtyService->create($specialty);
        }
        $page = $request->get('page', 1);
        [$total, $specialties] = $this->loadSpecialties($publicEventSpecialtyService, $page);

        return response()->json([
            'specialties' => $specialties,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function delete(Request $request, PublicEventSpecialtyService $publicEventSpecialtyService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventSpecialtyService->delete($id);
        }

        return response()->json();
    }

    private function loadSpecialties(PublicEventSpecialtyService $publicEventSpecialtyService, $page): array
    {
        $query = $publicEventSpecialtyService->query()->pageNumber($page, 10);

        $total = $publicEventSpecialtyService->count($query);
        $specialties = $publicEventSpecialtyService->find($query);

        return [$total, $specialties];
    }
}
