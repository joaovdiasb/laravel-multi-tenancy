<?php

return [
    // Used to ecrypt tenant database password
    'encrypt_key' => env('TENANT_ENCRYPT_KEY'),

    // Database type used on commands
    'database' => 'mysql',

    'current_container_key' => 'currentTenant',

    'tenant_connection_name' => 'tenant',

    'landlord_connection_name' => env('DB_CONNECTION', 'landlord'),

    'backup' => [
        'temp_folder' => storage_path('app/backup-temp/'),
        'disks' => ['local'],
        'compress' => true
    ],

    /**
     * If you use passport, set this to true to create personal token everytime
     * that you create a new client
     */
    'passport' => false,
];