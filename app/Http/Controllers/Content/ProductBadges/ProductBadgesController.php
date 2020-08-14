<?php

namespace App\Http\Controllers\Content\ProductBadges;

use App\Http\Controllers\Controller;
use Cms\Dto\ProductBadgeDto;
use Cms\Services\ContentBadgesService\ContentBadgesService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProductBadgesController extends Controller
{
    /**
     * Список всех товарных ярлыков
     * @param ContentBadgesService $badgesService
     * @return mixed
     */
    public function list(ContentBadgesService $badgesService)
    {
        $badges = $badgesService->productBadges();

        $this->title = 'Справочник товарных шильдиков';

        return $this->render('Content/ProductBadges', [
            'iBadges' => $badges
        ]);
    }

    /**
     * Добавить новый продуктовый ярлык
     * @param ContentBadgesService $badgesService
     * @return JsonResponse
     */
    public function add(ContentBadgesService $badgesService)
    {
        $data = $this->validate(request(), [
            'text' => 'required|string'
        ]);

        $badgeDto = new ProductBadgeDto();
        $badgeDto->text = $data['text'];

        $badgesService->createProductBadge($badgeDto);

        $badges = $badgesService->productBadges();

        return response()->json([
            'badges' => $badges
        ]);

    }

    /**
     * Редактировать продуктовый ярлык
     * @param ContentBadgesService $badgesService
     * @return JsonResponse
     */
    public function edit(ContentBadgesService $badgesService)
    {
        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'text' => 'required|string',
        ]);

        $badgeDto = new ProductBadgeDto();
        $badgeDto->text = $data['text'];

        $badgesService->updateProductBadge($data['id'], $badgeDto);

        $badges = $badgesService->productBadges();

        return response()->json([
            'badges' => $badges
        ]);

    }

    /**
     * Изменить порядок продуктовых ярлыков
     * @param ContentBadgesService $badgesService
     * @return Application|ResponseFactory|Response
     */
    public function reorder(ContentBadgesService $badgesService)
    {
        $data = $this->validate(request(), [
            'items' => 'required|json',
        ]);

        $badgesService->reorderProductBadges($data);

        return response('', 204);
    }

    /**
     * Удалить продуктовый ярлык
     * @param ContentBadgesService $badgesService
     * @return Application|ResponseFactory|Response
     */
    public function remove(ContentBadgesService $badgesService)
    {
        $data = $this->validate(request(), [
            'id' => 'required|integer',
        ]);

        $badgesService->deleteProductBadge($data['id']);

        return response('', 204);
    }
}