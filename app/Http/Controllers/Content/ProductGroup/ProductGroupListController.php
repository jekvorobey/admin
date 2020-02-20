<?php

namespace App\Http\Controllers\Content\ProductGroup;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\ProductGroupDto;
use Cms\Dto\ProductGroupTypeDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Cms\Services\ProductGroupTypeService\ProductGroupTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductGroupListController extends Controller
{
    public function index(
        Request $request,
        ProductGroupService $productGroupService,
        ProductGroupTypeService $productGroupTypeService,
        FileService $fileService
    ) {
        $this->title = 'Подборки';
        $query = $this->makeQuery($request);

        return $this->render('Content/ProductGroupList', [
            'iProductGroups' => $this->loadItems($query, $productGroupService, $fileService),
            'iPager' => $productGroupService->productGroupsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'types' => $this->loadTypes($productGroupTypeService),
            ]
        ]);
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
        $query->addFields('products', '*');
        $query->addFields('filters', '*');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        return $query;
    }

    /**
     * @param RestQuery $query
     * @param ProductGroupService $productGroupService
     * @param FileService $fileService
     * @return ProductGroupDto[]|Collection
     * @throws CmsException
     */
    protected function loadItems(
        RestQuery $query,
        ProductGroupService $productGroupService,
        FileService $fileService
    ) {
        $productGroups = $productGroupService->productGroups($query);
        $photoIds = $productGroups->pluck('preview_photo_id')->all();
        $photos = $fileService->getFiles($photoIds)->keyBy('id');

        // Получается дополненный ProductGroupDto
        return $productGroups->map(function(ProductGroupDto $productGroup) use ($photos) {
            /** @var FileDto $photo */
            $photo = $photos->get($productGroup['preview_photo_id']);
            $productGroup['photo'] = $photo ? $photo->absoluteUrl() : null;
            return $productGroup;
        });
    }

    /**
     * @param ProductGroupTypeService $productGroupTypeService
     * @return ProductGroupTypeDto[]|Collection
     * @throws CmsException
     */
    protected function loadTypes(ProductGroupTypeService $productGroupTypeService)
    {
        return $productGroupTypeService->productGroupTypes($productGroupTypeService->newQuery());
    }
}
