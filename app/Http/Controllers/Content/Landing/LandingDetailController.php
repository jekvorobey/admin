<?php

namespace App\Http\Controllers\Content\Landing;

use App\Http\Controllers\Controller;
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
            'iLanding' => [],
        ]);
    }

    /**
     * @throws CmsException
     */
    public function create(Request $request, LandingService $landingService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'active' => 'integer|required',
            'code' => 'string|required',
            'name' => 'string|required',
            'widgets' => 'string|nullable|required',
            'meta_title' => 'string|nullable',
            'meta_description' => 'string|nullable',
        ]);

        $landingService->createLanding(new LandingDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function update(int $id, Request $request, LandingService $landingService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'id' => 'integer|required',
            'active' => 'integer|required',
            'code' => 'string|required',
            'name' => 'string|required',
            'widgets' => 'string|required',
            'meta_title' => 'string|nullable',
            'meta_description' => 'string|nullable',
        ]);

        $validatedData['id'] = $id;

        $landingService->updateLanding($validatedData['id'], new LandingDto($validatedData));

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
