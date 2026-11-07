<?php

/**
 * Database Configuration for Local Development & Hostinger Production
 * Configured with Hostinger MySQL credentials.
 */

// If running locally in development, default to SQLite; on Hostinger server, default to MySQL
$isLocal = (php_sapi_name() === 'cli-server') || (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'localhost'));

return [
    'default' => getenv('DB_CONNECTION') ?: ($isLocal ? 'sqlite' : 'mysql'),

    'sqlite' => [
        'database' => __DIR__ . '/../data/tpt.db',
    ],

    'mysql' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'u258212572_shop',
        'username' => getenv('DB_USERNAME') ?: 'u258212572_maz',
        'password' => getenv('DB_PASSWORD') ?: 'Mazhar123$^$',
        'charset' => 'utf8mb4',
    ]
];

