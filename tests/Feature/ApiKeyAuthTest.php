<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiKeyAuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware(['api', 'api.key'])->get('/api/v1/_probe', fn () => ['issuer' => request()->user()->id]);
    }

    public function test_requests_without_a_live_key_are_refused(): void
    {
        $this->getJson('/api/v1/_probe')->assertStatus(401)->assertJsonPath('error.code', 'unauthorized');

        $this->withToken('cz_unknown')->getJson('/api/v1/_probe')->assertStatus(401)->assertJsonPath('error.code', 'unauthorized');

        [$key, $plain] = ApiKey::issue(User::factory()->create(), 'Old');
        $key->revoke();
        $this->withToken($plain)->getJson('/api/v1/_probe')->assertStatus(401);
    }

    public function test_a_live_key_resolves_its_issuer_and_stamps_last_used(): void
    {
        $issuer = User::factory()->create();
        [$key, $plain] = ApiKey::issue($issuer, 'Shop');

        $this->withToken($plain)->getJson('/api/v1/_probe')->assertOk()->assertJson(['issuer' => $issuer->id]);

        $this->assertNotNull($key->fresh()->last_used_at);
    }

    public function test_requests_over_the_limit_say_when_to_retry(): void
    {
        config(['api.rate_limit_per_minute' => 2]);
        RateLimiter::clear('api-key');
        [, $plain] = ApiKey::issue(User::factory()->create(), 'Busy');

        $this->withToken($plain)->getJson('/api/v1/_probe')->assertOk();
        $this->withToken($plain)->getJson('/api/v1/_probe')->assertOk();
        $this->withToken($plain)->getJson('/api/v1/_probe')
            ->assertStatus(429)->assertHeader('Retry-After')->assertJsonPath('error.code', 'rate_limited');
    }
}
