<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SessionExpiryRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function registerExpiringRoute(): void
    {
        Route::post('/__expire_test', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        })->middleware('web');
    }

    public function test_full_page_419_redirects_to_login_and_stashes_return_target(): void
    {
        $this->registerExpiringRoute();

        $response = $this->post('/__expire_test', ['_return_to' => url('/dashboard')]);

        $response->assertRedirect(route('login', ['expired' => 1]));
        $response->assertSessionHas('url.intended', url('/dashboard'));
    }

    public function test_ajax_419_returns_json_redirect_not_html(): void
    {
        $this->registerExpiringRoute();

        $response = $this->postJson('/__expire_test', ['_return_to' => url('/dashboard')]);

        $response->assertStatus(419);
        $response->assertJson(['redirect' => route('login', ['expired' => 1])]);
    }

    public function test_419_falls_back_to_dashboard_when_target_is_off_origin(): void
    {
        $this->registerExpiringRoute();

        $response = $this->post('/__expire_test', ['_return_to' => 'https://evil.example/steal']);

        $response->assertRedirect(route('login', ['expired' => 1]));
        $response->assertSessionMissing('url.intended');
    }

    public function test_419_rejects_a_post_only_route_as_return_target(): void
    {
        $this->registerExpiringRoute();

        // The logout endpoint is POST-only — never a valid GET return target.
        $response = $this->post('/__expire_test', ['_return_to' => url('/logout')]);

        $response->assertRedirect(route('login', ['expired' => 1]));
        $response->assertSessionMissing('url.intended');
    }

    public function test_guest_419_resolves_to_login_without_forcing_unwanted_auth(): void
    {
        Route::post('/__guest_expire_test', function () {
            throw new TokenMismatchException('CSRF token mismatch.');
        })->middleware('web');

        $response = $this->post('/__guest_expire_test');

        $response->assertRedirect(route('login', ['expired' => 1]));
    }

    public function test_get_idle_expiry_flags_login_for_a_returning_user(): void
    {
        // A returning user carries the marker cookie, so a guest bounce from a
        // protected GET surfaces the expired-session banner.
        $this->withUnencryptedCookie(AuthenticatedSessionController::RETURNING_COOKIE, '1')
            ->get('/dashboard')
            ->assertRedirect(route('login', ['expired' => 1]));
    }

    public function test_get_idle_redirect_is_plain_login_for_a_first_time_visitor(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_login_drops_returning_marker_and_logout_clears_it(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertCookie(AuthenticatedSessionController::RETURNING_COOKIE);

        $this->post('/logout')
            ->assertCookieExpired(AuthenticatedSessionController::RETURNING_COOKIE);
    }

    public function test_login_page_shows_expired_notice_when_flagged(): void
    {
        $this->get(route('login', ['expired' => 1]))
            ->assertOk()
            ->assertSee('Your session expired', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Your session expired', false);
    }

    public function test_login_returns_user_to_the_captured_page(): void
    {
        $user = User::factory()->create();

        $response = $this->withSession(['url.intended' => url('/dashboard')])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(url('/dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
