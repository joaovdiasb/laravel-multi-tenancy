<?php

namespace Joaovdiasb\LaravelMultiTenancy\Console;

use Joaovdiasb\LaravelMultiTenancy\Model\Tenant;
use Storage;

class TenantBackupCleanupCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:backup-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tenant backup cleanup';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->cleanup();

        return 0;
    }

    public function cleanup(): void
    {
        $this->lineHeader("Cleanup Backups");

        if (!$this->confirm('Are you sure you want to continue?')) {
            throw new \Exception('Action canceled.');
        }

        foreach (config('multitenancy.backup.disks') as $disk) {
            foreach (Tenant::all() as $tenant) {
                foreach (Storage::disk($disk)->allFiles($tenant->reference) as $file) {
                    if (Storage::disk($disk)->lastModified($file) > now()->addDays(-7)->endOfDay()->timestamp) {
                        Storage::disk($disk)->delete($file);
                    }
                }
            }
        }
    }
}
