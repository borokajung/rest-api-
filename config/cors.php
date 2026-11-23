<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['OPTIONS', 'GET', 'POST', 'PUT', 'DELETE'],
    'allowed_origins' => [
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        'http://localhost:5173', // Vite (Vue / React)
        'http://localhost:3000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
