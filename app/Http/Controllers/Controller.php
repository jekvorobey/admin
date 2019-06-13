<?php

namespace App\Http\Controllers;

use App\Core\Menu;
use Common\Auth\Client;
use Common\Auth\TokenStore;
use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

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
                'css' => [
                    "styles/{$componentName}.css",
                ],
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
}
