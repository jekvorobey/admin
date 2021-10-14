<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $blockId)
    {
        if ($blockId !== null && !$request->user()->hasPermissionToBlock($blockId)) {
            abort(404);
        }

        return $next($request);
    }
}
