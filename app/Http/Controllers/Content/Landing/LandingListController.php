<?php

namespace App\Http\Controllers\Content\Landing;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\LandingDto;
use Cms\Services\LandingService\LandingService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LandingListController extends Controller
{
    public function listPage(
        Request $request,
        LandingService $landingService
    ) {
        $this->title = 'Страницы';
        $query = $this->makeQuery($request);

        return $this->render('Content/LandingList', [
            'iLandings' => $this->loadItems($query, $landingService),
            'iPager' => $landingService->landingsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
            ],
        ]);
    }

    public function page(
        Request $request,
        LandingService $landingService
    ) {
        $query = $this->makeQuery($request);
        $data = [
            'landings' => $this->loadItems($query, $landingService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $landingService->landingsCount($query);
        }

        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return RestQuery
     */
    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $query->addFields('type', '*');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        return $query;
    }

    /**
     * @param RestQuery $query
     * @param LandingService $landingService
     * @return LandingDto[]|Collection
     * @throws CmsException
     */
    protected function loadItems(
        RestQuery $query,
        LandingService $landingService
    ) {
        return $landingService->landings($query);
    }
}
