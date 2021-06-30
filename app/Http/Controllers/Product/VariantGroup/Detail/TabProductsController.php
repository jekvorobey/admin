<?php

namespace App\Http\Controllers\Product\VariantGroup\Detail;

use App\Http\Controllers\Product\VariantGroup\VariantGroupDetailController;
use Greensight\Marketing\Dto\Price\PriceOutDto;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\VariantGroupDto;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TabProductsController
 * @package App\Http\Controllers\Product\VariantGroup\Detail
 */
class TabProductsController extends VariantGroupDetailController
{
    /**
     * @throws \Pim\Core\PimException|\Exception
     */
    public function load(int $variantGroupId): JsonResponse
    {
        $restQuery = $this->variantGroupService
            ->newQuery()
            ->addFields(
                VariantGroupDto::entity(),
                'id',
                'name',
                'main_product_id',
                'products_count',
                'updated_at'
            )
            ->include(
                'products.category',
                'products.currentOffer',
                'products.mainImage',
                'products.brand',
                'products.properties.property',
                'products.properties.propertyDirectoryValue',
                'properties'
            );
        $variantGroupDto = $this->variantGroupService->variantGroup($variantGroupId, $restQuery);
        if (!$variantGroupDto) {
            throw new NotFoundHttpException();
        }

        $this->addVariantGroupCommonInfo($variantGroupDto);
        $this->addVariantGroupProductInfo($variantGroupDto);

        return response()->json([
            'variantGroup' => [
                'id' => $variantGroupDto->id,
                'name' => $variantGroupDto->name,
                'main_product_id' => $variantGroupDto->main_product_id,
                'updated_at' => $variantGroupDto->updated_at,
                'products_count' => $variantGroupDto->products_count,
            ],
            'products' => $variantGroupDto->products,
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function addVariantGroupProductInfo(VariantGroupDto $variantGroupDto): void
    {
        $gluedPropertiesIds = $variantGroupDto->properties->pluck('id');
        if ($variantGroupDto->products->isNotEmpty()) {
            $offerIds = [];
            foreach ($variantGroupDto->products as $productDto) {
                $productDto->created_at = date_time2str(new Carbon($productDto->created_at));
                $productDto->approval_status = ProductApprovalStatus::statusById($productDto->approval_status);
                $offerIds[] = $productDto->currentOffer ? $productDto->currentOffer->id : null;

                $productGluedProperties = [];
                foreach ($productDto->properties as $productPropertyValueDto) {
                    if ($gluedPropertiesIds->contains($productPropertyValueDto->property_id)) {
                        $productGluedProperties[] = $productPropertyValueDto;
                    }
                }
                $productDto['gluedProperties'] = $productGluedProperties;
            }
            $offerIds = array_filter($offerIds);

            if ($offerIds) {
                $stocks = $this->stockService->qtyByOffers($offerIds);
                $pricesIn = new PricesInDto();
                foreach ($offerIds as $offerId) {
                    $pricesIn->addOffer($offerId);
                }
                /** @var Collection|PriceOutDto[] $priceOutDtos */
                $priceOutDtos = $this->priceService->prices($pricesIn)->keyBy('offer_id');

                foreach ($variantGroupDto->products as $productDto) {
                    $offerId = $productDto->currentOffer ? $productDto->currentOffer->id : null;
                    $productDto['qty'] = $stocks[$offerId] ?? 0;
                    $productDto['price'] = $priceOutDtos->has($offerId) ? $priceOutDtos[$offerId]->price : 0;
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function add(int $variantGroupId, Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'productIds' => ['array', 'required'],
            'productIds.*' => ['integer'],
        ]);
        $this->variantGroupService->addProducts($variantGroupId, $data['productIds']);

        return $this->load($variantGroupId);
    }

    /**
     * @throws \Exception
     */
    public function delete(int $variantGroupId, Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'productIds' => ['array', 'required'],
            'productIds.*' => ['integer'],
        ]);
        $this->variantGroupService->deleteProducts($variantGroupId, $data['productIds']);

        return $this->load($variantGroupId);
    }

    /**
     * @throws \Pim\Core\PimException
     */
    public function setMain(int $variantGroupId, int $productId): JsonResponse
    {
        $variantGroupDto = new VariantGroupDto();
        $variantGroupDto->main_product_id = $productId;
        $this->variantGroupService->updateVariantGroup($variantGroupId, $variantGroupDto);

        return $this->load($variantGroupId);
    }
}
