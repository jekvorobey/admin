<?php

namespace App\Http\Controllers;

use App\Core\Menu;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Controller extends BaseController
{
    protected $title = '';
    protected $breadcrumbs = '';

    public function render($componentName, $props)
    {
        $breadcrumbs = $this->breadcrumbs ? (array)$this->breadcrumbs : false;
        return View::component(
            $componentName,
            $props,
            [
                'breadcrumbs' => $breadcrumbs ? Breadcrumbs::generate(...$breadcrumbs) : [],
                'menu' => Menu::getMenuItems(),
                'isGuest' => resolve(TokenStore::class)->token() == null
            ],
            [
                'title' => $this->title,
                'assets' => $this->getAssets($componentName),
            ]
        );
    }

    private function getAssets($componentName)
    {
        if (frontend()->isInDevMode()) {
            return [
                'js' => [
                    "scripts/{$componentName}.js",
                ],
                'css' => [],
            ];
        } else {
            $webPack = json_decode(file_get_contents(public_path('/assets/webpack-assets.json')), true);
            $js = [];
            $css = [];

            if (isset($webPack[$componentName])) {
                if (isset($webPack[$componentName]['js'])) {
                    $js = array_filter((array)$webPack[$componentName]['js']);
                }
                if (isset($webPack[$componentName]['css'])) {
                    $css = array_filter((array)$webPack[$componentName]['css']);
                }
            }
            return [
                'js' => $js,
                'css' => $css,
            ];
        }
    }
    
    protected function validate(Request $request, array $rules): array
    {
        $data = $request->all();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        return $data;
    }
}
