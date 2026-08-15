<?php

namespace Tests\Unit;

use App\Services\DonationKey;
use Tests\TestCase;

/**
 * The donation key is operator env config, so nothing validates it on the way
 * in the way onboarding validates a pasted key (#149). These pin the config-only
 * verdict — no database, because the route gate runs on every request.
 */
class DonationKeyTest extends TestCase
{
    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    private const XPUB = 'xpub6BgBgsespWvERF3LHQu6CnqdvfEvtMcQjYrcRzx53QJjSxarj2afYWcLteoGVky7D3UKDP9QyrLprQ3VCECoY49yfdDEHGCtMMj92pReUsQ';

    protected function setUp(): void
    {
        parent::setUp();

        config(['wallet.default_network' => 'testnet']);
    }

    public function test_unconfigured_key_is_not_usable(): void
    {
        config(['donations.xpub' => null]);

        $this->assertFalse(DonationKey::isConfigured());
        $this->assertFalse(DonationKey::isUsable());
        $this->assertSame('unconfigured', DonationKey::problem());
    }

    public function test_bare_account_key_is_usable(): void
    {
        config(['donations.xpub' => self::TPUB]);

        $this->assertTrue(DonationKey::isUsable());
        $this->assertNull(DonationKey::problem());
        $this->assertSame(self::TPUB, DonationKey::parsed()['key']);
    }

    public function test_descriptor_is_usable_and_states_its_script_type(): void
    {
        config(['donations.xpub' => 'tr(' . self::TPUB . '/0/*)']);

        $this->assertTrue(DonationKey::isUsable());
        $this->assertSame(self::TPUB, DonationKey::parsed()['key']);
        $this->assertSame('bip86', DonationKey::parsed()['script_type']);
    }

    public function test_signing_material_is_not_usable(): void
    {
        config(['donations.xpub' => 'tprvPhpunitNeverAKey']);

        $this->assertTrue(DonationKey::isConfigured());
        $this->assertFalse(DonationKey::isUsable());
        $this->assertSame('signing-material', DonationKey::problem());
    }

    public function test_malformed_descriptor_is_not_usable(): void
    {
        config(['donations.xpub' => 'tr(' . self::TPUB]);

        $this->assertFalse(DonationKey::isUsable());
        $this->assertSame('malformed-descriptor', DonationKey::problem());
    }

    public function test_unsupported_descriptor_type_is_not_usable(): void
    {
        config(['donations.xpub' => 'sh(' . self::TPUB . '/0/*)']);

        $this->assertFalse(DonationKey::isUsable());
        $this->assertSame('unsupported-format', DonationKey::problem());
    }

    public function test_key_from_the_other_network_is_not_usable(): void
    {
        config(['donations.xpub' => self::XPUB]);

        $this->assertFalse(DonationKey::isUsable());
        $this->assertSame('wrong-network', DonationKey::problem());
    }

    public function test_mainnet_accepts_a_mainnet_key(): void
    {
        config([
            'wallet.default_network' => 'mainnet',
            'donations.xpub' => self::XPUB,
        ]);

        $this->assertTrue(DonationKey::isUsable());
    }

    public function test_junk_is_not_usable(): void
    {
        config(['donations.xpub' => 'not a key at all']);

        $this->assertFalse(DonationKey::isUsable());
        $this->assertSame('unsupported-format', DonationKey::problem());
    }
}
