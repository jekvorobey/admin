<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductGroupPageDto;
use Cms\Services\ProductGroupPageService\ProductGroupPageService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductGroupPageDetailController extends Controller
{
    /**
     * @param int $id
     * @param ProductGroupPageService $productGroupPageService
     *
     * @return mixed
     */
    public function index(
        $id,
        ProductGroupPageService $productGroupPageService
    ) {
        [$productGroupPage] = $this->getProductData($id, $productGroupPageService);

        return $this->render('Content/ProductGroupPageDetail', [
            'iProductGroupPage' => $productGroupPage,
            'options' => [],
        ]);
    }

    protected function getProductData(int $id, ProductGroupPageService $productGroupPageService)
    {
        /** @var Collection|ProductGroupPageDto[] $productGroupPages */
        $productGroupPages = $productGroupPageService->newQuery()->setFilter('id', $id)->productGroupPages();
        if (!$productGroupPages->count()) {
            throw new NotFoundHttpException();
        }

        /** @var ProductGroupPageDto $product */
        $productGroupPage = $productGroupPages->first();

        return [
            $productGroupPage,
        ];
    }
}
