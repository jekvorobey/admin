<?php

namespace App\Http\Middleware;

use Closure;
use Greensight\CommonMsa\Services\TokenBuilder\TokenBuilder;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Illuminate\Http\Request;

class BlockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $blockId)
    {
        $token = resolve(TokenStore::class)->token();
        $blockPermissions = collect(resolve(TokenBuilder::class)->decodeJwt($token)->blockPermissions);

        if ($blockId !== null && $blockPermissions->where('block_id', $blockId)->isEmpty()) {
            abort(403);
        }

        return $next($request);
    }
}
