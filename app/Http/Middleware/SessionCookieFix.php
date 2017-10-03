<?php

namespace App\Http\Middleware;

use Closure;
use Psy\Exception\FatalErrorException;

class SessionCookieFix
{
    protected $except = [
        'config/subscription/invoices/*',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (method_exists($response, 'header')) {
            $response->header('P3P', 'CP="This site does not have a p3p policy"');
        }
        return $response;
    }
}
