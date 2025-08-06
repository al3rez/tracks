<?php

return [
    'development' => [
        'adapter' => 'sqlite',
        'database' => dirname(__DIR__) . '/db/development.sqlite3',
    ],
    
    'test' => [
        'adapter' => 'sqlite',
        'database' => dirname(__DIR__) . '/db/test.sqlite3',
    ],
    
    'production' => [
        'adapter' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_NAME'] ?? 'tracks_production',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
];