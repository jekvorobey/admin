<?php

namespace App\Providers;

use Common\Auth\SessionTokenStore;
use Common\Auth\TokenStore;
use Common\Rest\RefreshTokenProvider;
use Common\Services\Auth;
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
        app()->singleton(TokenStore::class, function () {
            return new SessionTokenStore();
        });

        app()->singleton(Auth::class, function () {
            $store = resolve(TokenStore::class);
            return new Auth(config('app.serviceAuth'), new RefreshTokenProvider($store));
        });

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
                    'token' => $store->token()

                    // some services keys like dadata key
                ],
                'routes' => $routes
            ]));
        });
        frontend()->handle();
    }
}
