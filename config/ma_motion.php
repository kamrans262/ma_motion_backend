<?php

$csv = static fn (?string $value): array => array_values(array_filter(
    array_map('trim', explode(',', (string) $value)),
    static fn (string $item): bool => $item !== '',
));

return [
    'social' => [
        'google' => [
            'enabled' => (bool) env('MA_GOOGLE_AUTH_ENABLED', false),
            'client_ids' => $csv(env('MA_GOOGLE_CLIENT_IDS')),
            'issuers' => ['https://accounts.google.com', 'accounts.google.com'],
            'jwks_url' => 'https://www.googleapis.com/oauth2/v3/certs',
        ],
        'apple' => [
            'enabled' => (bool) env('MA_APPLE_AUTH_ENABLED', false),
            'client_ids' => $csv(env('MA_APPLE_CLIENT_IDS')),
            'issuers' => ['https://appleid.apple.com'],
            'jwks_url' => 'https://appleid.apple.com/auth/keys',
        ],
        'jwks_cache_seconds' => 21600,
        'clock_skew_seconds' => 60,
    ],

    'release' => [
        'required_php_extensions' => [
            'ctype', 'curl', 'fileinfo', 'gd', 'intl', 'mbstring', 'openssl', 'pdo_mysql',
        ],
    ],
];
