<?php

namespace App\Http\Controllers\Product\ImportExport;

use App\Http\Controllers\Controller;
use Greensight\CatalogImport\Services\ProductsService\ProductsCatalogImportService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductsSelectionExportController extends Controller
{
    /**
     * Экспорт выбранных товаров в файлы Excel по id торгового предложения
     */
    public function exportByOfferIds(
        Request $request,
        ProductsCatalogImportService $productsCatalogImportService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'offer_ids' => 'required|array',
            'offer_ids.*' => 'integer',
        ]);

        $fileData = $productsCatalogImportService->exportByOfferIds(0, $data['offer_ids']);

        return response()->json([
            'path' => $fileData->url,
        ]);
    }

    /**
     * Экспорт выбранных товаров в файлы Excel по артикулу
     */
    public function exportByVendorCodes(
        Request $request,
        ProductsCatalogImportService $productsCatalogImportService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'vendor_codes' => 'required|array'
        ]);

        $fileData = $productsCatalogImportService->exportByVendorCodes(0, $data['vendor_codes']);

        return response()->json([
            'path' => $fileData->url,
        ]);
    }
}
