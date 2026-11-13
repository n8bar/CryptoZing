<?php

return [
    // Requests allowed per API key per minute.
    'rate_limit_per_minute' => (int) env('API_RATE_LIMIT_PER_MINUTE', 60),
];
