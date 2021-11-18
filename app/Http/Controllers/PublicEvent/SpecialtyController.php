<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialtyRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\SpecialtyDto;
use Pim\Services\PublicEventSpecialtyService\PublicEventSpecialtyService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SpecialtyController extends Controller
{
    /**
     * @throws PimException
     */
    public function list(Request $request, PublicEventSpecialtyService $publicEventSpecialtyService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $this->title = 'Направления';

        $query = $this->makeQuery($request);

        return $this->render('PublicEvent/SpecialtyList', [
            'iSpecialties' => $this->loadItems($query, $publicEventSpecialtyService),
            'iPager' => $publicEventSpecialtyService->count($query),
            'iCurrentPage' => $request->get('page', 1),
        ]);
    }

    /**
     * @throws PimException
     */
    public function page(Request $request, PublicEventSpecialtyService $publicEventSpecialtyService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $query = $this->makeQuery($request);
        $data = [
            'items' => $this->loadItems($query, $publicEventSpecialtyService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $publicEventSpecialtyService->count($query);
        }

        return response()->json($data);
    }

    /**
     * @throws PimException
     */
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
        $query = $this->makeQuery($request);

        return response()->json([
            'specialty' => $specialty,
            'specialties' => $this->loadItems($query, $publicEventSpecialtyService),
            'iPager' => $publicEventSpecialtyService->count($query),
        ]);
    }

    /**
     * @throws PimException
     */
    public function delete(Request $request, PublicEventSpecialtyService $publicEventSpecialtyService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');

        if (!$id) {
            throw new BadRequestHttpException('id required');
        }
        $publicEventSpecialtyService->delete($id);
        $query = $this->makeQuery($request);

        return response()->json([
            'specialties' => $this->loadItems($query, $publicEventSpecialtyService),
            'iPager' => $publicEventSpecialtyService->count($query),
        ]);
    }

    protected function makeQuery(Request $request): RestQuery
    {
        $restQuery = new RestQuery();
        $restQuery->pageNumber($request->get('page', 1), 20);

        return $restQuery;
    }

    /**
     * @throws PimException
     */
    protected function loadItems(RestQuery $query, PublicEventSpecialtyService $publicEventSpecialtyService)
    {
        return $publicEventSpecialtyService->specialties($query);
    }
}
