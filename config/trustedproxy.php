<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | When the app runs behind a TLS-terminating reverse proxy, Laravel must
    | trust that proxy's X-Forwarded-* headers or it will generate http://
    | URLs on https:// pages (mixed content, blocked assets). Set this to a
    | comma-separated list of proxy IPs/CIDRs, or '*' to trust the directly
    | connecting client (suitable for dev; scope to real IPs in production).
    | Read by Illuminate\Http\Middleware\TrustProxies in the default stack.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
