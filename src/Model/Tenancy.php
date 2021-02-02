<?php

namespace Joaovdiasb\LaravelMultiTenancy\Model;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Joaovdiasb\LaravelMultiTenancy\Exceptions\TenancyNotFoundException;

class Tenancy extends Model
{
    use HasFactory;

    protected $table = 'tenancys';

    protected $connection = 'tenancy';

    protected $fillable = [
        'name',
        'reference',
        'db_host',
        'db_port',
        'db_database',
        'db_user',
        'db_password'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(fn ($model) => $model->uuid = Str::uuid());
    }

    public static function findFirstByKey(string $key, string $value)
    {
        $tenant = self::where($key, $value)->first();

        if (!$tenant) {
            throw new TenancyNotFoundException();
        }

        return $tenant;
    }

    public function getDbPasswordAttribute($value): string
    {
        $encrypter = new Encrypter(config('app.encrypt_key'), 'AES-256-CBC');

        return $encrypter->decryptString($value);
    }

    public function setDbPasswordAttribute($value): void
    {
        $encrypter = new Encrypter(config('app.encrypt_key'), 'AES-256-CBC');

        $this->attributes['db_password'] = $encrypter->encryptString($value);
    }

    public function configure(): Tenant
    {
        config([
            'database.connections.tenant.host' => $this->db_host,
            'database.connections.tenant.port' => $this->db_port,
            'database.connections.tenant.database' => $this->db_database,
            'database.connections.tenant.user' => $this->db_user,
            'database.connections.tenant.password' => $this->db_password,
            'filesystems.disks.local.root' => $this->reference
        ]);

        DB::purge('tenant');

        return $this;
    }

    public function use(): Tenant
    {
        app()->forgetInstance('tenant');

        app()->instance('tenant', $this);

        return $this;
    }
}
