<?php

return [
    'xpub' => env('DONATION_WALLET_XPUB'),
    'max_unpaid_addresses' => (int) env('DONATION_MAX_UNPAID_ADDRESSES', 20),
    'notify_email' => env('DONATION_NOTIFY_EMAIL'),
];
