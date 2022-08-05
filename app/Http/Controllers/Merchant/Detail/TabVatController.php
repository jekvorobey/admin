<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\VatDto;
use MerchantManagement\Services\MerchantService\Dto\GetVatDto;
use MerchantManagement\Services\MerchantService\Dto\SaveVatDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Core\PimException;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TabVatController extends Controller
{
    /**
     * @throws PimException
     */
    public function load(int $id): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        [$productVats, $merchantVat, $brands, $categories, $products] = $this->loadVat($id);

        return response()->json([
            'vats' => $productVats,
            'merchantVat' => $merchantVat,
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @throws PimException
     */
    public function saveVat(int $id, MerchantService $merchantService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $types = [VatDto::TYPE_MERCHANT, VatDto::TYPE_BRAND, VatDto::TYPE_CATEGORY, VatDto::TYPE_SKU];
        $data = $this->validate(request(), [
            'id' => 'nullable|integer',
            'type' => ['required', Rule::in($types)],
            'value' => 'nullable|numeric|max:100',
            'related_id' => 'nullable',
        ], [
            'type' => 'тип',
            'value' => 'НДС',
            'related_id' => 'связанная сущность',
        ]);

        $dto = new SaveVatDto();
        $dto->setId($data['id'] ?? null);
        $dto->setType($data['type']);
        $dto->setValue($data['value']);
        $dto->setMerchantId($id);

        if ($data['type'] != VatDto::TYPE_MERCHANT) {
            if (!$data['related_id']) {
                throw new BadRequestHttpException('Не заполнена связанная сущность');
            }

            switch ($data['type']) {
                case VatDto::TYPE_BRAND:
                    $dto->setBrandId($data['related_id']);
                    break;
                case VatDto::TYPE_CATEGORY:
                    $dto->setCategoryId($data['related_id']);
                    break;
                case VatDto::TYPE_SKU:
                    $dto->setProductId($data['related_id']);
                    break;
            }
        }

        $merchantService->saveVat($dto);

        [$productVats, $merchantVat, $brands, $categories, $products] = $this->loadVat($id);

        return response()->json([
            'vats' => $productVats,
            'merchantVat' => $merchantVat,
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @throws PimException
     */
    public function removeVat(int $id, MerchantService $merchantService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate(request(), [
            'id' => 'required',
        ]);

        $merchantService->removeVat($data['id']);

        [$productVats, $merchantVat, $brands, $categories, $products] = $this->loadVat($id);

        return response()->json([
            'vats' => $productVats,
            'merchantVat' => $merchantVat,
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @throws PimException
     */
    protected function loadVat(int $id): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $vats = $merchantService->vats(
            (new GetVatDto())
                ->addType(VatDto::TYPE_MERCHANT)
                ->addType(VatDto::TYPE_BRAND)
                ->addType(VatDto::TYPE_CATEGORY)
                ->addType(VatDto::TYPE_SKU)
                ->setMerchantId($id)
        );

        $merchantVat = [
            'id' => null,
            'value' => '',
        ];
        $productVats = collect();

        $brandIds = [];
        $categoryIds = [];
        $productIds = [];
        foreach ($vats as $vat) {
            if ($vat->type == VatDto::TYPE_MERCHANT) {
                $merchantVat = [
                    'id' => $vat->id,
                    'value' => $vat->value,
                ];
            } else {
                $related_id = null;
                if ($vat->type == VatDto::TYPE_BRAND) {
                    $related_id = $vat->brand_id;
                    $brandIds[] = $vat->brand_id;
                } elseif ($vat->type == VatDto::TYPE_CATEGORY) {
                    $related_id = $vat->category_id;
                    $categoryIds[] = $vat->category_id;
                } elseif ($vat->type == VatDto::TYPE_SKU) {
                    $related_id = $vat->product_id;
                    $productIds[] = $vat->product_id;
                }

                if ($related_id) {
                    $productVats->push([
                        'id' => $vat->id,
                        'type' => $vat->type,
                        'value' => $vat->value,
                        'related_id' => $related_id,
                    ]);
                }
            }
        }

        $brands = [];
        $categories = [];
        $products = [];
        if ($brandIds) {
            /** @var BrandService $brandService */
            $brandService = resolve(BrandService::class);
            $brands = $brandService->brands((new RestQuery())->setFilter('id', $brandIds))->pluck('name', 'id');
        }
        if ($categoryIds) {
            /** @var CategoryService $categoryService */
            $categoryService = resolve(CategoryService::class);
            $categories = $categoryService->categories((new RestQuery())->setFilter('id', $categoryIds))->pluck('name', 'id');
        }
        if ($productIds) {
            /** @var ProductService $productService */
            $productService = resolve(ProductService::class);
            $products = $productService->products((new RestQuery())->setFilter('id', $productIds))->pluck('name', 'id');
        }

        return [$productVats, $merchantVat, $brands, $categories, $products];
    }
}
