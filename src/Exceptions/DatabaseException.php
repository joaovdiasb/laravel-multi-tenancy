<?php

namespace Joaovdiasb\LaravelMultiTenancy\Exceptions;

use Exception;

class DatabaseException extends Exception
{
  /**
   * @param string $config
   * 
   * @return \Joaovdiasb\LaravelMultiTenancy\Exceptions\DatabaseException
   */
  public static function invalidTypeConfig(string $config): self
  {
    return new static("Invalid database type config named \"{$config}\"");
  }
}