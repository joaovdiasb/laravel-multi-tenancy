<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database;

abstract class Database
{
    /** @var string **/
    protected $dbName;

    /** @var string **/
    protected $dbUser;

    /** @var string **/
    protected $dbPassword;

    /** @var string **/
    protected $dbHost = 'localhost';

    /** @var int **/
    protected $dbPort;

    /** @var int **/
    protected $timeout = 0;

    /** @var string **/
    protected $dumpBinaryPath = '';

    /** @var bool **/
    protected $compressor = false;

    /** @var bool **/
    protected $onlyStructure = false;

    /** @var bool **/
    protected $onlyData = false;

    public static function create()
    {
        $databaseTypes = [
            'mysql' => 'MySql'
        ];

        $databaseType = strtolower(config('tenancy.backup.database'));

        if (!isset($databaseTypes[$databaseType])) {
            throw new \InvalidArgumentException('Invalid database type');
        }

        $databaseTypeClass = '\Joaovdiasb\LaravelMultiTenancy\Utils\Database\\' . $databaseTypes[$databaseType];

        return new $databaseTypeClass;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     * 
     * @return $this
     */
    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @param string $dbUser
     * 
     * @return $this
     */
    public function setDbUser(string $dbUser)
    {
        $this->dbUser = $dbUser;

        return $this;
    }

    /**
     * @param string $dbPassword
     * 
     * @return $this
     */
    public function setDbPassword(string $dbPassword)
    {
        $this->dbPassword = $dbPassword;

        return $this;
    }

    /**
     * @param string $dbHost
     * 
     * @return $this
     */
    public function setDbHost(string $dbHost)
    {
        $this->dbHost = $dbHost;

        return $this;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    /**
     * @param string $dbPort
     * 
     * @return $this
     */
    public function setDbPort(int $dbPort)
    {
        $this->dbPort = $dbPort;

        return $this;
    }

    /**
     * @param string $compressor
     * 
     * @return $this
     */
    public function setCompressor(string $compressor)
    {
        $this->compressor = $compressor;

        return $this;
    }

    /**
     * @param string $onlyStructure
     * 
     * @return $this
     */
    public function setOnlyStructure(bool $onlyStructure)
    {
        $this->onlyStructure = $onlyStructure;

        return $this;
    }

    /**
     * @param string $onlyData
     * 
     * @return $this
     */
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
