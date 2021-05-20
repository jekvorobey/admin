<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Pim\Core\PimException;
use Pim\Dto\CategoryDto;
use Pim\Dto\PropertyDirectoryValueDto;
use Pim\Dto\PropertyDto;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PropertyDirectoryValueService\PropertyDirectoryValueService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertiesController extends Controller
{
    /**
     * Страница со списком всех товарных атрибутов
     * @return mixed
     */
    public function list(ProductService $productService)
    {
        $restQuery = $productService->newQuery()
            ->addFields(
                PropertyDto::entity(),
                'id',
                'name',
                'code',
                'type'
            );

        $this->title = 'Справочник товарных атрибутов';
        return $this->render('Product/PropertiesList', [
            'iProperties' => $productService->getProperties($restQuery),
        ]);
    }

    /**
     * Страница с детальной информацией о товарном атрибуте
     * @return mixed
     * @throws PimException
     */
    public function detail(string $propCode)
    {
        $productProperty = $this->getPropData($propCode);

        $this->title = "Редактирование товарного атрибута";
        return $this->render('Product/PropertyDetail', [
            'iProperty' => $productProperty,
            'iCategories' => $this->getCategoriesData(),
            'property_types' => PropertyDto::getTypes(),
        ]);
    }

    /**
     * Страница создания товарного атрибута
     * @return mixed
     * @throws PimException
     */
    public function create()
    {
        $this->title = "Создание товарного атрибута";
        return $this->render('Product/PropertyDetail', [
            'iCategories' => $this->getCategoriesData(),
            'property_types' => PropertyDto::getTypes(),
        ]);
    }

    /**
     * Отправить запрос на сохранение редактируемого товарного атрибута
     * @return Application|ResponseFactory|Response
     */
    public function update(ProductService $productService)
    {
        $data = $this->validate(request(), [
            'id' => 'present|nullable|integer',
            'name' => 'required|string',
            'display_name' => 'required|string',
            'type' => [
                'required',
                Rule::in(array_keys(PropertyDto::getTypes())),
            ],
            'is_filterable' => 'required|boolean',
            'is_multiple' => 'required|boolean',
            'is_color' => 'required|boolean',
            'categories' => 'present|json',
            'old_values' => 'nullable|json',
            'new_values' => 'nullable|json',
            'measurement_unit' => 'nullable|string',
        ]);

        $propertyDto = $this->fulfillDto($data);
        $productService->updateProperty($propertyDto);

        return response('', 204);
    }

    /**
     * Отправить запрос на удаление товарного атрибута и всех связанных с ним данных
     * @return Application|ResponseFactory|Response
     */
    public function delete(int $propertyId, ProductService $productService)
    {
        $productService->deleteProperty($propertyId);

        return response('', 204);
    }

    /**
     * Подгрузить детальную информацию о товарном атрибуте
     * @return mixed
     * @throws PimException
     */
    protected function getPropData(string $code)
    {
        $productService = resolve(ProductService::class);
        $valuesService = resolve(PropertyDirectoryValueService::class);

        $propQuery = $productService->newQuery()
            ->include('categoryPropertyLinks')
            ->addFields(
                PropertyDto::entity(),
                'id',
                'name',
                'display_name',
                'code',
                'type',
                'is_filterable',
                'is_multiple',
                'is_color',
                'updated_at',
                'measurement_unit'
            )->setFilter('code', $code);

        /** @var PropertyDto $property */
        $property = $productService->getProperties($propQuery)->first();
        if (!$property) {
            throw new NotFoundHttpException();
        }

        # Если у товарного атрибута жестко установленные значения - они подгружаются
        $property->offsetSet('values', []);
        if ($property->type === PropertyDto::TYPE_DIRECTORY) {
            $valuesQuery = $valuesService
                ->newQuery()
                ->setFilter('property_id', $property->id)
                ->addFields(
                    PropertyDirectoryValueDto::entity(),
                    'id',
                    'name',
                    'code',
                    'property_id'
                );

            $values = $valuesService->values($valuesQuery);
            $property->offsetSet('values', $values);
        }

        return $property;
    }

    /**
     * Подгрузить информацию о продуктовых категориях
     * @return Collection|CategoryDto[]
     * @throws PimException
     */
    public function getCategoriesData()
    {
        $categoryService = resolve(CategoryService::class);
        $categoriesQuery = $categoryService->newQuery()
            ->addFields(
                CategoryDto::entity(),
                'id',
                'name',
                'parent_id',
                '_lft',
                '_rgt'
            );

        return $categoryService->categories($categoriesQuery);
    }

    /**
     * Заполнить DTO
     * @param array $data
     */
    private function fulfillDto(array $data): PropertyDto
    {
        $dto = new PropertyDto();

        $dto->id = $data['id'] ?? null;
        $dto->name = $data['name'];
        $dto->display_name = $data['display_name'];
        $dto->type = $data['type'];
        $dto->is_filterable = $data['is_filterable'];
        $dto->is_multiple = $data['is_multiple'];
        $dto->is_color = $data['is_color'];
        $dto->categoryPropertyLinks = $data['categories'] ?? json_encode([]);
        $dto->measurement_unit = $data['measurement_unit'];

        if ($dto->type === PropertyDto::TYPE_DIRECTORY) {
            $dto->old_values = $data['old_values'] ?? json_encode([]);
            $dto->new_values = $data['new_values'] ?? json_encode([]);
        }

        return $dto;
    }
}
