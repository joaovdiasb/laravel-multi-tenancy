<?php

return [
    // Used to ecrypt database password
    'encrypt_key' => env('TENANT_ENCRYPT_KEY'),

    // Database type of commands used
    'database' => 'mysql',

    'current_container_key' => 'currentTenant',

    'tenant_connection_name' => 'tenant',

    'landlord_connection_name' => 'landlord',

    'backup' => [
        'temp_folder' => storage_path('app/backup-temp/'),
        'disks' => ['local'],
        'compress' => true
    ],

    'passport' => false,
];