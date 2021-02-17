<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Console\Command;
use File;
use Storage;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\MySql;

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

        $fileName = date('Y_m_d_His', time()) . (config('tenancy.backup.compress') ? '.gz' : '.sql');
        $backupTempPath = config('tenancy.backup.temp_folder');

        if (!File::exists($backupTempPath)) {
            File::makeDirectory($backupTempPath, 0775, true, true);
        }

        $conn = MySql::create()
            ->setDbName($tenancy->db_name)
            ->setDbUser($tenancy->db_user)
            ->setDbPassword($tenancy->db_password);

        if (config('tenancy.backup.compress')) {
            $conn->setCompressor(true);
        }

        $conn->dumpToFile($backupTempFullPath = ($backupTempPath . $fileName));

        $this->info("Database dump finished » {$backupTempFullPath}");

        foreach (config('tenancy.backup.disks') as $disk) {
            $backupPath = Storage::disk($disk)->getAdapter()->getPathPrefix();

            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0775, true, true);
            }

            File::put($backupFullPath = ($backupPath . $fileName), File::get($backupTempFullPath));
            File::delete($backupTempFullPath);

            $this->info("Copying backup disk [{$disk}] » {$backupFullPath}");
        }
    }
}