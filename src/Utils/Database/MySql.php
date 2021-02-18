<?php

namespace Joaovdiasb\LaravelMultiTenancy\Utils\Database;

use Symfony\Component\Process\Process;

class MySql extends Database
{
  private $tempFileHandle;

  public function __construct()
  {
    $this->dbPort = 3306;
  }

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

  public function createDatabase()
  {
    $tempFileHandle = tmpfile();
    $this->setTempFileHandle($tempFileHandle);

    fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
    $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

    $command = $this->getCreateDatabaseCommand($temporaryCredentialsFile);

    Process::fromShellCommandline($command, null, null, null, $this->timeout)->run();
  }

  public function getDumpCommand(string $dumpFile, string $temporaryCredentialsFile): string
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

    return $this->echoToFile(implode(' ', $command), $dumpFile);
  }

  public function dumpToFile(string $dumpFile)
  {
    $tempFileHandle = tmpfile();
    $this->setTempFileHandle($tempFileHandle);

    fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
    $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

    $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

    Process::fromShellCommandline($command, null, null, null, $this->timeout)->run();
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

  public function getTempFileHandle()
  {
    return $this->tempFileHandle;
  }

  public function setTempFileHandle($tempFileHandle)
  {
    $this->tempFileHandle = $tempFileHandle;
  }
}
