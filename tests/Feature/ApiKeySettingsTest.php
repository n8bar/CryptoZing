<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiKeySettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_making_a_key_shows_it_once(): void
    {
        $issuer = User::factory()->create();

        $response = $this->actingAs($issuer)->post(route('settings.api-keys.store'), ['name' => 'Shop']);
        $response->assertRedirect(route('settings.api-keys.edit'));

        $plain = session('api_key_plain');
        $this->assertStringStartsWith('cz_', $plain);
        $this->assertTrue(ApiKey::findByPlain($plain)->user->is($issuer));

        $this->actingAs($issuer)->get(route('settings.api-keys.edit'))->assertOk()->assertSee($plain)->assertSee('Shop');
        $this->actingAs($issuer)->get(route('settings.api-keys.edit'))->assertOk()->assertDontSee($plain);
    }

    public function test_page_lists_keys_with_last_used_and_revoking_one(): void
    {
        $issuer = User::factory()->create();
        [$used, $plainUsed] = ApiKey::issue($issuer, 'Website');
        $used->touchUsed();
        [$spare] = ApiKey::issue($issuer, 'Spare');

        $this->actingAs($issuer)->get(route('settings.api-keys.edit'))->assertOk()
            ->assertSeeInOrder(['Website', 'Spare'])->assertSee('Never used');

        $this->actingAs($issuer)->delete(route('settings.api-keys.revoke', $spare))
            ->assertRedirect(route('settings.api-keys.edit'));

        $this->assertNotNull($spare->fresh()->revoked_at);
        $this->assertNotNull(ApiKey::findByPlain($plainUsed));
        $this->actingAs($issuer)->get(route('settings.api-keys.edit'))->assertOk()->assertSee('Revoked');
    }

    public function test_another_issuers_key_cannot_be_revoked(): void
    {
        [$key] = ApiKey::issue(User::factory()->create(), 'Theirs');

        $this->actingAs(User::factory()->create())->delete(route('settings.api-keys.revoke', $key))->assertNotFound();
        $this->assertNull($key->fresh()->revoked_at);
    }
}
