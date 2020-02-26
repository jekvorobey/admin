<?php

namespace App\Http\Controllers\Content\Menu;

use App\Http\Controllers\Controller;
use Cms\Dto\MenuDto;
use Cms\Dto\ProductGroupDto;
use Cms\Services\MenuService\MenuService;
use Cms\Services\ProductGroupService\ProductGroupService;
use Illuminate\Http\Request;

class MenuDetailController extends Controller
{
    /**
     * @param int $id
     * @param MenuService $menuService
     * @return mixed
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

    public function getMenu(
        int $id,
        MenuService $menuService
    ) {
        $query = $menuService->newQuery();
        $query->setFilter('id', $id);
        $query->include('items');

        return $menuService->menus($query)->first();
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
}
