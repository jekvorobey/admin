<?php

namespace App\Http\Controllers\Content\PopularProduct;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\PopularProductDto;
use Cms\Services\PopularProductService\PopularProductService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Pim\Core\PimException;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PopularProductController extends Controller
{
    /**
     * Список всех популярных товаров
     * @param PopularProductService $popularProductService
     * @return mixed
     * @throws CmsException|PimException
     */
    public function list(
        PopularProductService $popularProductService,
        ProductService $productService
    ) {
        $popularProducts = $popularProductService->popularProducts(
            (new RestQuery())
                ->addSort('weight')
        );

        if ($popularProducts->isNotEmpty()) {
            $productIds = $popularProducts->pluck('product_id')->all();
            $products = $productService->products(
                (new RestQuery())
                    ->addFields(ProductDto::entity(), 'id', 'name')
                    ->setFilter('id', $productIds)
            )->keyBy('id');

            $popularProducts = $popularProducts->map(function (PopularProductDto $popularProduct) use ($products) {
                return [
                    'id' => $popularProduct->id,
                    'product_id' => $popularProduct->product_id,
                    'name' => $products[$popularProduct->product_id]->name,
                    'weight' => $popularProduct->weight,
                ];
            });
        }

        $this->title = 'Популярные товары';
        return $this->render('Content/PopularProducts', [
            'iPopularProducts' => $popularProducts,
        ]);
    }

    /**
     * Добавить новый популярный товар
     * @param PopularProductService $popularProductService
     * @param ProductService $productService
     * @return JsonResponse
     * @throws CmsException|PimException
     */
    public function create(PopularProductService $popularProductService, ProductService $productService)
    {
        $data = $this->validate(request(), [
            'product_id' => 'required|integer',
        ]);

        /** @var ProductDto $product */
        $product = $productService->products(
            (new RestQuery())
                ->addFields(ProductDto::entity(), 'id', 'name')
                ->setFilter('id', $data['product_id'])
        )->first();

        if (!$product) {
            throw new NotFoundHttpException("Товара с id={$data['product_id']} не существует");
        }

        $popularProduct = new PopularProductDto();
        $popularProduct->product_id = $data['product_id'];

        $createdPopularProduct = $popularProductService->create($popularProduct);

        return response()->json([
            'popular_product' => [
                'id' => $createdPopularProduct->id,
                'product_id' => $createdPopularProduct->product_id,
                'name' => $product->name,
                'weight' => $createdPopularProduct->weight,
            ],
        ], 201);
    }

    /**
     * Удалить популярный товар
     * @param PopularProductService $popularProductService
     * @return Application|ResponseFactory|Response
     * @throws CmsException
     */
    public function delete(PopularProductService $popularProductService)
    {
        $data = $this->validate(request(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $popularProductService->delete($data['ids']);

        return response('', 204);
    }

    /**
     * Изменить порядок популярных товаров
     * @param PopularProductService $popularProductService
     * @return Response
     */
    public function reorder(PopularProductService $popularProductService)
    {
        $data = $this->validate(request(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.weight' => 'required|integer',
        ]);

        $popularProductService->reorder($data);

        return response('', 204);
    }
}