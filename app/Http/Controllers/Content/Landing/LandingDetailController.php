<?php

namespace App\Http\Controllers\Content\Landing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Landing\LandingActionRequest;
use Cms\Core\CmsException;
use Cms\Dto\LandingDto;
use Cms\Dto\LandingWidgetDto;
use Cms\Services\LandingService\LandingService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LandingDetailController extends Controller
{
    /**
     * @param int $id
     * @return mixed
     * @throws CmsException
     */
    public function updatePage($id, LandingService $landingService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $landing = $this->getLanding($id, $landingService);

        return $this->render('Content/LandingDetail', [
            'iLanding' => $landing,
        ]);
    }

    /**
     * @return mixed
     */
    public function createPage()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        return $this->render('Content/LandingDetail', [
            'iLanding' => new \stdClass(),
        ]);
    }

    /**
     * @throws CmsException
     */
    public function create(LandingActionRequest $request, LandingService $landingService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $landingService->createLanding(new LandingDto($request->validated()));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function update(int $id, LandingActionRequest $request, LandingService $landingService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $landingService->updateLanding($id, new LandingDto($request->validated()));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function delete(int $id, LandingService $landingService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $landingService->deleteLanding($id);

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    private function getLanding(int $id, LandingService $landingService): ?LandingDto
    {
        $query = $landingService->newQuery()->setFilter('id', $id);
        return $landingService->landings($query)->first();
    }

    /**
     * @return LandingWidgetDto[]|Collection
     * @throws CmsException
     */
    private function getWidgets(LandingService $landingService)
    {
        return $landingService->widgets($landingService->newQuery());
    }
}
