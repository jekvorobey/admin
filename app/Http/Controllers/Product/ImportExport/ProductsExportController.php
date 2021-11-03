<?php

namespace App\Http\Controllers\Product\ImportExport;

use App\Http\Controllers\Controller;
use Greensight\CatalogImport\Services\ProductsImportService\ProductsImportService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\SearchService\SearchService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductsExportController extends Controller
{
    /**
     * Экспорт выбранных товаров в файлы Excel
     */
    public function exportByProductIds(Request $request, ProductsImportService $productsImportService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
        ]);

        $fileData = $productsImportService->exportByProductIds($data['product_ids']);

        [$originalUrl, $originalName] = self::getOriginalFileUrlAndName($fileData['file_id']);

        return response()->json([
            'path' => $originalUrl,
            'name' => $originalName,
        ]);
    }

    /**
     * Экспорт отфильтрованных товаров в файлы Excel
     */
    public function exportByFilters(
        Request $request,
        SearchService $searchService,
        ProductsImportService $productsImportService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'filters' => 'required|array',
        ]);

        $query = (new ProductQuery())
            ->setFilters($data['filters']);
        $query->segment = 1;// todo
        $query->role = UserDto::SHOWCASE__GUEST;
        $query->fields([
            ProductQuery::PRODUCT_ID,
        ]);

        $productsSearchResult = $searchService->products($query);
        $productIds = collect($productsSearchResult->products)->pluck('id')->all();

        $fileData = $productsImportService->exportByProductIds($productIds);

        [$originalUrl, $originalName] = self::getOriginalFileUrlAndName($fileData['file_id']);

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
