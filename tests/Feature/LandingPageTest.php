<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The landing page carries the public metadata and legal footer the apex
 * publishes (M21.1 §1.4), derived from the configured public URL.
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private const DESCRIPTION = 'CryptoZing is an open-source Bitcoin invoicing app for USD-first invoices, unique Bitcoin addresses, and reliable on-chain payment tracking.';

    public function test_landing_page_publishes_metadata_from_the_public_url(): void
    {
        config(['app.public_url' => 'https://example.test']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<title>CryptoZing | Bitcoin Invoicing with Payment Tracking</title>', false);
        $response->assertSee('<meta name="description" content="'.self::DESCRIPTION.'">', false);
        $response->assertSee('<link rel="canonical" href="https://example.test/">', false);
        $response->assertSee('<meta property="og:url" content="https://example.test/">', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:image" content="https://example.test/og-preview.png">', false);
        $response->assertSee('<meta property="og:image:width" content="1200">', false);
        $response->assertSee('<meta property="og:image:height" content="630">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<meta name="twitter:image" content="https://example.test/og-preview.png">', false);
        $response->assertSee('favicon.svg', false);
        $response->assertSee('site.webmanifest', false);
    }

    public function test_landing_page_publishes_software_application_structured_data(): void
    {
        config(['app.public_url' => 'https://example.test/']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<script type="application/ld+json">', false);
        $response->assertSee('"@type":"SoftwareApplication"', false);
        $response->assertSee('"applicationCategory":"BusinessApplication"', false);
        $response->assertSee('"url":"https://example.test/"', false);
        $response->assertSee('"image":"https://example.test/og-preview.png"', false);
    }

    public function test_landing_page_links_to_policies_and_guides(): void
    {
        config(['app.guides_url' => 'https://example.test/learn/']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('href="'.route('legal.terms').'"', false);
        $response->assertSee('Terms of Service');
        $response->assertSee('href="'.route('legal.privacy').'"', false);
        $response->assertSee('Privacy Policy');
        $response->assertSee('href="https://example.test/learn/"', false);
        $response->assertSee('Guides');
    }

    public function test_landing_page_loads_no_analytics_unless_a_deployment_configures_it(): void
    {
        config(['analytics.script_url' => null, 'analytics.website_id' => null]);

        $this->get('/')->assertOk()->assertDontSee('data-website-id', false);

        config(['analytics.script_url' => 'https://stats.example.test/script.js', 'analytics.website_id' => null]);

        $this->get('/')->assertOk()->assertDontSee('data-website-id', false);
    }

    public function test_landing_page_loads_the_configured_analytics_script(): void
    {
        config(['analytics.script_url' => 'https://stats.example.test/script.js', 'analytics.website_id' => 'site-123']);

        $this->get('/')->assertOk()->assertSee(
            '<script defer src="https://stats.example.test/script.js" data-website-id="site-123"></script>',
            false,
        );
    }

    public function test_analytics_stays_off_every_page_but_the_landing(): void
    {
        config(['analytics.script_url' => 'https://stats.example.test/script.js', 'analytics.website_id' => 'site-123']);

        foreach (['/terms', '/privacy', '/login'] as $path) {
            $this->get($path)->assertOk()->assertDontSee('data-website-id', false);
        }

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertDontSee('data-website-id', false);
    }

    public function test_only_the_landing_view_references_the_analytics_config(): void
    {
        $views = collect(\Illuminate\Support\Facades\File::allFiles(resource_path('views')))
            ->filter(fn ($file) => str_contains((string) file_get_contents($file->getPathname()), 'analytics.'))
            ->map(fn ($file) => str_replace(resource_path('views').'/', '', $file->getPathname()))
            ->values()
            ->all();

        $this->assertSame(['welcome.blade.php'], $views);
    }

    public function test_social_preview_assets_exist_in_public(): void
    {
        $this->assertFileExists(public_path('og-preview.png'));
        $this->assertFileExists(public_path('og-preview.svg'));
    }
}
