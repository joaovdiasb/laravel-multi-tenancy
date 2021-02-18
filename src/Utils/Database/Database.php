<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database;

abstract class Database
{
    protected $dbName;

    protected $dbUser;

    protected $dbPassword;

    protected $dbHost = 'localhost';

    protected $dbPort;

    protected $timeout = 0;

    protected $dumpBinaryPath = '';

    protected $compressor = false;

    protected $onlyStructure = false;

    protected $onlyData = false;

    public static function create()
    {
        return new static();
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    public function setDbUser(string $dbUser)
    {
        $this->dbUser = $dbUser;

        return $this;
    }

    public function setDbPassword(string $dbPassword)
    {
        $this->dbPassword = $dbPassword;

        return $this;
    }

    public function setDbHost(string $dbHost)
    {
        $this->dbHost = $dbHost;

        return $this;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function setDbPort(int $dbPort)
    {
        $this->dbPort = $dbPort;

        return $this;
    }

    public function setCompressor(string $compressor)
    {
        $this->compressor = $compressor;

        return $this;
    }

    public function setOnlyStructure(bool $onlyStructure)
    {
        $this->onlyStructure = $onlyStructure;

        return $this;
    }

    public function setOnlyData(bool $onlyData)
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
