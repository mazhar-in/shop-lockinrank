<?php

return [
    'key_id' => getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_Tp3PMKVjGXswG8',
    'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: 'm2Q4SNsHHGxafFRFFZ32MhA7',
    'company_name' => getenv('STORE_NAME') ?: 'Lockinrank Shop',
    'domain' => getenv('STORE_DOMAIN') ?: 'shop.lockinrank.com',
    'theme_color' => '#4f46e5',
    'currency' => 'INR'
];
