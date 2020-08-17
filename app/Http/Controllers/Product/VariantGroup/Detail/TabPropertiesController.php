<?php

namespace App\Http\Controllers\Product\VariantGroup\Detail;

use App\Http\Controllers\Product\VariantGroup\VariantGroupDetailController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\PropertyDirectoryValueDto;
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
     * @param  int  $variantGroupId
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function load(int $variantGroupId): JsonResponse
    {
        $propertiesQuery = $this->productService->newQuery()
            ->setFilter('type', PropertyDto::TYPE_DIRECTORY)
            ->addFields(PropertyDto::entity(), 'id', 'name');
        $properties = $this->productService->getProperties($propertiesQuery);

        $variantGroupQuery = $this->variantGroupService
            ->newQuery()
            ->include('products.properties', 'properties');
        $variantGroupDto = $this->variantGroupService->variantGroup($variantGroupId, $variantGroupQuery);
        $propertyDirectoryValueIds = [];
        foreach ($variantGroupDto->products as $productDto) {
            foreach ($productDto->properties as $productPropertyValueDto) {
                if (!in_array($productPropertyValueDto->value, $propertyDirectoryValueIds)) {
                    $propertyDirectoryValueIds[] = $productPropertyValueDto->value;
                }
            }
        }
        $propertyDirectoryValuesQuery = $this->propertyDirectoryValueService->newQuery()
            ->setFilter('id', $propertyDirectoryValueIds);
        /** @var Collection|Collection[] $propertyDirectoryValues */
        $propertyDirectoryValues = $this->propertyDirectoryValueService
            ->values($propertyDirectoryValuesQuery)
            ->groupBy('property_id');

        return response()->json([
            'allProperties' => $properties,
            'usedProperties' => $variantGroupDto->properties->map(function (PropertyDto $propertyDto) use ($propertyDirectoryValues) {
                return [
                    'id' => $propertyDto->id,
                    'name' => $propertyDto->name,
                    'usedValues' => $propertyDirectoryValues->has($propertyDto->id) ?
                        $propertyDirectoryValues[$propertyDto->id]->map(function (PropertyDirectoryValueDto $propertyDirectoryValueDto) {
                            return [
                                'id' => $propertyDirectoryValueDto->id,
                                'name' => $propertyDirectoryValueDto->name,
                            ];
                        }) : []
                ];
            })
        ]);
    }

    /**
     * @param  int  $variantGroupId
     * @param  int  $propertyId
     * @return JsonResponse
     * @throws \Exception
     */
    public function add(int $variantGroupId, int $propertyId): JsonResponse
    {
        $this->variantGroupService->addProperty($variantGroupId, $propertyId);

        return response()->json([
            'variantGroup' => $this->getVariantGroup($variantGroupId),
        ]);
    }

    /**
     * @param  int  $variantGroupId
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(int $variantGroupId, Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'propertyIds' => ['array', 'required'],
            'propertyIds.*' => ['integer'],
        ]);
        $this->variantGroupService->deleteProperties($variantGroupId, $data['propertyIds']);

        return response()->json([
            'variantGroup' => $this->getVariantGroup($variantGroupId),
        ]);
    }
}
