<?php

return [
    'key_id' => getenv('RAZORPAY_KEY_ID') ?: 'rzp_live_TxzRZd7TxkpQxL',
    'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: 'DnaM6B7ztNTWOf1RFhsENwyo',
    'company_name' => getenv('STORE_NAME') ?: 'Lockinrank Shop',
    'domain' => getenv('STORE_DOMAIN') ?: 'shop.lockinrank.com',
    'theme_color' => '#4f46e5',
    'currency' => 'INR'
];
