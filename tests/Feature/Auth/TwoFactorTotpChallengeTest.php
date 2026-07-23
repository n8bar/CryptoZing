<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTotpChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_totp_login_diverts_to_challenge_without_emailing_a_code(): void
    {
        Mail::fake();
        $user = $this->makeTotpUser();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        Mail::assertNothingSent();
    }

    public function test_totp_user_completes_login_with_an_app_code(): void
    {
        Mail::fake();
        $user = $this->makeTotpUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $code = app(Google2FA::class)->getCurrentOtp($user->two_factor_totp_secret);
        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_totp_user_can_fall_back_to_an_emailed_code(): void
    {
        Mail::fake();
        $user = $this->makeTotpUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        // "Email me a code instead".
        $this->post(route('two-factor.challenge.resend'));
        $code = $this->capturedCode();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_challenge_prompts_for_the_app_code_and_offers_email_fallback(): void
    {
        Mail::fake();
        $user = $this->makeTotpUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->get(route('two-factor.challenge'));

        $response->assertOk();
        $response->assertSee('authenticator app', false);
        $response->assertSee('Email me a code instead', false);
    }

    public function test_failed_totp_attempts_hit_the_shared_lockout(): void
    {
        Mail::fake();
        $user = $this->makeTotpUser();
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('two-factor.challenge.store'), ['code' => '000000']);
        }

        $user->refresh();
        $this->assertNotNull($user->two_factor_locked_until);
        $this->assertTrue($user->two_factor_locked_until->isFuture());
        $this->assertGuest();
    }

    private function makeTotpUser(): User
    {
        $secret = app(Google2FA::class)->generateSecretKey();

        return User::factory()->create([
            'two_factor_totp_secret' => $secret,
            'two_factor_totp_confirmed_at' => now(),
        ]);
    }

    private function capturedCode(): string
    {
        $code = null;
        Mail::assertSent(TwoFactorCodeMail::class, function (TwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }
}
