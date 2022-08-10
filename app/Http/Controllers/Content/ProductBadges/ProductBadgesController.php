<?php

namespace App\Http\Controllers\Content\ProductBadges;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductBadgeDto;
use Cms\Services\ContentBadgesService\ContentBadgesService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Pim\Services\ProductService\ProductService;

class ProductBadgesController extends Controller
{
    private array $replacements = [
        '{plus}' => '+',
        '{equal}' => '=',
    ];

    /**
     * Список всех товарных ярлыков
     * @return mixed
     */
    public function list(ContentBadgesService $badgesService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $badges = $badgesService->productBadges();

        $this->title = 'Справочник товарных шильдиков';

        return $this->render('Content/ProductBadges', [
            'iBadges' => $badges,
        ]);
    }

    /**
     * Добавить новый продуктовый ярлык
     */
    public function add(ContentBadgesService $badgesService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'text' => 'required|string',
        ]);

        foreach ($this->replacements as $code => $symbol) {
            $data['text'] = str_replace($code, $symbol, $data['text']);
        }

        $badgeDto = new ProductBadgeDto();
        $badgeDto->text = $data['text'];

        $badgesService->createProductBadge($badgeDto);

        $badges = $badgesService->productBadges();

        return response()->json([
            'badges' => $badges,
        ]);
    }

    /**
     * Редактировать продуктовый ярлык
     */
    public function edit(ContentBadgesService $badgesService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'text' => 'required|string',
        ]);

        foreach ($this->replacements as $code => $symbol) {
            $data['text'] = str_replace($code, $symbol, $data['text']);
        }

        $badgeDto = new ProductBadgeDto();
        $badgeDto->text = $data['text'];

        $badgesService->updateProductBadge($data['id'], $badgeDto);

        $badges = $badgesService->productBadges();

        return response()->json([
            'badges' => $badges,
        ]);
    }

    /**
     * Изменить порядок продуктовых ярлыков
     */
    public function reorder(ContentBadgesService $badgesService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'items' => 'required|json',
        ]);

        $badgesService->reorderProductBadges($data);

        return response('', 204);
    }

    /**
     * Удалить продуктовый ярлык и его связи с товарами
     */
    public function remove(ContentBadgesService $badgesService, ProductService $productService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
        ]);

        # Сперва ярлык убирается со всех товаров
        $productService->forgetBadge($data['id']);
        # Затем безопасно удаляется из Справочника ярлыков
        $badgesService->deleteProductBadge($data['id']);

        return response('', 204);
    }
}
