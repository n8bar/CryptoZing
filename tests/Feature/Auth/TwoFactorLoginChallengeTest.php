<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorLoginChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_two_factor_logs_in_normally(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_email_two_factor_diverts_to_the_challenge_without_authenticating(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
        // Email-led challenge emails a code as the login diverts.
        Mail::assertQueued(TwoFactorCodeMail::class, fn (TwoFactorCodeMail $mail) => $mail->hasTo($user->email));
    }

    public function test_challenge_page_requires_a_pending_login(): void
    {
        $this->get(route('two-factor.challenge'))->assertRedirect(route('login'));
    }

    public function test_correct_challenge_code_completes_login(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'two_factor_email_enabled_at' => now(),
            'getting_started_completed_at' => now(),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $code = $this->capturedCode();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_challenge_completion_honours_support_agent_routing(): void
    {
        Mail::fake();
        config()->set('support.agent_emails', ['support@example.com']);
        $user = User::factory()->create([
            'email' => 'support@example.com',
            'two_factor_email_enabled_at' => now(),
            'getting_started_completed_at' => now(),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $code = $this->capturedCode();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertRedirect(route('support.dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_challenge_completion_honours_getting_started_routing(): void
    {
        Mail::fake();
        // A fresh (incomplete) user should still land on getting-started, not dashboard.
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $code = $this->capturedCode();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertRedirect(route('getting-started.start'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_login_divert_sends_are_capped(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        }

        // Repeated valid-password logins can't flood the inbox past the cap.
        Mail::assertQueued(TwoFactorCodeMail::class, 3);
    }

    public function test_wrong_challenge_code_is_rejected_and_the_user_stays_a_guest(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response = $this->post(route('two-factor.challenge.store'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_expired_challenge_code_is_rejected(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $code = $this->capturedCode();

        // Push the pending code past its TTL.
        $user->forceFill(['two_factor_code_expires_at' => now()->subMinute()])->save();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    private function capturedCode(): string
    {
        $code = null;
        Mail::assertQueued(TwoFactorCodeMail::class, function (TwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        return $code;
    }
}
