<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\AlphaGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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
}
