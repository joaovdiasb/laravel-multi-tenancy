<?php

return [
    // Used to ecrypt tenant database password
    'encrypt_key' => env('TENANT_ENCRYPT_KEY'),

    // Database type used on commands
    'database' => 'mysql',

    'current_container_key' => 'currentTenant',

    'tenant_connection_name' => 'tenant',

    /**
     * You can set this configuration to null and define the connection name
     * on env variable called DB_CONNECTION
     */
    'landlord_connection_name' => 'landlord',

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