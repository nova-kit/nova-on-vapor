<?php

return [
    'user' => [
        'default-password' => env('NOVA_ON_VAPOR_DEFAULT_USER_PASSWORD'),
    ],

    'minio' => [
        'disk' => env('NOVA_ON_VAPOR_MINIO_DISK', 's3'),
        'enabled' => env('NOVA_ON_VAPOR_ENABLES_MINIO', false),
    ],

    'actions' => [
        'queues' => [
            'connection' => env('NOVA_ON_VAPOR_QUEUE_CONNECTION'),
            'queue' => env('NOVA_ON_VAPOR_QUEUE', 'default'),
        ],
    ],
];
