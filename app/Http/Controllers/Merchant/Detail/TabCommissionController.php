<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use MerchantManagement\Dto\CommissionDto;
use MerchantManagement\Services\MerchantService\Dto\GetCommissionDto;
use MerchantManagement\Services\MerchantService\Dto\SaveCommissionDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Core\PimException;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TabCommissionController extends Controller
{
    public function load(int $id): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        [$productCommissions, $merchantCommission, $brands, $categories, $products] = $this->loadCommissions($id);

        return response()->json([
            'commissions' => $productCommissions,
            'merchantCommission' => $merchantCommission,
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function saveCommission(int $id, MerchantService $merchantService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $types = [CommissionDto::TYPE_MERCHANT, CommissionDto::TYPE_BRAND, CommissionDto::TYPE_CATEGORY, CommissionDto::TYPE_SKU];
        $data = $this->validate(request(), [
            'id' => 'nullable|integer',
            'type' => ['required', Rule::in($types)],
            'value' => 'required|numeric|min:0|max:100',
            'related_id' => 'nullable',
            'dates' => 'nullable|array',
        ], [
            'type' => 'тип',
            'value' => 'комиссия',
            'related_id' => 'связанная сущность',
            'dates' => 'даты активности',
        ]);

        $dto = new SaveCommissionDto();
        $dto->setId($data['id'] ?? null);
        $dto->setType($data['type']);
        $dto->setValue($data['value']);
        $dto->setMerchantId($id);

        if ($data['type'] != CommissionDto::TYPE_MERCHANT) {
            if (!$data['related_id']) {
                throw new BadRequestHttpException('Не заполнена связанная сущность');
            }

            switch ($data['type']) {
                case CommissionDto::TYPE_BRAND:
                    $dto->setBrandId($data['related_id']);
                    break;
                case CommissionDto::TYPE_CATEGORY:
                    $dto->setCategoryId($data['related_id']);
                    break;
                case CommissionDto::TYPE_SKU:
                    $dto->setProductId($data['related_id']);
                    break;
            }
        }

        if (isset($data['dates'])) {
            $data['dates'] = array_filter($data['dates']);
            if (count($data['dates']) == 2) {
                $dto->setDateStart(Carbon::createFromFormat('Y-m-d', $data['dates'][0]));
                $dto->setDateEnd(Carbon::createFromFormat('Y-m-d', $data['dates'][1]));
            }
        }

        $merchantService->saveCommission($dto);

        [$productCommissions, $merchantCommission, $brands, $categories, $products] = $this->loadCommissions($id);

        return response()->json([
            'commissions' => $productCommissions,
            'merchantCommission' => $merchantCommission,
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function removeCommission(int $id, MerchantService $merchantService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $data = $this->validate(request(), [
            'id' => 'required',
        ]);

        $merchantService->removeCommission($data['id']);

        [$productCommissions, $merchantCommission, $brands, $categories, $products] = $this->loadCommissions($id);

        return response()->json([
            'commissions' => $productCommissions,
            'merchantCommission' => $merchantCommission,
            'brands' => $brands,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @throws PimException
     */
    protected function loadCommissions(int $id): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        $commissions = $merchantService->commissions(
            (new GetCommissionDto())
                ->addType(CommissionDto::TYPE_MERCHANT)
                ->addType(CommissionDto::TYPE_BRAND)
                ->addType(CommissionDto::TYPE_CATEGORY)
                ->addType(CommissionDto::TYPE_SKU)
                ->setMerchantId($id)
        );

        $merchantCommission = [
            'id' => null,
            'value' => '',
        ];
        $productCommissions = collect();

        $brandIds = [];
        $categoryIds = [];
        $productIds = [];
        foreach ($commissions as $commission) {
            if ($commission->type == CommissionDto::TYPE_MERCHANT) {
                $merchantCommission = [
                    'id' => $commission->id,
                    'value' => $commission->value,
                ];
            } else {
                $related_id = null;
                if ($commission->type == CommissionDto::TYPE_BRAND) {
                    $related_id = $commission->brand_id;
                    $brandIds[] = $commission->brand_id;
                } elseif ($commission->type == CommissionDto::TYPE_CATEGORY) {
                    $related_id = $commission->category_id;
                    $categoryIds[] = $commission->category_id;
                } elseif ($commission->type == CommissionDto::TYPE_SKU) {
                    $related_id = $commission->product_id;
                    $productIds[] = $commission->product_id;
                }

                if ($related_id) {
                    $productCommissions->push([
                        'id' => $commission->id,
                        'type' => $commission->type,
                        'value' => $commission->value,
                        'related_id' => $related_id,
                        'dates' => [
                            $commission->date_start,
                            $commission->date_end,
                        ],
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

        return [$productCommissions, $merchantCommission, $brands, $categories, $products];
    }
}
