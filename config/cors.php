<?php

$origins = trim((string) env('CORS_ALLOWED_ORIGINS', '*'));
$allowedOrigins = $origins === '*'
    ? ['*']
    : array_filter(array_map('trim', explode(',', $origins)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN),
];
