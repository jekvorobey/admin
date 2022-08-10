<?php

namespace App\Http\Controllers\Content\Menu;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\MenuDto;
use Cms\Services\MenuService\MenuService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuDetailController extends Controller
{
    /**
     * @return mixed
     * @throws CmsException
     */
    public function index(int $id, MenuService $menuService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $menu = $this->getMenu($id, $menuService);

        return $this->render('Content/MenuDetail', [
            'iMenu' => $menu,
            'options' => [],
        ]);
    }

    /**
     * @throws CmsException
     */
    public function update($id, Request $request, MenuService $menuService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'id' => 'integer|required',
            'items' => 'array|required',
        ]);

        $validatedData['id'] = $id;

        $menuService->updateItemsTree($validatedData['id'], new MenuDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @return mixed
     * @throws CmsException
     */
    private function getMenu(int $id, MenuService $menuService)
    {
        $query = $menuService->newQuery();
        $query->setFilter('id', $id);
        $query->include('all_items');

        return $menuService->menus($query)->first();
    }
}
