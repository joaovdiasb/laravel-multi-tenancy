<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Support\Facades\Validator;
use Illuminate\Console\Command;

class BaseCommand extends Command
{
  /**
   * Pretty line message
   *
   * @param string $message
   * 
   * @return void
   */
  protected function lineHeader(string $message): void
  {
    $this->line('');
    $this->line('-------------------------------------------');
    $this->line($message);
    $this->line('-------------------------------------------');
  }

  /**
   * Validate command data
   *
   * @param array $data
   * @param array $validation
   * 
   * @return boolean
   */
  protected function validate(array $data, array $validation): bool
  {
      $validator = Validator::make($data, $validation);

      if ($validator->fails()) {
          $this->info('Invalid data. See error messages below:');

          foreach ($validator->errors()->all() as $error) {
              $this->error($error);
          }

          return false;
      }

      return true;
  }
}
