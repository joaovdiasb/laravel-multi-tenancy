<?php

namespace Joaovdiasb\LaravelMultiTenancy\Http\Middleware;

use Closure;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;

class TenantChangeConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @throws \Joaovdiasb\LaravelMultiTenancy\Exceptions\TenantException
     * 
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!app()->runningInConsole()){
            $reference = request()->header('X-Ref');
            Tenant::findFirstByKey('reference', $reference)->configure()->use();
        }

        return $next($request);
    }
}
