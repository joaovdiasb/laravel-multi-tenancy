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

    $process = $this->getProcess($dumpFile);

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

  public function getProcess(string $dumpFile): Process
  {
    fwrite($this->getTempFileHandle(), $this->getContentsOfCredentialsFile());
    $temporaryCredentialsFile = stream_get_meta_data($this->getTempFileHandle())['uri'];

    $command = $this->getDumpCommand($dumpFile, $temporaryCredentialsFile);

    return Process::fromShellCommandline($command, null, null, null, $this->timeout);
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
