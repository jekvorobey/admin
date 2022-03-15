<?php

namespace App\Managers\PromoProducts;

use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Customer\Services\ReferralService\Dto\GetPromotionDto;
use Greensight\Customer\Services\ReferralService\Dto\PutPromotionDto;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PromoProductsManager
{
    public function fetch(?int $merchantId = null): array
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);
        /** @var ProductService $productService */
        $productService = resolve(ProductService::class);
        /** @var OfferService $offerService */
        $offerService = resolve(OfferService::class);
        /** @var PriceService $priceService */
        $priceService = resolve(PriceService::class);

        if ($merchantId) {
            $promoProducts = $referralService
                ->getPromotions((new GetPromotionDto())->setReferralId($merchantId));
        } else {
            $promoProducts = $referralService
                ->getMassPromotions((new GetPromotionDto())->setMass(true));
        }

        $productIds = $promoProducts->pluck('product_id');
        $result = $promoProducts->toArray();
        if ($productIds->isNotEmpty()) {
            $products = $productService
                ->products(
                    (new RestQuery())
                        ->setFilter('id', $productIds->all())
                        ->include('brand', 'category')
                        ->addFields(ProductDto::entity(), 'id', 'brand_id', 'category_id', 'name')
                        ->addFields(BrandDto::entity(), 'id', 'name')
                        ->addFields(CategoryDto::entity(), 'id', 'name')
                )
                ->keyBy('id');
            $offers = $offerService->offersByProduct($productIds->all(), null, true)->pluck('id', 'product_id');

            $prices = $priceService
                ->prices((new PricesInDto())->setOffers($offers->values()->all()))
                ->keyBy('offer_id');

            foreach ($result as &$promoProduct) {
                $product = $products->get($promoProduct['product_id']);
                if ($product) {
                    $promoProduct['brand'] = $product->brand;
                    $promoProduct['category'] = $product->category;
                    $promoProduct['product_name'] = $product->name;
                }
                if ($offers->has($promoProduct['product_id']) && $prices->has($offers[$promoProduct['product_id']])) {
                    $promoProduct['price'] = $prices[$offers[$promoProduct['product_id']]]->price;
                }
            }
        }

        return $result;
    }

    public function save(?int $merchantId, array $data): void
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);
        /** @var ProductService $productService */
        $productService = resolve(ProductService::class);

        $product = $productService->newQuery()
            ->setFilter('id', $data['product_id'])
            ->products();
        if ($product->isEmpty()) {
            throw new BadRequestHttpException('Ошибка: товар не найден');
        }

        $promotionDto = new PutPromotionDto();
        $promotionDto
            ->setProductId($data['product_id'])
            ->setMass((bool) $data['mass'])
            ->setActive((bool) $data['active'])
            ->setFiles($data['files'] ?? [])
            ->setDescription($data['description'])
            ->setCustomerId($merchantId);

        if ($merchantId) {
            $referralService->putPromotions($promotionDto);
        } else {
            $referralService->putMassPromotions($promotionDto);
        }
    }
}
