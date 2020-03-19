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
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;

class TabPromoPageController extends Controller
{
    public function load($id)
    {
        return response()->json([
            'products' => $this->loadPromPageProducts($id),
        ]);
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
        if ($promoProducts) {
            $products = $productService
                ->products(
                    (new RestQuery())
                        ->setFilter('id', $promoProducts)
                        ->include('brand', 'category')
                        ->addFields(ProductDto::entity(), 'id', 'brand_id', 'category_id', 'name')
                        ->addFields(BrandDto::entity(), 'id', 'name')
                        ->addFields(CategoryDto::entity(), 'id', 'name')
                )
                ->keyBy('id');
            $offers = $offerService->offersByProduct($promoProducts, null, true)->pluck('id', 'product_id');

            $prices = $priceService
                ->prices((new PricesInDto())->setOffers($offers->values()->all()))
                ->keyBy('offer_id');

            foreach ($promoProducts as $product_id) {
                if ($products->has($promoProduct['product_id'])) {
                    if ($offers->has($promoProduct['product_id'])) {
                        if ($prices->has($offers[$promoProduct['product_id']])) {
                            $promoProduct['price'] = $prices[$offers[$promoProduct['product_id']]]->price;
                        }
                    }
                    $productDto = $products[$promoProduct['product_id']];

                    $result[] = [
                        'id' => $product_id,
                        'image' => '',
                        'name' => $productDto->name,
                        'category' => $productDto->category(),
                        'brand' => $productDto->brand(),
                        'price' => '',
                        'created_at' => '',
                    ];
                }
            }
        }


        return $result;
    }
}