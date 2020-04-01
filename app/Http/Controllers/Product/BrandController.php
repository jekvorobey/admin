<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Services\BrandService\BrandService;

class BrandController extends Controller
{
    public function list(Request $request, BrandService $brandService)
    {
        $page = $request->get('page', 1);
        $query = $brandService->newQuery()->pageNumber($page, 10);
        
        $total =$brandService->brandsCount($query);
        $brands = $brandService->brands($query);
        
        return $this->render('Product/BrandList', [
            'iBrands' => $brands,
            'iTotal' => $total,
            'iCurrentPage' => $page,
        ]);
    }
}