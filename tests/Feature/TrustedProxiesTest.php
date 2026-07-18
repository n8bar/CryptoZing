<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    /**
     * Behind a trusted TLS-terminating proxy, forwarded scheme must be
     * honored so generated URLs are https (mixed content otherwise).
     */
    public function test_forwarded_proto_is_honored_when_proxy_is_trusted(): void
    {
        config(['trustedproxy.proxies' => '*']);

        // Absolute http URL so the faked request itself is plain http; only
        // the trusted forwarded header can make it read as secure.
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '192.168.68.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('http://localhost/terms');

        $response->assertOk();
        $this->assertTrue(request()->isSecure());
        $this->assertStringStartsWith('https://', url('/terms'));
    }

    public function test_forwarded_proto_is_ignored_when_no_proxy_is_trusted(): void
    {
        config(['trustedproxy.proxies' => null]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '192.168.68.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('http://localhost/terms');

        $response->assertOk();
        $this->assertFalse(request()->isSecure());
    }
}
