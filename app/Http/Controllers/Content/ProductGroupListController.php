<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Services\ProductGroupService\ProductGroupService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;

class ProductGroupListController extends Controller
{
    public function index(
        Request $request,
        ProductGroupService $productGroupService
    ) {
        $this->title = 'Подборки';
        $query = $this->makeQuery($request);

        return $this->render('Content/ProductGroupList', [
            'iProductGroups' => $this->loadItems($query, $productGroupService),
            'iPager' => $productGroupService->productGroupsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'types' => [
                    [
                        'id' => 1,
                        'name' => 'Тест',
                    ],
                ],
            ]
        ]);
    }

    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $query->addFields('type', '*');
        $query->addFields('products', '*');
        $query->addFields('filters', '*');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        return $query;
    }

    protected function loadItems(
        RestQuery $query,
        ProductGroupService $productGroupService
    ) {
        return $productGroupService->productGroups($query);
    }
}
