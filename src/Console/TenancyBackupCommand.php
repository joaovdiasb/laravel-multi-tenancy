<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;
use Illuminate\Support\Facades\Storage;

class TenancyBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:backup {tenancy?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenancy backup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->argument('tenancy')) {
            $this->backup(
                Tenancy::find($this->argument('tenancy'))
            );
        } else {
            Tenancy::all()->each(
                fn ($tenancy) => $this->backup($tenancy)
            );
        }
    }

    public function backup($tenancy)
    {
        $tenancy->configure()->use();

        $this->line('');
        $this->line('-------------------------------------------');
        $this->line("Backup Tenancy #{$tenancy->id} ({$tenancy->name})");
        $this->line('-------------------------------------------');

        if (!$this->confirm('Tem certeza que deseja realmente continuar?')) {
            return $this->line('Operação cancelada');
        }

        $backupPath   = "backup/{$tenancy->reference}/" . date('d-m-Y-H-i-s', strtotime(now()));
        $tableFile    = "{$backupPath}/tables.sql.gz";
        $dataFile     = "{$backupPath}/data.sql.gz";
        $ignoreTables = implode(
            "--ignore-table={$tenancy->db_database}.",
            []
        );

        if (!Storage::disk(config('tenancy.backup.disk1'))->exists($backupPath)) {
            Storage::disk(config('tenancy.backup.disk1'))->makeDirectory($backupPath);
        }

        exec('mysqldump --host=' . escapeshellarg($tenancy->db_host) . ' -P ' . escapeshellarg($tenancy->db_port) . ' -u ' . escapeshellarg($tenancy->db_user) .
            " --password='" . escapeshellarg($tenancy->db_password) . "' -d --skip-lock-tables " . escapeshellarg($tenancy->db_database) . ' | gzip > storage/app/' .
            escapeshellarg($tableFile), $output);

        $this->info(json_encode($output));
        $this->info("Finalizado estrutura » storage/app/{$tableFile}");

        exec('mysqldump --host=' . escapeshellarg($tenancy->db_host) . ' -P ' . escapeshellarg($tenancy->db_port) . ' -u ' . escapeshellarg($tenancy->db_user) .
            " --password='" . escapeshellarg($tenancy->db_password) . "' --skip-lock-tables --no-create-info " . $ignoreTables . ' ' . escapeshellarg($tenancy->db_database) .
            ' | gzip > storage/app/' . escapeshellarg($dataFile), $output);

        $this->info(json_encode($output));
        $this->info("Finalizado dados » storage/app/{$dataFile}");

        if (App::environment('production') && config('tenancy.backup.disk2_allow_backup')) {
            Storage::disk(config('tenancy.backup.disk2'))->put($backupPath, Storage::disk(config('tenancy.backup.disk2'))->get($tableFile));
            $this->info("Salvando estrutura em cloud » {$tableFile}");

            Storage::disk(config('tenancy.backup.disk2'))->put($backupPath, Storage::disk(config('tenancy.backup.disk2'))->get($dataFile));
            $this->info("Salvando dados em cloud » {$dataFile}");
        }
    }
}
