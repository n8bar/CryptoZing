<?php

namespace Tests\Unit;

use App\Services\WalletKeyInput;
use InvalidArgumentException;
use Tests\TestCase;

class WalletKeyInputTest extends TestCase
{
    private const XPUB = 'xpub6BgBgsespWvERF3LHQu6CnqdvfEvtMcQjYrcRzx53QJjSxarj2afYWcLteoGVky7D3UKDP9QyrLprQ3VCECoY49yfdDEHGCtMMj92pReUsQ';
    private const ZPUB = 'zpub6qmcgewKLxt6CpdEi5YU3Kq66trggdaeYvVoGuN56Qegm5oZKs8r7t6gqXeD9mNrScTs8RjHk6JGefcpEapt4Ph3CPbsRQ8AkhbZH92xNDx';

    public function test_bare_xpub_has_no_stated_script_type(): void
    {
        $parsed = WalletKeyInput::parse(self::XPUB);

        $this->assertSame(self::XPUB, $parsed['key']);
        $this->assertNull($parsed['script_type']);
    }

    public function test_slip132_zpub_states_bip84(): void
    {
        $parsed = WalletKeyInput::parse(self::ZPUB);

        $this->assertSame(self::ZPUB, $parsed['key']);
        $this->assertSame('bip84', $parsed['script_type']);
    }

    public function test_wpkh_descriptor_states_bip84(): void
    {
        $parsed = WalletKeyInput::parse('wpkh(' . self::XPUB . ')');

        $this->assertSame(self::XPUB, $parsed['key']);
        $this->assertSame('bip84', $parsed['script_type']);
    }

    public function test_tr_descriptor_states_bip86(): void
    {
        $parsed = WalletKeyInput::parse('tr(' . self::XPUB . ')');

        $this->assertSame(self::XPUB, $parsed['key']);
        $this->assertSame('bip86', $parsed['script_type']);
    }

    public function test_descriptor_tolerates_key_origin_chain_suffix_and_checksum(): void
    {
        $raw = 'tr([73c5da0a/86h/0h/0h]' . self::XPUB . '/0/*)#a1b2c3d4';

        $parsed = WalletKeyInput::parse($raw);

        $this->assertSame(self::XPUB, $parsed['key']);
        $this->assertSame('bip86', $parsed['script_type']);
    }

    public function test_unsupported_descriptor_wrapper_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported-format');

        WalletKeyInput::parse('wsh(sortedmulti(2,' . self::XPUB . ',' . self::XPUB . '))');
    }

    public function test_malformed_descriptor_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('malformed-descriptor');

        WalletKeyInput::parse('tr(' . self::XPUB);
    }

    public function test_private_key_material_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse('xprv9s21ZrQH143K3QTDL4LXw2F7HEK3wJUD2nW2nRk4stbPy6cq3jPPqjiChkVvvNKmPGJxWUtg6LnF5kejMRNNU3TGtRBeJgk33yuGBxrMPHi');
    }

    public function test_seed_phrase_is_rejected_as_signing_material(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse('abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about');
    }

    public function test_seed_phrase_without_spaces_is_rejected_as_signing_material(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        // The browser strips whitespace before submitting, so this is the form
        // a pasted mnemonic actually arrives in.
        WalletKeyInput::parse('abandonabandonabandonabandonabandonabandonabandonabandonabandonabandonabandonabout');
    }

    public function test_seed_phrase_split_across_lines_is_rejected_as_signing_material(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse("legal\nwinner\tthank  year wave sausage worth useful legal winner thank yellow");
    }

    public function test_twenty_four_word_seed_phrase_is_rejected_as_signing_material(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse(str_repeat('abandon ', 23) . 'art');
    }

    public function test_wif_private_key_is_rejected_as_signing_material(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse('5HueCGU8rMjxEXxiPuD5BDku4MkFqeZyd4dZ1jvhTVqvbTLvyTJ');
    }

    public function test_raw_hex_private_key_is_rejected_as_signing_material(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse('1e99423a4ed27608a15a2616a2b0e9e52ced330ac530edcc32c8ffc6a526aedd');
    }

    public function test_account_public_key_is_not_mistaken_for_signing_material(): void
    {
        $parsed = WalletKeyInput::parse(self::XPUB);

        $this->assertSame(self::XPUB, $parsed['key']);
    }

    public function test_ordinary_junk_is_not_treated_as_signing_material(): void
    {
        // Short unrecognized input must stay a plain format rejection so the
        // form can hand it back.
        $parsed = WalletKeyInput::parse('not-a-key');

        $this->assertSame('not-a-key', $parsed['key']);
        $this->assertNull($parsed['script_type']);
    }

    public function test_descriptor_containing_private_key_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('signing-material');

        WalletKeyInput::parse('tr(xprv9s21ZrQH143K3QTDL4LXw2F7HEK3wJUD2nW2nRk4stbPy6cq3jPPqjiChkVvvNKmPGJxWUtg6LnF5kejMRNNU3TGtRBeJgk33yuGBxrMPHi/0/*)');
    }

    public function test_slip132_private_prefixes_are_rejected_as_signing_material(): void
    {
        foreach (['yprv', 'uprv', 'Yprv', 'Uprv', 'Zprv', 'Vprv'] as $prefix) {
            try {
                WalletKeyInput::parse($prefix . '9s21ZrQH143K3QTDL4LXw2F7HEK3wJUD2nW2nRk4stbPy6cq3jPPqjiChkVvvNKmPGJxWUtg6LnF5kejMRNNU3TGtRBeJgk33yuGBxrMPHi');
                $this->fail("{$prefix} was not rejected");
            } catch (InvalidArgumentException $e) {
                $this->assertSame('signing-material', $e->getMessage(), $prefix);
            }
        }
    }
}
