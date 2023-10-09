<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database\DatabaseTypes;

use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Contracts\DatabaseType;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;
use Symfony\Component\Process\Process;

class Postgres extends Database implements DatabaseType
{
    protected bool $useInserts = false;

    protected bool $createTables = true;

    /** @var false|resource */
    private $tempFileHandle;

    public function __construct()
    {
        $this->dbPort = 5432;
    }

    public function useInserts(): self
    {
        $this->useInserts = true;

        return $this;
    }

    public function getCreateDatabaseCommand(string $temporaryCredentialsFile): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}psql{$quote}",
            "-U {$this->dbUser}",
            '-h '.($this->socket === '' ? $this->dbHost : $this->socket),
            "-p {$this->dbPort}",
            '-c "CREATE DATABASE ' . $this->getDbName() . ';"'
        ];

        return implode(' ', $command);
    }

    public function createDatabase(): void
    {
        $tempFileHandle = tmpfile();
        $this->setTempFileHandle($tempFileHandle);

        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $command = $this->getCreateDatabaseCommand($temporaryCredentialsFile);

        $envVars = $this->getEnvironmentVariablesForCreateCommand($temporaryCredentialsFile);

        $process = Process::fromShellCommandline($command, null, $envVars, null, $this->timeout);

        $process->run();
    }

    public function dumpToFile(string $dumpFile): void
    {
        $this->guardAgainstIncompleteCredentials();

        $tempFileHandle = tmpfile();
        $this->setTempFileHandle($tempFileHandle);

        fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
        $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

        $command = $this->getDumpCommand($dumpFile);

        $envVars = $this->getEnvironmentVariablesForDumpCommand($temporaryCredentialsFile);

        $process = Process::fromShellCommandline($command, null, $envVars, null, $this->timeout);

        $process->run();
    }

    public function getDumpCommand(string $dumpFile): string
    {
        $quote = $this->determineQuote();

        $command = [
            "{$quote}{$this->dumpBinaryPath}pg_dump{$quote}",
            "-U {$this->dbUser}",
            '-h '.($this->socket === '' ? $this->dbHost : $this->socket),
            "-p {$this->dbPort}",
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        if (! $this->createTables) {
            $command[] = '--data-only';
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if (! empty($this->includeTables)) {
            $command[] = '-t '.implode(' -t ', $this->includeTables);
        }

        if (! empty($this->excludeTables)) {
            $command[] = '-T '.implode(' -T ', $this->excludeTables);
        }

        return $this->echoToFile(implode(' ', $command), $dumpFile);
    }

    public function getContentsOfCredentialsFile(): string
    {
        $contents = [
            $this->escapeCredentialEntry($this->dbHost),
            $this->escapeCredentialEntry($this->dbPort),
            $this->escapeCredentialEntry($this->dbName),
            $this->escapeCredentialEntry($this->dbUser),
            $this->escapeCredentialEntry($this->dbPassword),
        ];

        return implode(':', $contents);
    }

    protected function escapeCredentialEntry($entry): string
    {
        $entry = str_replace('\\', '\\\\', $entry);
        $entry = str_replace(':', '\\:', $entry);

        return $entry;
    }

    public function guardAgainstIncompleteCredentials()
    {
        foreach (['dbUser', 'dbName', 'dbHost'] as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw CannotStartDump::emptyParameter($requiredProperty);
            }
        }
    }

    protected function getEnvironmentVariablesForCreateCommand(string $temporaryCredentialsFile): array
    {
        return [
            'PGPASSFILE' => $temporaryCredentialsFile,
        ];
    }

    protected function getEnvironmentVariablesForDumpCommand(string $temporaryCredentialsFile): array
    {
        return [
            'PGPASSFILE' => $temporaryCredentialsFile,
            'PGDATABASE' => $this->dbName,
        ];
    }

    public function doNotCreateTables(): self
    {
        $this->createTables = false;

        return $this;
    }

    /**
     * @return false|resource
     */
    public function getTempFileHandle()
    {
        return $this->tempFileHandle;
    }

    /**
     * @param false|resource $tempFileHandle
     */
    public function setTempFileHandle($tempFileHandle): void
    {
        $this->tempFileHandle = $tempFileHandle;
    }
}
