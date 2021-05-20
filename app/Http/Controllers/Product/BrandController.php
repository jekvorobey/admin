<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BrandController extends Controller
{
    public function list(Request $request, BrandService $brandService)
    {
        $page = $request->get('page', 1);
        [$total, $brands] = $this->loadBrands($brandService, $page);

        return $this->render('Product/BrandList', [
            'iBrands' => $brands,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, BrandService $brandService)
    {
        $page = $request->get('page', 1);
        [$total, $brands] = $this->loadBrands($brandService, $page);

        return response()->json([
            'brands' => $brands,
            'total' => $total['total'],
        ]);
    }

    public function save(Request $request, BrandService $brandService)
    {
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

    public function delete(Request $request, BrandService $brandService)
    {
        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        $brandService->deleteBrands($ids);

        return response()->json();
    }

    /**
     * @param BrandService $brandService
     * @param $page
     * @return array
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
