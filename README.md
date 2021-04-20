# Laravel multi-tenancy databases

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![CI](https://github.com/joaovdiasb/laravel-multi-tenancy/actions/workflows/run_tests.yml/badge.svg)]()
[![Total Downloads](https://img.shields.io/packagist/dt/joaovdiasb/laravel-multi-tenancy.svg?style=flat-square)](https://packagist.org/packages/joaovdiasb/laravel-multi-tenancy)


## Installation

Install via composer
```bash
composer require joaovdiasb/laravel-multi-tenancy
```

## Configuration
1. Publish provider and migrate:
```bash
php artisan vendor:publish --provider="Joaovdiasb\LaravelMultiTenancy\LaravelMultiTenancyServiceProvider" && php artisan migrate --path=./database/migrations/tenant
```
2. Define connections on published config called tenant;
3. Add TENANCY_ENCRYPT_KEY with 32 random characters string.

## Usage
1. Add middleware *multitenancy* on the routes that you need;
2. Send *X-Ref* header on request with tenant reference, defined on tenants table.

## Commands
- Add tenant:
```bash
php artisan tenant:add {name?} {reference?} {db_name?} {db_user?} {db_password?} {db_host?} {db_port?}
```
> **{reference?}** Used to pass on request header to identify tenant

> All params are optional, if not present, will be asked on console

- Migrate tenant:
```bash
php artisan tenant:migrate {tenant?} {--fresh} {--seed}
```
> **{tenant?}** Select tenant by id, if not present, all tenants are selected

> **{--fresh}** Is present, will drop all tables from the database

> **{--seed}** Is present, call seeds

- Seed tenant:
```bash
php artisan tenant:seed {tenant?} {--class=*}
```
> **{tenant?}** Select tenant by id, if is not present, all tenants are selected

> **{--class=*}** Is required, specify class name

- Backup tenant:
```bash
php artisan tenant:backup {tenant?}
```
> **{tenant?}** Select tenant by id, if is not present, all tenants are selected

## Testing
Run the tests with:

```bash
vendor/bin/phpunit
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Security

If you discover any security-related issues, please email j.v_dias@hotmail.com instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](/LICENSE.md) for more information.