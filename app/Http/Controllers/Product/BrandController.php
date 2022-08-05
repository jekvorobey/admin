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
        [$total, $brands] = $this->loadBrands($brandService, $page);

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
        [$total, $brands] = $this->loadBrands($brandService, $page);

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
    private function loadBrands(BrandService $brandService, $page): array
    {
        $query = $brandService->newQuery()->pageNumber($page, 10);

        $total = $brandService->brandsCount($query);
        $brands = $brandService->brands($query);
        return [$total, $brands];
    }
}
