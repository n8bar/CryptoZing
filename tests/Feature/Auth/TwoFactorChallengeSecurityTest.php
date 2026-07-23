<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorChallengeSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_resend_is_capped_per_window(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->startChallenge($user); // send #1 as login diverts

        $this->post(route('two-factor.challenge.resend')); // #2
        $this->post(route('two-factor.challenge.resend')); // #3
        $this->post(route('two-factor.challenge.resend')); // blocked

        Mail::assertQueued(TwoFactorCodeMail::class, 3);
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);
        $this->startChallenge($user);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('two-factor.challenge.store'), ['code' => '000000']);
        }

        $user->refresh();
        $this->assertNotNull($user->two_factor_locked_until);
        $this->assertTrue($user->two_factor_locked_until->isFuture());
        $this->assertGuest();
    }

    public function test_locked_account_refuses_even_a_valid_code(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);
        $this->startChallenge($user);
        $code = $this->capturedCode();

        // Lock the account out-of-band, then present the still-valid code.
        $user->forceFill(['two_factor_locked_until' => now()->addMinutes(15)])->save();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    private function startChallenge(User $user): void
    {
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
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
