<?php

namespace Joaovdiasb\LaravelMultiTenancy\Exceptions;

use Exception;

class TenancyFail extends Exception
{
    /**
     * @param string $value
     * 
     * @return \Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyFail
     */
    public static function notFound(string $name)
    {
        return new static("Tenancy {$name} not found.");
    }
}
