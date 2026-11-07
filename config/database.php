<?php

/**
 * Database Configuration for Local Development & Hostinger Production
 * Supports zero-setup SQLite (default) and Hostinger MySQL.
 */

return [
    'default' => getenv('DB_CONNECTION') ?: 'sqlite', // 'sqlite' or 'mysql'

    'sqlite' => [
        'database' => __DIR__ . '/../data/tpt.db',
    ],

    'mysql' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: '',
        'username' => getenv('DB_USERNAME') ?: '',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
    ]
];
