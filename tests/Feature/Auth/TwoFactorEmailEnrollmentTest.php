<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorEmailEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_has_email_two_factor_disabled_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasEmailTwoFactorEnabled());
        $this->assertNull($user->two_factor_email_enabled_at);
    }

    public function test_authenticated_user_can_request_an_email_enrollment_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('two-factor.email.enroll'));

        $response->assertSessionHasNoErrors();
        Mail::assertSent(TwoFactorCodeMail::class, fn (TwoFactorCodeMail $mail) => $mail->hasTo($user->email));

        $user->refresh();
        // Round-trip: a pending code is stashed, but 2FA is not yet enabled.
        $this->assertNotNull($user->two_factor_code_hash);
        $this->assertNull($user->two_factor_email_enabled_at);
        $this->assertFalse($user->hasEmailTwoFactorEnabled());
    }

    public function test_user_activates_email_two_factor_with_the_correct_code(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.email.enroll'))->assertSessionHasNoErrors();
        $code = $this->capturedCode();

        $response = $this->actingAs($user)->post(route('two-factor.email.confirm'), ['code' => $code]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue($user->hasEmailTwoFactorEnabled());
        $this->assertNotNull($user->two_factor_email_enabled_at);
        // Code is consumed on activation.
        $this->assertNull($user->two_factor_code_hash);
    }

    public function test_activation_is_rejected_when_the_code_is_wrong(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.email.enroll'));

        $response = $this->actingAs($user)->post(route('two-factor.email.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->hasEmailTwoFactorEnabled());
        $this->assertNull($user->two_factor_email_enabled_at);
    }

    public function test_settings_page_offers_to_enable_email_two_factor(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Two-Factor Authentication');
        $response->assertSee('Enable email two-factor');
    }

    public function test_settings_page_prompts_for_the_code_after_enrollment_begins(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.email.enroll'));

        $response = $this->actingAs($user)->get('/profile');
        $response->assertSee('Verification code');
        $response->assertSee('Confirm and enable');
    }

    public function test_settings_page_shows_the_enabled_state(): void
    {
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertSee('Email two-factor authentication is on.');
    }

    /**
     * Pull the plaintext 6-digit code out of the faked enrollment mail.
     */
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
