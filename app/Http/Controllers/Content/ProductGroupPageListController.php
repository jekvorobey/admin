<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Cms\Dto\ProductGroupPageDto;
use Cms\Services\ProductGroupPageService\ProductGroupPageService;

class ProductGroupPageListController extends Controller
{
    public function index(
        Request $request,
        ProductGroupPageService $productGroupPageService
    ) {
        $this->title = 'Подборки';
        $this->breadcrumbs = 'productGroupPages.list';
        $query = $this->makeQuery($request);

        return $this->render('Content/ProductGroupPageList', [
            'iProductGroupPages' => $this->loadItems($query, $productGroupPageService),
            'iPager' => $productGroupPageService->productGroupPagesCount($query),
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

        $query->addFields(ProductGroupPageDto::entity(), 'id', 'name');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        return $query;
    }

    protected function loadItems(
        RestQuery $query,
        ProductGroupPageService $productGroupPageService
    ) {
        return $productGroupPageService->productGroupPages($query);
    }
}
