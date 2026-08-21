<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://www.masterpiecegh.com',
        'https://masterpiecegh.com',
        'https://boutique-frontend-production.up.railway.app',
        'http://localhost:5173',
        'http://localhost:8000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,  // ← Changed to true for authentication
];