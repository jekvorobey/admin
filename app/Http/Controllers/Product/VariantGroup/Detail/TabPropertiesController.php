<?php

namespace App\Http\Controllers\Product\VariantGroup\Detail;

use App\Http\Controllers\Product\VariantGroup\VariantGroupDetailController;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Pim\Core\PimException;
use Pim\Dto\Product\VariantGroupDto;
use Pim\Dto\PropertyDto;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PropertyDirectoryValueService\PropertyDirectoryValueService;

/**
 * Class TabPropertiesController
 * @package App\Http\Controllers\Product\VariantGroup\Detail
 */
class TabPropertiesController extends VariantGroupDetailController
{
    /** @var ProductService */
    protected $productService;
    /** @var PropertyDirectoryValueService */
    protected $propertyDirectoryValueService;

    /**
     * TabPropertiesController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->productService = resolve(ProductService::class);
        $this->propertyDirectoryValueService = resolve(PropertyDirectoryValueService::class);
    }

    /**
     * @throws PimException
     */
    public function load(int $variantGroupId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $propertiesQuery = $this->productService->newQuery()
            ->addFields(PropertyDto::entity(), 'id', 'name');
        $properties = $this->productService->getProperties($propertiesQuery);

        $variantGroupQuery = $this->variantGroupService
            ->newQuery()
            ->addFields(
                VariantGroupDto::entity(),
                'id',
                'properties_count',
                'updated_at'
            )
            ->include('products.properties.property', 'properties');
        $variantGroupDto = $this->variantGroupService->variantGroup($variantGroupId, $variantGroupQuery);
        $gluedPropertyIds = $variantGroupDto->properties->pluck('id')->all();
        $propertyDirectoryValueIds = [];
        $gluedPropertyValues = [];
        foreach ($variantGroupDto->products as $productDto) {
            foreach ($productDto->properties as $productPropertyValueDto) {
                if (!in_array($productPropertyValueDto->property_id, $gluedPropertyIds)) {
                    continue;
                }
                if (
                    !in_array($productPropertyValueDto->value, $propertyDirectoryValueIds) &&
                    $productPropertyValueDto->property->type == PropertyDto::TYPE_DIRECTORY
                ) {
                    $propertyDirectoryValueIds[] = $productPropertyValueDto->value;
                }

                if (
                    !isset($gluedPropertyValues[$productPropertyValueDto->property_id]) ||
                    !in_array($productPropertyValueDto->value, $gluedPropertyValues[$productPropertyValueDto->property_id])
                ) {
                    $gluedPropertyValues[$productPropertyValueDto->property_id][] = $productPropertyValueDto->value;
                }
            }
        }
        $propertyDirectoryValues = collect();
        if ($propertyDirectoryValueIds) {
            $propertyDirectoryValuesQuery = $this->propertyDirectoryValueService->newQuery()
                ->setFilter('id', $propertyDirectoryValueIds);
            /** @var Collection|Collection[] $propertyDirectoryValues */
            $propertyDirectoryValues = $this->propertyDirectoryValueService
                ->values($propertyDirectoryValuesQuery)
                ->keyBy('id');
        }

        return response()->json([
            'variantGroup' => [
                'id' => $variantGroupDto->id,
                'updated_at' => $variantGroupDto->updated_at,
                'properties_count' => $variantGroupDto->properties_count,
            ],
            'allProperties' => $properties,
            'usedProperties' => $variantGroupDto->properties->map(function (PropertyDto $propertyDto) use ($gluedPropertyValues, $propertyDirectoryValues) {
                $usedValues = isset($gluedPropertyValues[$propertyDto->id]) ?
                    array_values(Arr::sort(array_map(function ($value) use ($propertyDto, $propertyDirectoryValues) {
                        return $propertyDto->type == PropertyDto::TYPE_DIRECTORY && $propertyDirectoryValues->has($value) ?
                            [
                                'property_directory_value_id' => $propertyDirectoryValues[$value]->id,
                                'name' => $propertyDirectoryValues[$value]->name,
                                'color' => $propertyDto->is_color ? $propertyDirectoryValues[$value]->code : '',
                            ] :
                            [
                                'property_directory_value_id' => null,
                                'name' => $value,
                                'color' => '',
                            ];
                    }, $gluedPropertyValues[$propertyDto->id]), function ($value) {
                        return is_int($value['name']) ? (int) $value['name'] : $value['name'];
                    })) : [];

                return [
                    'id' => $propertyDto->id,
                    'name' => $propertyDto->name,
                    'isColor' => (bool) array_filter(array_column($usedValues, 'color')),
                    'usedValues' => $usedValues,
                ];
            }),
        ]);
    }

    /**
     * @throws Exception
     */
    public function add(int $variantGroupId, int $propertyId): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $this->variantGroupService->addProperty($variantGroupId, $propertyId);

        return $this->load($variantGroupId);
    }

    /**
     * @throws Exception
     */
    public function delete(int $variantGroupId, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'propertyIds' => ['array', 'required'],
            'propertyIds.*' => ['integer'],
        ]);
        $this->variantGroupService->deleteProperties($variantGroupId, $data['propertyIds']);

        return $this->load($variantGroupId);
    }
}
