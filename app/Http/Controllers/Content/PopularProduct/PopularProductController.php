<?php

namespace App\Http\Controllers\Content\PopularProduct;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\PopularProductDto;
use Cms\Services\PopularProductService\PopularProductService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Pim\Core\PimException;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;

class PopularProductController extends Controller
{
    /**
     * Список всех популярных товаров
     * @return mixed
     * @throws CmsException|PimException
     */
    public function list(PopularProductService $popularProductService, ProductService $productService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $popularProducts = $popularProductService->popularProducts(
            (new RestQuery())
                ->addSort('weight', 'desc')
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
     * @throws PimException
     */
    public function create(PopularProductService $popularProductService, ProductService $productService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'product_id' => [
                'required',
                'regex:/^\d+(,\d+)*$/',
            ],
        ]);

        $data['product_id'] = explode(',', $data['product_id']);

        $products = $productService->products(
            (new RestQuery())
                ->addFields(ProductDto::entity(), 'id', 'name')
                ->setFilter('id', $data['product_id'])
        );

        $response = [];

        // Несуществующие товары. Т.к. PIM отправляет инфу только он валидных ID,
        // приходится считать невалидные
        $missingProducts = array_udiff(
            $data['product_id'],
            $products->toArray(),
            function ($first, $second) {
                if (is_numeric($first)) {
                    $f = $first;
                } else {
                    $f = $first['id'];
                }
                if (is_numeric($second)) {
                    $s = $second;
                } else {
                    $s = $second['id'];
                }
                return $f - $s;
            }
        );
        foreach ($missingProducts as $prod) {
            $response[] = [
                'isAdded' => false,
                'popular_product' => [
                    'product_id' => $prod,
                ],
                'message' => "Товар с id={$prod} не существует",
            ];
        }

        foreach ($products as $product) {
            $popularProduct = new PopularProductDto();
            $popularProduct->product_id = $product->id;

            try {
                $createdPopularProduct = $popularProductService->create($popularProduct);
                $response[] = [
                    'isAdded' => true,
                    'popular_product' => [
                        'id' => $createdPopularProduct->id,
                        'product_id' => $createdPopularProduct->product_id,
                        'name' => $product->name,
                        'weight' => $createdPopularProduct->weight,
                    ],
                ];
            } catch (CmsException $e) {
                $response[] = [
                    'isAdded' => false,
                    'popular_product' => [
                        'product_id' => $product->id,
                    ],
                    'message' => "Товар с id={$product->id} не был добавлен",
                ];
            }
        }

        return response()->json($response, 201);
    }

    /**
     * Удалить популярный товар
     * @return Application|ResponseFactory|Response
     * @throws CmsException
     */
    public function delete(PopularProductService $popularProductService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $popularProductService->delete($data['ids']);

        return response('', 204);
    }

    /**
     * Изменить порядок популярных товаров
     */
    public function reorder(PopularProductService $popularProductService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.weight' => 'required|integer',
        ]);

        $popularProductService->reorder($data);

        return response('', 204);
    }
}
