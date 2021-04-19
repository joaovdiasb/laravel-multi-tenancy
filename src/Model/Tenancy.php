<?php

namespace Joaovdiasb\LaravelMultiTenancy\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyException;
use Joaovdiasb\LaravelMultiTenancy\Traits\TenancyConfig;

class Tenancy extends Model
{
    use TenancyConfig;
    
    protected $table = 'tenancys';

    static protected array $beforeCurrentConnection = [];

    protected $fillable = [
        'name',
        'reference',
        'db_host',
        'db_port',
        'db_name',
        'db_user',
        'db_password'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    /**
     * findFirstByKey
     *
     * @param string $key
     * @param string $value
     * 
     * @throws TenancyException
     * 
     * @return Tenancy
     */
    public static function findFirstByKey(string $key, string $value)
    {
        $tenancy = self::where($key, $value)->first();

        if (!$tenancy) {
            throw TenancyException::notFound($value);
        }

        return $tenancy;
    }

    public function getDbPasswordAttribute(string $value): string
    {
        $encrypter = new Encrypter(config('tenancy.encrypt_key'), 'AES-256-CBC');

        return $encrypter->decryptString($value);
    }

    public function setDbPasswordAttribute(string $value): void
    {
        $encrypter = new Encrypter(config('tenancy.encrypt_key'), 'AES-256-CBC');

        $this->attributes['db_password'] = $encrypter->encryptString($value);
    }

    public static function current(): ?self
    {
        $containerKey = config('tenancy.current_container_key');

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
                "{filesystems.disks.{$disk}.root" => config("filesystems.disks.{$disk}.root") . $reference
            ]);
        };
    }

    public function configure(): self
    {
        $this->beforeCurrentConnection = config($this->tenancyConnectionPath());

        config([$this->tenancyConnectionPath() => [
            'driver'    => config($this->tenancyConnectionPath() . '.driver'),
            'host'      => $this->db_host,
            'port'      => $this->db_port,
            'database'  => $this->db_name,
            'username'  => $this->db_user,
            'password'  => $this->db_password
        ]]);
        
        $this->configureRootFolder($this->reference);

        DB::purge($this->tenancyConnectionName());

        return $this;
    }

    public function configureBack(): self
    {
        if (empty($this->beforeCurrentConnection)) {
            return $this;
        }

        config([$this->tenancyConnectionPath() => $this->beforeCurrentConnection]);

        $this->configureRootFolder($this->reference ?? '');

        DB::purge($this->tenancyConnectionName());

        return $this;
    }

    public function use(): self
    {
        $containerKey = config('tenancy.current_container_key');

        app()->forgetInstance($containerKey);
        app()->instance($containerKey, $this);

        return $this;
    }
}
