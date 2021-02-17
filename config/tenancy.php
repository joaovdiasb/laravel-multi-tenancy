<?php

return [
    'encrypt_key' => env('TENANCY_ENCRYPT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Tenancys disks
    |--------------------------------------------------------------------------
    |
    | Overrides a existent disk root with the tenancy reference name as the client folder.
    |
    */

    'disks' => [
        'local' => [
            'name' => 'local',
        ],
        'backup' => [
            'name' => 's3',
            // Backup works on local disk and the file is copied to a backup disk
            // if is allowed (works only on production)
            'allow_copy' => false
        ],
    ],

    'passport' => false,
];