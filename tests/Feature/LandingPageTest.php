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

    public function test_social_preview_assets_exist_in_public(): void
    {
        $this->assertFileExists(public_path('og-preview.png'));
        $this->assertFileExists(public_path('og-preview.svg'));
    }
}
