<?php
return [
    'default' => env('DB_DRIVER', 'mysql'),
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'hostname' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_NAME', 'nft'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASS', ''),
            'hostport' => env('DB_PORT', '3306'),
            'charset'  => 'utf8mb4',
            'prefix'   => 'nft_',
            'deploy'   => 0,
            'trigger_sql' => false,
        ],
    ],
];
