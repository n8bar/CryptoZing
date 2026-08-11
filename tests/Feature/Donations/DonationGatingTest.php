<?php

namespace Tests\Feature\Donations;

use Tests\TestCase;

/**
 * /donate is CryptoZing's own funding page, not a per-instance feature (#135).
 * The donation xpub's presence at boot decides whether the routes exist at all;
 * these tests reboot the app with the env value under test.
 */
class DonationGatingTest extends TestCase
{
    private const PINNED_XPUB = 'tpubPhpunitDonation';

    private function rebootWithXpub(string $xpub): void
    {
        putenv('DONATION_WALLET_XPUB=' . $xpub);
        $_ENV['DONATION_WALLET_XPUB'] = $xpub;
        $_SERVER['DONATION_WALLET_XPUB'] = $xpub;
        $this->refreshApplication();
    }

    protected function tearDown(): void
    {
        putenv('DONATION_WALLET_XPUB=' . self::PINNED_XPUB);
        $_ENV['DONATION_WALLET_XPUB'] = self::PINNED_XPUB;
        $_SERVER['DONATION_WALLET_XPUB'] = self::PINNED_XPUB;

        parent::tearDown();
    }

    public function test_donate_routes_are_absent_when_xpub_is_blank(): void
    {
        $this->rebootWithXpub('');

        $this->get('/donate')->assertNotFound();
        $this->post('/donate')->assertNotFound();
        $this->get('/donate/status')->assertNotFound();
        $this->post('/donate/reset')->assertNotFound();
    }

    public function test_footer_links_to_cryptozing_donate_page_when_xpub_is_blank(): void
    {
        $this->rebootWithXpub('');

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Support CryptoZing');
        $response->assertSee('https://cryptozing.app/donate');
        $response->assertDontSee('>Donate</a>', false);
    }

    public function test_donate_routes_and_local_footer_link_exist_with_xpub_configured(): void
    {
        $this->get('/donate')->assertOk();

        $response = $this->get('/login');
        $response->assertSee('>Donate</a>', false);
        $response->assertDontSee('Support CryptoZing');
    }
}
