<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Support\AlphaGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountBanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_banned_login_is_refused_with_gate_on_using_disabled_copy(): void
    {
        $user = User::factory()->create(['banned_at' => now()]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email' => __(AlphaGate::BANNED_MESSAGE)]);
    }

    public function test_banned_login_is_refused_with_gate_off(): void
    {
        config(['alpha.gate_enabled' => false]);
        $user = User::factory()->create(['banned_at' => now()]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_ban_mid_challenge_refuses_a_valid_code(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $code = $this->capturedCode();

        $user->forceFill(['banned_at' => now()])->save();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
    }

    public function test_banned_live_session_is_unauthenticated_on_next_request(): void
    {
        $user = User::factory()->create(['getting_started_completed_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);

        $user->forceFill(['banned_at' => now()])->save();

        // Same guard-cache caveat as the revoke test: a real next request
        // re-resolves the user from the session.
        $this->app['auth']->forgetGuards();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_account_lookup_shows_state_and_actions(): void
    {
        $agent = $this->supportAgent();
        $user = User::factory()->create(['name' => 'Lookup Larry']);

        $response = $this->actingAs($agent)->get(route('support.dashboard', ['account' => $user->email]));

        $response->assertStatus(200);
        $response->assertSee('Lookup Larry');
        $response->assertSee('Approved');
        $response->assertSee(route('support.accounts.ban', $user));
        $response->assertSee(route('support.approvals.revoke', $user));
    }

    public function test_account_lookup_shows_unban_for_banned_account(): void
    {
        $agent = $this->supportAgent();
        $user = User::factory()->create(['banned_at' => now()]);

        $response = $this->actingAs($agent)->get(route('support.dashboard', ['account' => $user->email]));

        $response->assertSee('Banned');
        $response->assertSee(route('support.accounts.unban', $user));
    }

    public function test_ban_and_unban_actions_are_support_gated(): void
    {
        $nonAgent = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($nonAgent)->post(route('support.accounts.ban', $target))->assertStatus(403);
        $this->actingAs($nonAgent)->post(route('support.accounts.unban', $target))->assertStatus(403);
        $this->assertNull($target->fresh()->banned_at);
    }

    public function test_self_ban_is_rejected(): void
    {
        $agent = $this->supportAgent();

        $response = $this->actingAs($agent)->post(route('support.accounts.ban', $agent));

        $response->assertRedirect();
        $response->assertSessionHasErrors('ban');
        $this->assertNull($agent->fresh()->banned_at);
    }

    public function test_ban_then_unban_restores_login(): void
    {
        $agent = $this->supportAgent();
        $user = User::factory()->create();

        $this->actingAs($agent)->post(route('support.accounts.ban', $user));
        $this->assertNotNull($user->fresh()->banned_at);

        $this->actingAs($agent)->post(route('support.accounts.unban', $user));
        $this->assertNull($user->fresh()->banned_at);

        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_ban_and_unban_send_no_mail(): void
    {
        Mail::fake();
        $agent = $this->supportAgent();
        $user = User::factory()->create();

        $this->actingAs($agent)->post(route('support.accounts.ban', $user));
        $this->actingAs($agent)->post(route('support.accounts.unban', $user));

        Mail::assertNothingQueued();
    }

    private function supportAgent(): User
    {
        config()->set('support.agent_emails', ['support@example.com']);

        return User::factory()->create(['email' => 'support@example.com']);
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
