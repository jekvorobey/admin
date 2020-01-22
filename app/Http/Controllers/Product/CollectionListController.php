<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Crm\Dto\CollectionDto;
use Crm\Services\CollectionService\CollectionService;

class CollectionListController extends Controller
{
    public function index(
        Request $request,
        CollectionService $collectionService
    ) {
        $this->title = 'Подборки';
        $this->breadcrumbs = 'collections.list';
        $query = $this->makeQuery($request);

        return $this->render('Product/CollectionList', [
            'iCollections' => $this->loadItems($query, $collectionService),
            'iPager' => $collectionService->collectionsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'types' => [],
            ]
        ]);
    }

    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $query->addFields(CollectionDto::entity(), 'id', 'name');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        return $query;
    }

    protected function loadItems(
        RestQuery $query,
        CollectionService $collectionService
    ) {
        /** @var Collection $collections */
        $collections = $collectionService->collections($query);

        return $collections;
    }
}