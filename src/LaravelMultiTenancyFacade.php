<?php

namespace Joaovdiasb\LaravelMultiTenancy;

use Illuminate\Support\Facades\Facade;

class LaravelMultiTenancyFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-multi-tenancy';
    }
}
