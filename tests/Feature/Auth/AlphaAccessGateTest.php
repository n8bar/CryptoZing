<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Mail\AccountApprovedMail;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Support\AlphaGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AlphaAccessGateTest extends TestCase
{
    use DatabaseTransactions;

    private const APPROVED_AT_MIGRATION = 'database/migrations/2026_08_04_000001_add_approved_at_to_users_table.php';

    public function test_is_approved_reflects_approved_at_timestamp(): void
    {
        $approved = User::factory()->create(['approved_at' => now()]);
        $pending = User::factory()->create(['approved_at' => null]);

        $this->assertTrue($approved->isApproved());
        $this->assertFalse($pending->isApproved());
    }

    public function test_pending_and_approved_scopes_partition_users(): void
    {
        $approved = User::factory()->create(['approved_at' => now()]);
        $pending = User::factory()->create(['approved_at' => null]);

        $this->assertSame([$pending->id], User::pending()->pluck('id')->all());
        $this->assertSame([$approved->id], User::approved()->pluck('id')->all());
    }

    public function test_factory_users_default_to_approved(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->isApproved());
    }

    public function test_pending_factory_state_creates_unapproved_user(): void
    {
        $user = User::factory()->pending()->create();

        $this->assertFalse($user->isApproved());
    }

    public function test_alpha_gate_is_enabled_by_default(): void
    {
        $this->assertTrue(AlphaGate::enabled());
    }

    public function test_alpha_gate_flag_can_be_disabled(): void
    {
        config(['alpha.gate_enabled' => false]);

        $this->assertFalse(AlphaGate::enabled());
    }

    public function test_registration_creates_pending_account_without_session(): void
    {
        $response = $this->post('/register', [
            'name' => 'Gate Tester',
            'email' => 'gate-tester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('approval.pending'));
        $this->assertNull(User::where('email', 'gate-tester@example.com')->value('approved_at'));
    }

    public function test_awaiting_approval_page_is_guest_safe(): void
    {
        $response = $this->get(route('approval.pending'));

        $response->assertStatus(200);
        $response->assertSee('approved');
    }

    public function test_gate_disabled_registration_logs_in_as_before(): void
    {
        config(['alpha.gate_enabled' => false]);

        $response = $this->post('/register', [
            'name' => 'Gate Off Tester',
            'email' => 'gate-off@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('getting-started.welcome'));
        $this->assertNotNull(User::where('email', 'gate-off@example.com')->value('approved_at'));
    }

    public function test_pending_account_with_correct_credentials_is_refused(): void
    {
        $user = User::factory()->pending()->create();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        // Entered email survives the refusal per UX guardrails.
        $response->assertSessionHasInput('email', $user->email);
    }

    public function test_pending_two_factor_account_is_refused_before_the_challenge(): void
    {
        Mail::fake();
        $user = User::factory()->pending()->create(['two_factor_email_enabled_at' => now()]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
        // No second-factor code goes to an account that can't get in either way.
        Mail::assertNothingQueued();
    }

    public function test_account_revoked_mid_challenge_is_refused_with_a_valid_code(): void
    {
        Mail::fake();
        $user = User::factory()->create(['two_factor_email_enabled_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $code = $this->capturedCode();

        $user->forceFill(['approved_at' => null])->save();

        $response = $this->post(route('two-factor.challenge.store'), ['code' => $code]);

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
    }

    public function test_gate_disabled_pending_account_logs_in(): void
    {
        config(['alpha.gate_enabled' => false]);
        $user = User::factory()->pending()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_support_dashboard_lists_pending_accounts(): void
    {
        $agent = $this->supportAgent();
        $pending = User::factory()->pending()->create(['name' => 'Waiting Wanda']);

        $response = $this->actingAs($agent)->get(route('support.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Waiting Wanda');
        $response->assertSee($pending->email);
    }

    public function test_approve_sets_timestamp_queues_mail_and_login_succeeds(): void
    {
        Mail::fake();
        $agent = $this->supportAgent();
        $pending = User::factory()->pending()->create();

        $response = $this->actingAs($agent)->post(route('support.approvals.approve', $pending));

        $response->assertRedirect();
        $this->assertTrue($pending->fresh()->isApproved());
        Mail::assertQueued(AccountApprovedMail::class, fn (AccountApprovedMail $mail) => $mail->hasTo($pending->email));

        $this->post('/logout');
        $this->post('/login', ['email' => $pending->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($pending->fresh());
    }

    public function test_revoke_clears_approval_without_mail(): void
    {
        Mail::fake();
        $agent = $this->supportAgent();
        $user = User::factory()->create();

        $response = $this->actingAs($agent)->post(route('support.approvals.revoke', $user));

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->isApproved());
        Mail::assertNothingQueued();
    }

    public function test_revoked_live_session_is_unauthenticated_on_next_request(): void
    {
        $user = User::factory()->create(['getting_started_completed_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($user);

        $user->forceFill(['approved_at' => null])->save();

        // In-test requests share one auth guard, which caches the user
        // instance; a real next request re-resolves from the session. Drop the
        // cached guard to simulate that.
        $this->app['auth']->forgetGuards();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_approval_actions_are_support_gated(): void
    {
        $nonAgent = User::factory()->create();
        $pending = User::factory()->pending()->create();

        $this->actingAs($nonAgent)->post(route('support.approvals.approve', $pending))->assertStatus(403);
        $this->actingAs($nonAgent)->post(route('support.approvals.revoke', $pending))->assertStatus(403);
        $this->assertFalse($pending->fresh()->isApproved());
    }

    public function test_self_revoke_is_rejected(): void
    {
        $agent = $this->supportAgent();

        $response = $this->actingAs($agent)->post(route('support.approvals.revoke', $agent));

        $response->assertRedirect();
        $response->assertSessionHasErrors('revoke');
        $this->assertTrue($agent->fresh()->isApproved());
    }

    public function test_migration_backfills_existing_users_as_approved(): void
    {
        Artisan::call('migrate:rollback', ['--path' => self::APPROVED_AT_MIGRATION, '--force' => true]);

        DB::table('users')->insert([
            'name' => 'Pre-Gate User',
            'email' => 'pre-gate@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::call('migrate', ['--path' => self::APPROVED_AT_MIGRATION, '--force' => true]);

        $this->assertNotNull(
            DB::table('users')->where('email', 'pre-gate@example.com')->value('approved_at')
        );

        // MySQL DDL commits implicitly, so this row escapes the test transaction — remove it.
        DB::table('users')->where('email', 'pre-gate@example.com')->delete();
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
