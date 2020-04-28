<?php

namespace App\Http\Controllers\Content\Menu;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\MenuDto;
use Cms\Services\MenuService\MenuService;
use Illuminate\Http\Request;

class MenuDetailController extends Controller
{
    /**
     * @param int $id
     * @param MenuService $menuService
     * @return mixed
     * @throws CmsException
     */
    public function index(
        $id,
        MenuService $menuService
    ) {
        $menu = $this->getMenu($id, $menuService);

        return $this->render('Content/MenuDetail', [
            'iMenu' => $menu,
            'options' => [],
        ]);
    }

    public function update($id, Request $request, MenuService $menuService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'items' => 'array|required',
        ]);

        $validatedData['id'] = $id;

        $menuService->updateItemsTree($validatedData['id'], new MenuDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @param int $id
     * @param MenuService $menuService
     * @return mixed
     * @throws CmsException
     */
    private function getMenu(
        int $id,
        MenuService $menuService
    ) {
        $query = $menuService->newQuery();
        $query->setFilter('id', $id);
        $query->include('all_items');

        return $menuService->menus($query)->first();
    }
}
