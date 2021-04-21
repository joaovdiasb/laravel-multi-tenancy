<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database\Contracts;

interface DatabaseType
{
  public function createDatabase(): void;
  public function dumpToFile(string $dumpFilePath): void;
}