<?php

namespace App\Http\Controllers\Product\VariantGroup\Detail;

use App\Http\Controllers\Product\VariantGroup\VariantGroupDetailController;
use Illuminate\Http\JsonResponse;
use Pim\Dto\Product\VariantGroupDto;

/**
 * Class TabProductsController
 * @package App\Http\Controllers\Product\VariantGroup\Detail
 */
class TabProductsController extends VariantGroupDetailController
{
    /**
     * @param  int  $variantGroupId
     * @param  int  $productId
     * @return JsonResponse
     * @throws \Exception
     */
    public function add(int $variantGroupId, int $productId): JsonResponse
    {
        $this->variantGroupService->addProduct($variantGroupId, $productId);

        return response()->json([
            'variantGroup' => $this->getVariantGroup($variantGroupId),
        ]);
    }

    /**
     * @param  int  $variantGroupId
     * @param  int  $productId
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function setMain(int $variantGroupId, int $productId): JsonResponse
    {
        $variantGroupDto = new VariantGroupDto();
        $variantGroupDto->main_product_id = $productId;
        $this->variantGroupService->updateVariantGroup($variantGroupId, $variantGroupDto);

        return response()->json([
            'variantGroup' => $this->getVariantGroup($variantGroupId),
        ]);
    }

    /**
     * @param  int  $variantGroupId
     * @param  int  $productId
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(int $variantGroupId, int $productId): JsonResponse
    {
        $this->variantGroupService->deleteProduct($variantGroupId, $productId);

        return response()->json([
            'variantGroup' => $this->getVariantGroup($variantGroupId),
        ]);
    }
}
