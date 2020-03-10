<?php

namespace App\Http\Controllers\Content\Landing;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\LandingDto;
use Cms\Dto\LandingWidgetDto;
use Cms\Services\LandingService\LandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
        $widgets = $this->getWidgets($landingService);

        return $this->render('Content/LandingDetail', [
            'iLanding' => $landing,
            'iWidgetsList' => $widgets,
            'iAllWidgetsNames' => $widgets->pluck('widgetCode')->all(),
            'options' => [],
        ]);
    }

    /**
     * @return mixed
     * @throws CmsException
     */
    public function createPage()
    {
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

    /**
     * @param LandingService $landingService
     * @return LandingWidgetDto[]|Collection
     * @throws CmsException
     */
    private function getWidgets(LandingService $landingService)
    {
        return $landingService->widgets($landingService->newQuery());
    }
}
