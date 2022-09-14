<?php

namespace App\Http\Controllers\Product\ImportExport;

use App\Http\Controllers\Controller;
use Greensight\CatalogImport\Services\ProductsService\ProductsCatalogImportService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
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

        [$originalUrl, $originalName] = self::getOriginalFileUrlAndName($fileData->id);

        return response()->json([
            'path' => $originalUrl,
            'name' => $originalName,
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
            'vendor_codes' => 'required|array',
            'vendor_codes.*' => 'integer',
        ]);

        $fileData = $productsCatalogImportService->exportByVendorCodes(0, $data['vendor_codes']);

        [$originalUrl, $originalName] = self::getOriginalFileUrlAndName($fileData->id);

        return response()->json([
            'path' => $originalUrl,
            'name' => $originalName,
        ]);
    }

    /**
     * Получить оригинальный путь к файлу и его имя по id-шнику
     */
    protected static function getOriginalFileUrlAndName(int $fileId): array
    {
        /** @var FileService $fileService */
        $fileService = resolve(FileService::class);

        /** @var FileDto $fileDto */
        $fileDto = $fileService->getFiles([$fileId])->first();

        return [$fileDto->originalUrl(), $fileDto->original_name];
    }
}
