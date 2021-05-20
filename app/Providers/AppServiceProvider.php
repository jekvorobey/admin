<?php

namespace App\Providers;

use Greensight\CommonMsa\Services\TokenStore\TokenStore;
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
        if (config('app.force_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }
        Factory::macro('component', function ($name, $props = [], $layoutData = [], $viewData = []) {
            /** @var TokenStore $store */
            $store = resolve(TokenStore::class);
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
                'routes' => $routes,
            ]));
        });
        frontend()->handle();
    }
}
