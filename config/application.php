<?php

return [
    'app_name' => 'Tracks Application',
    'version' => '1.0.0',
    
    'timezone' => 'UTC',
    
    'session' => [
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'cookie' => 'tracks_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'http_only' => true,
    ],
    
    'log' => [
        'level' => 'debug',
        'path' => dirname(__DIR__) . '/log/',
    ],
    
    'cache' => [
        'driver' => 'file',
        'path' => dirname(__DIR__) . '/tmp/cache/',
    ],
    
    'view' => [
        'path' => dirname(__DIR__) . '/app/views/',
        'cache' => dirname(__DIR__) . '/tmp/views/',
    ],
];