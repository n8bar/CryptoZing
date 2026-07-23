<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTotpEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_starts_totp_setup_and_gets_a_pending_secret(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('two-factor.totp.setup'));

        $response->assertRedirect(route('two-factor.totp.setup.show'));
        $user->refresh();
        $this->assertNotNull($user->two_factor_totp_secret);
        $this->assertNull($user->two_factor_totp_confirmed_at);
        $this->assertFalse($user->hasTotpEnabled());
    }

    public function test_setup_page_discloses_qr_manual_uri_and_secret(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.totp.setup'));
        $user->refresh();

        $response = $this->actingAs($user)->get(route('two-factor.totp.setup.show'));

        $response->assertOk();
        $response->assertSee('otpauth://', false);                  // manual URI
        $response->assertSee($user->two_factor_totp_secret, false); // raw base32 secret
        $response->assertSee('<svg', false);                        // rendered QR
    }

    public function test_user_confirms_totp_with_a_valid_app_code(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.totp.setup'));
        $user->refresh();

        $code = app(Google2FA::class)->getCurrentOtp($user->two_factor_totp_secret);

        $response = $this->actingAs($user)->post(route('two-factor.totp.confirm'), ['code' => $code]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue($user->hasTotpEnabled());
        $this->assertNotNull($user->two_factor_totp_confirmed_at);
    }

    public function test_totp_confirmation_is_rejected_with_a_bad_code(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.totp.setup'));

        $response = $this->actingAs($user)->post(route('two-factor.totp.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->hasTotpEnabled());
    }

    public function test_user_disables_totp_with_a_valid_app_code(): void
    {
        $secret = app(Google2FA::class)->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_totp_secret' => $secret,
            'two_factor_totp_confirmed_at' => now(),
        ]);
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $response = $this->actingAs($user)->delete(route('two-factor.totp.disable'), ['code' => $code]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertFalse($user->hasTotpEnabled());
        $this->assertNull($user->two_factor_totp_secret);
    }

    public function test_totp_disable_is_rejected_with_a_bad_code(): void
    {
        $secret = app(Google2FA::class)->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_totp_secret' => $secret,
            'two_factor_totp_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('two-factor.totp.disable'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertTrue($user->hasTotpEnabled());
    }

    public function test_settings_page_offers_totp_setup(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertSee('Set up authenticator app');
    }
}
