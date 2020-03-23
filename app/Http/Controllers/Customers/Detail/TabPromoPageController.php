<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Customer\Services\ReferralService\Dto\GetPromoPageProductsDto;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\Product\ProductImageType;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;

class TabPromoPageController extends Controller
{
    public function load($id)
    {
        return response()->json(array_merge($this->loadPromPageProducts($id), [
            'url' => url_showcase('/referrer/{code}'),
        ]));
    }

    public function add($id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'product_id' => 'numeric'
        ]);
        $referralService->addPromoPageProduct($id, $data['product_id']);

        return response()->json($this->loadPromPageProducts($id));
    }

    public function delete($id, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'product_id' => 'numeric'
        ]);
        $referralService->deletePromoPageProduct($id, $data['product_id']);

        return response()->json($this->loadPromPageProducts($id));
    }

    protected function loadPromPageProducts($id)
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);
        /** @var ProductService $productService */
        $productService = resolve(ProductService::class);
        /** @var OfferService $offerService */
        $offerService = resolve(OfferService::class);
        /** @var PriceService $priceService */
        $priceService = resolve(PriceService::class);

        $promoProducts = $referralService->getPromoPageProducts((new GetPromoPageProductsDto())->setReferralId($id));

        $result = [];
        $brands = collect();
        $categories = collect();
        if ($promoProducts->isNotEmpty()) {
            $products = $productService
                ->products(
                    (new RestQuery())
                        ->setFilter('id', $promoProducts->pluck('product_id')->all())
                        ->include('brand', 'category')
                        ->addFields(ProductDto::entity(), 'id', 'brand_id', 'category_id', 'name')
                        ->addFields(BrandDto::entity(), 'id', 'name')
                        ->addFields(CategoryDto::entity(), 'id', 'name')
                )
                ->keyBy('id');
            $offers = $offerService->offersByProduct($promoProducts->pluck('product_id')->all(), null, true)->pluck('id', 'product_id');

            $prices = $priceService
                ->prices((new PricesInDto())->setOffers($offers->values()->all()))
                ->keyBy('offer_id');

            $allImages = $productService
                ->allImages($promoProducts->pluck('product_id')->all(), ProductImageType::TYPE_MAIN)
                ->pluck('id', 'productId');

            foreach ($promoProducts as $promoProduct) {
                if ($products->has($promoProduct->product_id)) {
                    $price = '';
                    if ($offers->has($promoProduct->product_id)) {
                        if ($prices->has($offers[$promoProduct->product_id])) {
                            $price = $prices[$offers[$promoProduct->product_id]]->price;
                        }
                    }
                    $productDto = $products[$promoProduct->product_id];

                    $result[] = [
                        'id' => $promoProduct->product_id,
                        'image' => $allImages->get($promoProduct->product_id),
                        'name' => $productDto->name,
                        'category' => $productDto->category(),
                        'brand' => $productDto->brand(),
                        'price' => $price,
                        'status' => '',
                        'created_at' => $promoProduct->created_at,
                    ];

                    $brands->put($productDto->brand()->id, $productDto->brand()->toArray());
                    $categories->put($productDto->category()->id, $productDto->category()->toArray());
                }
            }
        }


        return [
            'products' => $result,
            'brands' => $brands->sortBy('name')->values(),
            'categories' => $categories->sortBy('name')->values(),
        ];
    }
}