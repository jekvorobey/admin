<?php

namespace App\Http\Middleware;

use Closure;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Illuminate\Http\Request;

/**
 * Class Authenticate
 * @package App\Http\Middleware
 */
class Authenticate
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (resolve(TokenStore::class)->token() == null) {
            return redirect()->route('page.login');
        }

        return $next($request);
    }

}
