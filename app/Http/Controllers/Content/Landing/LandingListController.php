<?php

namespace App\Http\Controllers\Content\Landing;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\LandingDto;
use Cms\Services\LandingService\LandingService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;

class LandingListController extends Controller
{
    public function listPage(Request $request, LandingService $landingService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $this->title = 'Страницы';
        $query = $this->makeQuery($request);

        return $this->render('Content/LandingList', [
            'iLandings' => $this->loadItems($query, $landingService),
            'iPager' => $landingService->landingsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'url' => config('app.showcase_host'),
        ]);
    }

    public function page(Request $request, LandingService $landingService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $query = $this->makeQuery($request);
        $data = [
            'landings' => $this->loadItems($query, $landingService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $landingService->landingsCount($query);
        }

        return response()->json($data);
    }

    protected function makeQuery(Request $request): RestQuery
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $query->addFields(LandingDto::entity(), 'id', 'active', 'code', 'name');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        if (isset($filter['name'])) {
            $query->setFilter('name', 'like', "%{$filter['name']}%");
        }

        if (isset($filter['active'])) {
            $query->setFilter('active', $filter['active']);
        }

        return $query;
    }

    /**
     * @return LandingDto[]|Collection
     * @throws CmsException
     */
    protected function loadItems(RestQuery $query, LandingService $landingService)
    {
        return $landingService->landings($query);
    }
}
