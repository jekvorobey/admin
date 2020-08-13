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
use Pim\Dto\PropertyDto;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertiesController extends Controller
{
    /**
     * Страница со списком всех товарных атрибутов
     * @param ProductService $productService
     * @return mixed
     */
    public function list(ProductService $productService)
    {
        $restQuery = $productService->newQuery()
            ->addFields(PropertyDto::entity(),
                'id',
                'name',
                'code',
                'type'
            );

        $this->title = 'Справочник товарных атрибутов';
        return $this->render('Product/PropertiesList', [
            'iProperties' => $productService->getProperties($restQuery)
        ]);
    }

    /**
     * Страница с детальной информацией о товарном атрибуте
     * @param string $propCode
     * @return mixed
     * @throws PimException
     */
    public function detail(string $propCode)
    {
        /** @var PropertyDto $productProperty */
        $productProperty = $this->getPropData($propCode);

        if (!$productProperty) {
            throw new NotFoundHttpException();
        }

        $this->title = "Редактирование товарного атрибута";
        return $this->render('Product/PropertyDetail', [
            'iProperty' => $productProperty,
            'iCategories' => $this->getCategoriesData(),
            'property_types' => PropertyDto::getTypes()
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
            'property_types' => PropertyDto::getTypes()
        ]);
    }

    /**
     * Отправить запрос на сохранение редактируемого товарного атрибута
     * @param ProductService $productService
     * @return Application|ResponseFactory|Response
     */
    public function update(ProductService $productService)
    {
        $data = $this->validate(request(), [
            'id' => 'present|nullable|integer',
            'name' => 'required|string',
            'display_name' => 'required|string',
            'type' => ['required', Rule::in(
                array_keys(PropertyDto::getTypes())
            )],
            'is_filterable' => 'required|boolean',
            'is_multiple' => 'required|boolean',
            'is_color' => 'required|boolean',
            'categories' => 'present|json'
        ]);

        $propertyDto = $this->fulfillDto($data);
        $productService->updateProperty($propertyDto);

        return response('', 204);
    }

    /**
     * Отправить запрос на удаление товарного атрибута и всех связанных с ним данных
     * @param int $propertyId
     * @param ProductService $productService
     * @return Application|ResponseFactory|Response
     */
    public function delete(int $propertyId, ProductService $productService)
    {
        $productService->deleteProperty($propertyId);

        return response('', 204);
    }

    /**
     * Вспомогательный метод, подгружающий детальную информацию о товарном атрибуте
     * @param string $code
     * @return mixed
     */
    protected function getPropData(string $code)
    {
        $productService = resolve(ProductService::class);
        $restQuery = $productService->newQuery()
            ->include('categoryPropertyLinks')
            ->addFields(PropertyDto::entity(),
                'id',
                'name',
                'display_name',
                'code',
                'type',
                'is_filterable',
                'is_multiple',
                'is_color',
                'updated_at'
            )->setFilter('code', $code);

        return $productService->getProperties($restQuery)->first();
    }

    /**
     * Вспомогательный метод, подгружающий информацию о продуктовых категориях
     * @return Collection|CategoryDto[]
     * @throws PimException
     */
    public function getCategoriesData()
    {
        $categoryService = resolve(CategoryService::class);
        $categoriesQuery = $categoryService->newQuery()
            ->addFields(CategoryDto::entity(),
                'id',
                'name',
                'parent_id',
                '_lft',
                '_rgt'
            );

        return $categoryService->categories($categoriesQuery);
    }

    /**
     * Вспомогательный метод, заполняющий DTO полями из request
     * @param array $data
     * @return PropertyDto
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

        return $dto;
    }
}