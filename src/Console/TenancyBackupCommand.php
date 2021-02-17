<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Joaovdiasb\LaravelMultiTenancy\Model\Tenancy;
use Illuminate\Support\Facades\Storage;
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

        $diskLocal = Storage::disk(config('tenancy.disks.local.name'));
        $fileName = date('Y_m_d_His', time()) . '.sql';
        $backupPath = $diskLocal->getAdapter()->getPathPrefix() . 'backup/';

        if (!$diskLocal->exists('backup')) {
            $diskLocal->makeDirectory('backup');
        }

        $mySql = MySql::create()
            ->setDbName($tenancy->getDbName())
            ->setDbUser($tenancy->getDbUser())
            ->setDbPassword($tenancy->getDbPassword());

        $mySql->dumpToFile($backupFullPath = ($backupPath . $fileName));

        $this->info("Database dump finished » {$backupFullPath}");

        if (App::environment('production') && config('tenancy.disks.backup.allow_copy')) {
            $diskBackup = Storage::disk(config('tenancy.disk.backup.name'));
            $backupFullPath = $diskBackup->getAdapter()->getPathPrefix() . 'backup/' . $fileName;

            $diskBackup->put($backupPath, $diskLocal->get('backup/' . $fileName));

            $this->info("Copying dump to backup disk » {$backupFullPath}");
        }
    }
}