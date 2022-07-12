<?php

namespace App\Http\Controllers\Content\PopularBrand;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\PopularBrandDto;
use Cms\Services\PopularBrandService\PopularBrandService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Pim\Core\PimException;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;

class PopularBrandController extends Controller
{
    /**
     * Список всех популярных брендов
     * @return mixed
     * @throws CmsException|PimException
     */
    public function list(PopularBrandService $popularBrandService, BrandService $brandService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $popularBrands = $popularBrandService->popularBrands(
            (new RestQuery())
                ->addSort('order_num')
        );

        $popularBrandsIds = $popularBrands->pluck('brand_id')->all();
        $brands = $brandService->brands(
            (new RestQuery())
                ->addFields(BrandDto::entity(), 'id', 'name', 'file_id')
        );

        $groupedBrands = $brands->groupBy(function (BrandDto $brand) use ($popularBrandsIds) {
            return in_array($brand->id, $popularBrandsIds) ? 'popular' : 'other';
        });

        $brandsForPopularBrands = isset($groupedBrands['popular']) ? $groupedBrands['popular']->keyBy('id') : [];
        $otherBrands = $groupedBrands['other'] ?? [];

        $this->title = 'Популярные бренды';

        return $this->render('Content/PopularBrands', [
            'iPopularBrands' => $popularBrands->map(function (PopularBrandDto $popularBrand) use ($brandsForPopularBrands) {
                return [
                    'id' => $popularBrand->id,
                    'brand_id' => $popularBrand->brand_id,
                    'file_id' => $brandsForPopularBrands[$popularBrand->brand_id]->file_id,
                    'name' => $brandsForPopularBrands[$popularBrand->brand_id]->name,
                    'show_logo' => $popularBrand->show_logo,
                    'order_num' => $popularBrand->order_num,
                ];
            }),
            'iBrands' => $otherBrands,
        ]);
    }

    /**
     * Добавить новый популярный бренд
     * @throws CmsException
     */
    public function create(PopularBrandService $popularBrandService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'brand_id' => 'required|integer',
            'show_logo' => 'required|boolean',
        ]);

        $popularBrand = new PopularBrandDto();
        $popularBrand->brand_id = $data['brand_id'];
        $popularBrand->show_logo = $data['show_logo'];

        $createdPopularBrand = $popularBrandService->create($popularBrand);

        return response()->json([
            'popular_brand' => $createdPopularBrand->toArray(),
        ], 201);
    }

    /**
     * Редактировать популярный бренд
     * @throws CmsException
     */
    public function update(PopularBrandService $popularBrandService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'brand_id' => 'required|integer',
            'show_logo' => 'required|boolean',
        ]);

        $popularBrand = new PopularBrandDto();
        $popularBrand->id = $data['id'];
        $popularBrand->brand_id = $data['brand_id'];
        $popularBrand->show_logo = $data['show_logo'];

        $popularBrandService->update($popularBrand);

        return response('', 204);
    }

    /**
     * Удалить популярный бренд
     * @throws CmsException
     */
    public function delete(PopularBrandService $popularBrandService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $popularBrandService->delete($data['ids']);

        return response('', 204);
    }

    /**
     * Изменить порядок популярных брендов
     */
    public function reorder(PopularBrandService $popularBrandService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order_num' => 'required|integer',
        ]);

        $popularBrandService->reorder($data);

        return response('', 204);
    }
}
