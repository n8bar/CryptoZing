<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    use RefreshDatabase;

    /** Every favicon link the layouts are expected to emit. */
    private const ASSETS = [
        'favicon.ico',
        'favicon.svg',
        'favicon-32x32.png',
        'favicon-16x16.png',
        'apple-touch-icon.png',
        'site.webmanifest',
    ];

    public function test_guest_layout_includes_the_favicon_set(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();

        foreach (self::ASSETS as $asset) {
            $response->assertSee($asset, false);
        }
    }

    public function test_app_layout_includes_the_favicon_set(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('profile.edit'));
        $response->assertOk();

        foreach (self::ASSETS as $asset) {
            $response->assertSee($asset, false);
        }
    }

    public function test_deliverables_exist_in_public(): void
    {
        foreach (self::ASSETS as $asset) {
            $this->assertFileExists(public_path($asset), "Missing favicon deliverable: $asset");
        }
    }
}
