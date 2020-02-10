<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Services\MenuService\MenuService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;

class MenuListController extends Controller
{
    public function index(
        Request $request,
        MenuService $menuService
    ) {
        $this->title = 'Меню';
        $query = $this->makeQuery($request);

        return $this->render('Content/MenuList', [
            'iMenus' => $menuService->menus($query),
            'iPager' => $menuService->menusCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => []
        ]);
    }

    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        return $query;
    }
}
