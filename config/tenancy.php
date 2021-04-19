<?php

return [
    // Used to ecrypt database password
    'encrypt_key' => env('TENANCY_ENCRYPT_KEY'),

    // Database type of commands used
    'database' => 'mysql',

    'current_container_key' => 'currentTenancy',

    'connection_name' => 'tenancy',

    'backup' => [
        'temp_folder' => storage_path('app/backup-temp/'),
        'disks' => ['local'],
        'compress' => true
    ],

    'passport' => false,
];