<?php

namespace App\Http\Controllers\Content\Menu;

use App\Http\Controllers\Controller;
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
        $menus = $menuService->menus($query);

        return response()->json($menus);
    }

    public function update($id, Request $request, ProductGroupService $productGroupService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'code' => 'string|required',
            'active' => 'boolean|required',
            'added_in_menu' => 'boolean|required',
            'type_id' => 'integer|required',
            'preview_photo_id' => 'integer|required',
            'category_code' => 'string|nullable',
            'filters' => 'array',
            'products' => 'array',
        ]);

        $validatedData['id'] = $id;

        $productGroupService->updateProductGroup($validatedData['id'], new ProductGroupDto($validatedData));

        return response()->json([], 204);
    }
}
