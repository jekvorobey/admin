<?php

namespace App\Http\Controllers\Product\ImportExport;

use App\Http\Controllers\Controller;
use Greensight\CatalogImport\Services\ProductsService\ProductsCatalogImportService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Pim\Dto\Search\ProductQuery;
use Pim\Services\SearchService\SearchService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductsExportController extends Controller
{
    /**
     * Экспорт выбранных товаров в файлы Excel
     * @param Request $request
     * @param ProductsCatalogImportService $productsCatalogImportService
     * @return JsonResponse
     */
    public function exportByProductIds(Request $request, ProductsCatalogImportService $productsCatalogImportService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer',
        ]);

        $fileData = $productsCatalogImportService->exportByProductIds(0, $data['product_ids']);

        [$originalUrl, $originalName] = self::getOriginalFileUrlAndName($fileData->id);

        return response()->json([
            'path' => $originalUrl,
            'name' => $originalName,
        ]);
    }

    /**
     * Экспорт отфильтрованных товаров в файлы Excel
     * @param Request $request
     * @param SearchService $searchService
     * @param ProductsCatalogImportService $productsCatalogImportService
     * @return JsonResponse
     */
    public function exportByFilters(
        Request $request,
        SearchService $searchService,
        ProductsCatalogImportService $productsCatalogImportService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'filters' => 'required|array',
        ]);

        $query = (new ProductQuery())
            ->setFilters($data['filters']);
        $query->segment = 1;// todo
        $query->role = RoleDto::ROLE_SHOWCASE_GUEST;
        $query->fields([
            ProductQuery::PRODUCT_ID,
        ]);

        $productsSearchResult = $searchService->products($query);
        $productIds = collect($productsSearchResult->products)->pluck('id')->all();

        $fileData = $productsCatalogImportService->exportByProductIds(0, $productIds);

        [$originalUrl, $originalName] = self::getOriginalFileUrlAndName($fileData->id);

        return response()->json([
            'path' => $originalUrl,
            'name' => $originalName,
        ]);
    }

    /**
     * Получить оригинальный путь к файлу и его имя по id-шнику
     * @param int $fileId
     * @return array
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
