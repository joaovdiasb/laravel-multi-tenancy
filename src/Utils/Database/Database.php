<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database;

use Joaovdiasb\LaravelMultiTenancy\Exceptions\DatabaseException;

abstract class Database
{
    protected string $dbName;

    protected string $dbUser;

    protected string $dbPassword;

    protected string $dbHost = 'localhost';

    protected int $dbPort;

    protected int $timeout = 0;

    protected string $dumpBinaryPath = '';

    protected bool $compressor = false;

    protected bool $onlyStructure = false;

    protected bool $onlyData = false;

    /**
     * Create database type instance
     *
     * @throws DatabaseException
     * 
     * @return mixed
     */
    public static function create()
    {
        $databaseTypes = [
            'mysql' => 'MySql'
        ];

        $databaseType = strtolower(config('tenancy.database'));

        if (!isset($databaseTypes[$databaseType])) {
            throw DatabaseException::invalidTypeConfig(config('tenancy.database'));
        }

        $databaseTypeClass = '\Joaovdiasb\LaravelMultiTenancy\Utils\Database\\' . $databaseTypes[$databaseType];

        return new $databaseTypeClass;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function setDbName(string $dbName): self
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function setDbUser(string $dbUser): self
    {
        $this->dbUser = $dbUser;

        return $this;
    }

    public function setDbPassword(string $dbPassword): self
    {
        $this->dbPassword = $dbPassword;

        return $this;
    }

    public function setDbHost(string $dbHost): self
    {
        $this->dbHost = $dbHost;

        return $this;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function setDbPort(int $dbPort): self
    {
        $this->dbPort = $dbPort;

        return $this;
    }

    public function setCompressor(string $compressor): self
    {
        $this->compressor = $compressor;

        return $this;
    }

    public function setOnlyStructure(bool $onlyStructure): self
    {
        $this->onlyStructure = $onlyStructure;

        return $this;
    }

    public function setOnlyData(bool $onlyData): self
    {
        $this->onlyData = $onlyData;

        return $this;
    }

    protected function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    protected function determineQuote(): string
    {
        return $this->isWindows() ? '"' : "'";
    }

    protected function getCompressCommand(string $command, string $dumpFilePath): string
    {
        if ($this->isWindows()) {
            return "{$command} | gzip > {$dumpFilePath}";
        }

        return "(((({$command}; echo \$? >&3) | gzip > {$dumpFilePath}) 3>&1) | (read x; exit \$x))";
    }

    protected function echoToFile(string $command, string $dumpFilePath): string
    {
        $dumpFilePath = '"' . addcslashes($dumpFilePath, '\\"') . '"';

        if ($this->compressor) {
            return $this->getCompressCommand($command, $dumpFilePath);
        }

        return $command . ' > ' . $dumpFilePath;
    }
}
