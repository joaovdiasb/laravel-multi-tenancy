<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database\DatabaseTypes;

use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Contracts\DatabaseType;
use Symfony\Component\Process\Process;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;

class MySql extends Database implements DatabaseType
{
  /** @var false|resource */
  private $tempFileHandle;

  public function __construct()
  {
    $this->dbPort = 3306;
  }

  /**
   * getCreateDatabaseCommand
   *
   * @param string $temporaryCredentialsFile
   * 
   * @return string
   */
  public function getCreateDatabaseCommand(string $temporaryCredentialsFile): string
  {
    $quote = $this->determineQuote();

    $command = [
      "{$quote}mysql{$quote}",
      "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
      '-e "CREATE DATABASE ' . $this->getDbName() . ' CHARACTER SET utf8 COLLATE utf8_unicode_ci;"'
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

    $process = Process::fromShellCommandline($command, null, null, null, $this->timeout);

    $process->run();
  }

  /**
   * getDumpCommand
   *
   * @param string $dumpFilePath
   * @param string $temporaryCredentialsFile
   * 
   * @return string
   */
  public function getDumpCommand(string $dumpFilePath, string $temporaryCredentialsFile): string
  {
    $quote = $this->determineQuote();

    $command = [
      "{$quote}{$this->dumpBinaryPath}mysqldump{$quote}",
      "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
      '--all-databases'
    ];

    if ($this->onlyStructure) {
      $command[] = '--no-data';
    }

    if ($this->onlyData) {
      $command[] = '--no-create-info';
    }

    $command[] = '--skip-lock-tables';

    return $this->echoToFile(implode(' ', $command), $dumpFilePath);
  }

  public function dumpToFile(string $dumpFilePath): void
  {
    $tempFileHandle = tmpfile();
    $this->setTempFileHandle($tempFileHandle);

    fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
    $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

    $command = $this->getDumpCommand($dumpFilePath, $temporaryCredentialsFile);

    $process = Process::fromShellCommandline($command, null, null, null, $this->timeout);

    $process->run();
  }

  public function getContentsOfCredentialsFile(): string
  {
    $contents = [
      '[client]',
      "user = '{$this->dbUser}'",
      "password = '{$this->dbPassword}'",
      "host = '{$this->dbHost}'",
      "port = '{$this->dbPort}'",
    ];

    return implode(PHP_EOL, $contents);
  }

  /**
   * @return false|resource
   */
  public function getTempFileHandle()
  {
    return $this->tempFileHandle;
  }

  public function setTempFileHandle($tempFileHandle)
  {
    $this->tempFileHandle = $tempFileHandle;
  }
}
