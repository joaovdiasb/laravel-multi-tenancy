<?php

namespace Joaovdiasb\LaravelMultiTenancy\Exceptions;

use Exception;

class TenantException extends Exception
{
    /**
     * @param string $value
     * 
     * @return \Joaovdiasb\LaravelMultiTenancy\Exceptions\TenantException
     */
    public static function notFound(string $name): self
    {
        return new static("Tenant {$name} not found.");
    }
}
