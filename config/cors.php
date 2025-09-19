<?php
return [
    'paths' => [
        'api/*',
        'v1/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'https://flotiva.app',
        'https://www.flotiva.app',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // <— indispensable
];

