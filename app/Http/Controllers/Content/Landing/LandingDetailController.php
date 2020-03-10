<?php

namespace App\Http\Controllers\Content\Landing;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\LandingDto;
use Cms\Services\LandingService\LandingService;
use Illuminate\Http\Request;

class LandingDetailController extends Controller
{
    /**
     * @param int $id
     * @param LandingService $landingService
     * @return mixed
     * @throws CmsException
     */
    public function updatePage(
        $id,
        LandingService $landingService
    ) {
        $landing = $this->getLanding($id, $landingService);

        return $this->render('Content/LandingDetail', [
            'iLanding' => $landing,
            'iWidgetsList' => [],
            'iAllWidgetsNames' => [],
            'options' => [],
        ]);
    }

    /**
     * @return mixed
     * @throws CmsException
     */
    public function createPage() {
        return $this->render('Content/LandingDetail', [
            'iLanding' => [],
            'options' => [],
        ]);
    }

    /**
     * @param Request $request
     * @param LandingService $landingService
     * @return \Illuminate\Http\JsonResponse
     * @throws CmsException
     */
    public function create(Request $request, LandingService $landingService)
    {
        return response()->json([], 204);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param LandingService $landingService
     * @return \Illuminate\Http\JsonResponse
     * @throws CmsException
     */
    public function update(int $id, Request $request, LandingService $landingService)
    {
        return response()->json([], 204);
    }

    public function delete(int $id, LandingService $landingService)
    {
        return response()->json([], 204);
    }

    /**
     * @param int $id
     * @param LandingService $landingService
     * @return LandingDto|null
     * @throws CmsException
     */
    private function getLanding(
        int $id,
        LandingService $landingService
    ) {
        $query = $landingService->newQuery();
        $query->setFilter('id', $id);

        return $landingService->landings($query)->first();
    }
}
