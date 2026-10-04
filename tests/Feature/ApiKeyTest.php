<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiKeyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_issuing_a_key_returns_the_plain_key_once_and_stores_only_its_hash(): void
    {
        $issuer = User::factory()->create();

        [$key, $plain] = ApiKey::issue($issuer, 'Shop');

        $this->assertStringStartsWith('cz_', $plain);
        $this->assertSame(hash('sha256', $plain), $key->key_hash);
        $this->assertSame('Shop', $key->name);
        $this->assertTrue($key->user->is($issuer));
        $this->assertStringNotContainsString($plain, json_encode($key->fresh()->toArray()));
        $this->assertTrue(ApiKey::findByPlain($plain)->is($key));
    }

    public function test_a_revoked_or_unknown_key_is_not_found(): void
    {
        $issuer = User::factory()->create();
        [$key, $plain] = ApiKey::issue($issuer, 'Old');

        $key->revoke();

        $this->assertNotNull($key->fresh()->revoked_at);
        $this->assertNull(ApiKey::findByPlain($plain));
        $this->assertNull(ApiKey::findByPlain('cz_nothing'));
    }

    public function test_using_a_key_stamps_last_used(): void
    {
        [$key] = ApiKey::issue(User::factory()->create(), 'Shop');

        $this->assertNull($key->last_used_at);
        $key->touchUsed();
        $this->assertNotNull($key->fresh()->last_used_at);
    }
}
