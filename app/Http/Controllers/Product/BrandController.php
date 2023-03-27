<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BrandController extends Controller
{
    /**
     * @throws PimException
     */
    public function list(Request $request, BrandService $brandService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $page = $request->get('page', 1);
        [$total, $brands] = $this->loadBrands($request, $brandService, $page);

        return $this->render('Product/BrandList', [
            'iBrands' => $brands,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    /**
     * @throws PimException
     */
    public function page(Request $request, BrandService $brandService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $page = $request->get('page', 1);
        [$total, $brands] = $this->loadBrands($request, $brandService, $page);

        return response()->json([
            'brands' => $brands,
            'total' => $total['total'],
        ]);
    }

    public function save(Request $request, BrandService $brandService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $id = $request->get('id');
        $brand = $request->get('brand');

        if (!$brand) {
            throw new BadRequestHttpException('brand required');
        }

        $brandDto = new BrandDto($brand);

        if ($id) {
            $brandService->updateBrand($id, $brandDto);
        } else {
            $brandService->createBrand($brandDto);
        }

        return response()->json();
    }

    public function delete(Request $request, BrandService $brandService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        $brandService->deleteBrands($ids);

        return response()->json();
    }

    /**
     * @throws PimException
     */
    private function loadBrands(Request $request, BrandService $brandService, $page): array
    {
        $requestData = $request->validate([
            'filter' => 'nullable|array',
            'filter.id' => 'nullable|numeric',
            'filter.name' => 'nullable|string|max:255',
            'filter.code' => 'nullable|string|max:255',
            'filter.visibilityFilter' => 'nullable|in:true,false',
            'filter.activeFilter' => 'nullable|in:true,false',
        ]);
        $query = $brandService->newQuery()->pageNumber($page, 10);
        $filter = $requestData['filter'] ?? [];

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }
        if (isset($filter['name']) && $filter['name']) {
            $query->setFilter('name', 'like', "%{$filter['name']}%");
        }
        if (isset($filter['code']) && $filter['code']) {
            $query->setFilter('code', $filter['code']);
        }
        if (isset($filter['visibilityFilter']) && $filter['visibilityFilter']) {
            $query->setFilter('is_visible', filter_var($filter['activeFilter'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filter['activeFilter']) && $filter['activeFilter']) {
            $query->setFilter('active', filter_var($filter['activeFilter'], FILTER_VALIDATE_BOOLEAN));
        }

        $total = $brandService->brandsCount($query);
        $brands = $brandService->brands($query);
        return [$total, $brands];
    }
}
