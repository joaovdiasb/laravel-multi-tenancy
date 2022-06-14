<?php

namespace Joaovdiasb\LaravelMultiTenancy\Http\Middleware;

use Closure;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Str;

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
            $reference = $this->getTenantReference();
            Tenant::findFirstByKey('reference', $reference)->configure()->use();
        }

        return $next($request);
    }

    /**
     * Get tenant reference by header or subdomain
     *
     * @return string|null
     */
    private function getTenantReference(): ?string
    {
        if (request()->header('X-Ref')) {
            return request()->header('X-Ref');
        }

        if (Str::contains(request()->getHost(), '.')) {
            return explode('.', request()->getHost())[0];
        }

        return null;
    }
}
