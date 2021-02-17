<?php

return [
    'encrypt_key' => env('TENANCY_ENCRYPT_KEY'),

    'backup' => [
        'temp_folder' => storage_path('app/backup-temp/'),
        'disks' => ['local'],
        'compress' => true
    ],

    'passport' => false,
];