<?php

return [
    'encrypt_key' => env('ENCRYPT_KEY'),

    'passport' => true,

    'backup' => [
        'disk1' => 'local',
        'disk2' => 's3',
        'disk2_allow_backup' => false
    ]
];