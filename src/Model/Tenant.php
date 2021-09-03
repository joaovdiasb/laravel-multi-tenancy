<?php

namespace Joaovdiasb\LaravelMultiTenancy\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\{DatabaseException, TenantException};
use Joaovdiasb\LaravelMultiTenancy\Traits\MultitenancyConfig;
use Joaovdiasb\LaravelMultiTenancy\Utils\Database\Database;

class Tenant extends Model
{
    use MultitenancyConfig;
    
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'reference',
        'db_host',
        'db_port',
        'db_name',
        'db_user',
        'db_password',
        'driver'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    /**
     * Find first tenant by key
     *
     * @param string $key
     * @param string $value
     * 
     * @throws TenantException
     * 
     * @return Tenant
     */
    public static function findFirstByKey(string $key, string $value)
    {
        $tenant = self::where($key, $value)->first();

        if (!$tenant) {
            throw TenantException::notFound($value);
        }

        return $tenant;
    }

    public function getDbPasswordAttribute(string $value): string
    {
        $encrypter = new Encrypter(config('multitenancy.encrypt_key'), 'AES-256-CBC');

        return $encrypter->decryptString($value);
    }

    public function setDbPasswordAttribute(string $value): void
    {
        $encrypter = new Encrypter(config('multitenancy.encrypt_key'), 'AES-256-CBC');

        $this->attributes['db_password'] = $encrypter->encryptString($value);
    }

    public static function current(): ?self
    {
        $containerKey = config('multitenancy.current_container_key');

        if (!app()->has($containerKey)) {
            return null;
        }

        return app($containerKey);
    }

    public static function existCurrent(): bool
    {
        return static::current() !== null;
    }

    public function isCurrent(): bool
    {
        return optional(static::current())->reference === $this->reference;
    }

    private function configureRootFolder(string $reference): void
    {
        foreach (array_keys(config('filesystems.disks')) as $disk) {
            config([
                "filesystems.disks.{$disk}.root" => config("filesystems.disks.{$disk}.root.") . $reference
            ]);
        };
    }

    public function configure(): self
    {
        $selectedDatabaseDriver = Database::DATABASE_DRIVERS[strtolower($this->driver ?: config('multitenancy.database'))] ?? null;

        if (empty($selectedDatabaseDriver)) {
            throw DatabaseException::invalidTypeConfig($selectedDatabaseDriver);
        }

        config([$this->tenantConnectionFullName() => [
            'driver'    => $selectedDatabaseDriver,
            'host'      => $this->db_host,
            'port'      => $this->db_port,
            'database'  => $this->db_name,
            'username'  => $this->db_user,
            'password'  => $this->db_password,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ]]);

        $this->configureRootFolder($this->reference);

        DB::purge($this->tenantConnectionName());

        config(['database.default' => $this->tenantConnectionName()]);

        return $this;
    }

    public function use(): self
    {
        $containerKey = config('multitenancy.current_container_key');

        app()->forgetInstance($containerKey);
        app()->instance($containerKey, $this);

        return $this;
    }

    public function restore(): self
    {
        config([$this->tenantConnectionFullName() => null]);

        $this->configureRootFolder('');

        DB::purge($this->tenantConnectionName());

        config(['database.default' => $this->landlordConnectionName()]);

        app()->forgetInstance(config('multitenancy.current_container_key'));

        return $this;
    }
}
