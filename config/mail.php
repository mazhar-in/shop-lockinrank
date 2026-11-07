<?php

/**
 * Hostinger SMTP Mail Configuration
 * Used for automated digital order receipts and PDF download links.
 */

return [
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp', // 'smtp' or 'mail'
    'host' => getenv('MAIL_HOST') ?: 'smtp.hostinger.com',
    'port' => (int)(getenv('MAIL_PORT') ?: 465),
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'ssl', // 'ssl' (port 465) or 'tls' (port 587)
    'username' => getenv('MAIL_USERNAME') ?: 'orders@lockinrank.com',
    'password' => getenv('MAIL_PASSWORD') ?: '',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'Lockinrank Academic Store',
    'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'orders@lockinrank.com',
    'reply_to' => getenv('MAIL_REPLY_TO') ?: 'support@lockinrank.com',
];
