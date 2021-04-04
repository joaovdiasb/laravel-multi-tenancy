<?php

namespace Joaovdiasb\LaravelMultiTenancy\Exceptions;

use Exception;

class TenancyException extends Exception
{
    /**
     * @param string $value
     * 
     * @return \Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyException
     */
    public static function notFound(string $name)
    {
        return new static("Tenancy {$name} not found.");
    }
}
