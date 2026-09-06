<?php

// Marketing-page measurement (M21.1 §2.5). Only the public landing page loads
// the script, and only when a deployment sets both values; generic installs
// leave them blank and ship no tracking at all.
return [
    'script_url' => env('ANALYTICS_SCRIPT_URL'),
    'website_id' => env('ANALYTICS_WEBSITE_ID'),
];
