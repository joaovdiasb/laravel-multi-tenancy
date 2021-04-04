<?php

namespace Joaovdiasb\LaravelMultiTenancy\Http\Middleware;

use Closure;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;

class TenancyChangeConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * 
     * @throws \Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyException
     * 
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!app()->runningInConsole()){
            $reference = request()->header('X-Ref');
            Tenancy::findFirstByKey('reference', $reference)->configure()->use();
        }

        return $next($request);
    }
}
