<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Factory::macro('component', function ($name, $props = [], $layoutData = [], $viewData = []) {
            $routes = [];
            foreach (Route::getRoutes() as $route) {
                /** @var \Illuminate\Routing\Route $route */
                $routes[$route->getName()] = $route->uri;
            }

            return View::make('app', array_merge($viewData, [
                'name' => $name,
                'props' => $props,
                'layout' => $layoutData,
                'env' => [
                    // some services keys like dadata key
                ],
                'routes' => $routes
            ]));
        });
        frontend()->handle();
    }
}
