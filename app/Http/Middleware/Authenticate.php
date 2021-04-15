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

        $response = $next($request);

        $this->logMsQueries($request);

        return $response;
    }

    protected function logMsQueries(Request $request): void
    {
        if (!in_array($request->getPathInfo(), [
            '/notifications',
            '/communications/chats/unread/count',
            '/audit/queries',
            '/audit/queries/total',
        ])) {
            $measures = \Debugbar::getCollector('time')->getMeasures();

            $queries = [];

            foreach ($measures as $measure) {
                if (\Str::startsWith($measure['label'], 'RQ')) {
                    $queries[] = $measure['duration_str'] . ' - ' . \Str::after($measure['label'], 'RQ ');
                }
            }

            logs('ms-queries')->info(implode(PHP_EOL, [
                'REQUEST: ' . $request->getPathInfo(),
                ...$queries,
            ]));
        }
    }
}
