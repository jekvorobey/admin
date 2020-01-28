<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductGroupDto;
use Cms\Services\ProductGroupService\ProductGroupService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductGroupDetailController extends Controller
{
    /**
     * @param int $id
     * @param ProductGroupService $productGroupService
     *
     * @return mixed
     */
    public function index(
        $id,
        ProductGroupService $productGroupService
    ) {
        [$productGroup] = $this->getProductData($id, $productGroupService);

        return $this->render('Content/ProductGroupDetail', [
            'iProductGroup' => $productGroup,
            'options' => [],
        ]);
    }

    protected function getProductData(int $id, ProductGroupService $productGroupService)
    {
        /** @var Collection|ProductGroupDto[] $productGroups */
        $productGroups = $productGroupService->newQuery()->setFilter('id', $id)->productGroups();
        if (!$productGroups->count()) {
            throw new NotFoundHttpException();
        }

        /** @var ProductGroupDto $product */
        $productGroup = $productGroups->first();

        return [
            $productGroup,
        ];
    }
}
