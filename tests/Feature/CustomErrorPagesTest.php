<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * MS19.4 / #96 — branded, guest-safe error pages (404, 500, 503, 429, 403).
 */
class CustomErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_renders_a_branded_guest_safe_page(): void
    {
        $response = $this->get('/a-route-that-does-not-exist-7Z9');

        $response->assertNotFound();
        $response->assertSee('Page not found');
        $response->assertSee('CryptoZing');
        $response->assertSee('Home'); // guest recovery, no auth context required
    }

    public function test_500_shows_a_friendly_message_without_leaking_debug_detail(): void
    {
        Route::get('/__cz_boom', fn () => throw new \RuntimeException('SENSITIVE_STACK_DETAIL'))
            ->middleware('web');
        config(['app.debug' => false]); // production posture

        $response = $this->get('/__cz_boom');

        $response->assertStatus(500);
        $response->assertSee('Something went wrong');
        $response->assertDontSee('SENSITIVE_STACK_DETAIL', false);
    }

    public function test_503_reads_as_maintenance_with_auth_free_recovery(): void
    {
        $html = view('errors.503')->render();

        $this->assertStringContainsStringIgnoringCase('maintenance', $html);
        $this->assertStringContainsString('Try again', $html);
        // maintenance can run before auth boots — no auth-dependent recovery link
        $this->assertStringNotContainsString('Back to dashboard', $html);
    }

    public function test_429_renders_a_friendly_throttle_message(): void
    {
        $html = view('errors.429')->render();

        $this->assertStringContainsString('Too many requests', $html);
    }

    public function test_403_view_shows_details_and_is_guest_safe(): void
    {
        $html = view('errors.403', ['details' => 'Owner-only resource'])->render();

        $this->assertStringContainsString('Access denied', $html);
        $this->assertStringContainsString('Owner-only resource', $html);
        $this->assertStringContainsString('Sign in', $html); // guest recovery
    }

    public function test_authenticated_recovery_points_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $html = view('errors.404')->render();

        $this->assertStringContainsString('Back to dashboard', $html);
        $this->assertStringNotContainsString('Sign in', $html);
    }

    public function test_every_error_view_renders_for_guests_with_a_recovery_link(): void
    {
        foreach (['403', '404', '429', '500', '503'] as $code) {
            $html = view("errors.$code", ['details' => null, 'exception' => new \Exception('x')])->render();

            $this->assertStringContainsString('CryptoZing', $html, "errors.$code is missing brand chrome");
            $this->assertStringContainsString('href="/"', $html, "errors.$code is missing a home recovery link");
        }
    }
}
