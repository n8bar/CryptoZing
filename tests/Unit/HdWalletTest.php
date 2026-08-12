<?php

namespace Tests\Unit;

use App\Services\HdWallet;
use RuntimeException;
use Tests\TestCase;

class HdWalletTest extends TestCase
{
    private const TESTNET_VPUB = 'vpub5Z9vQhCkh1Z4BtN3fRK7aN5JEq2PHttwbJpsrs2gbHE1nPjQZ5e4DEkZPizSGZNnvmjTiR2zaUjL2Pv5gSLjXUq3ud994RSDjhuyt8LHQvv';
    private const MAINNET_ZPUB = 'zpub6qmcgewKLxt6CpdEi5YU3Kq66trggdaeYvVoGuN56Qegm5oZKs8r7t6gqXeD9mNrScTs8RjHk6JGefcpEapt4Ph3CPbsRQ8AkhbZH92xNDx';

    // BIP86 reference vectors (account xpub at m/86'/0'/0' from the spec's standard mnemonic).
    private const MAINNET_BIP86_XPUB = 'xpub6BgBgsespWvERF3LHQu6CnqdvfEvtMcQjYrcRzx53QJjSxarj2afYWcLteoGVky7D3UKDP9QyrLprQ3VCECoY49yfdDEHGCtMMj92pReUsQ';

    public function test_derives_testnet_vpub_on_external_chain(): void
    {
        $wallet = new HdWallet();

        $this->assertSame(
            'tb1qnj02g6mvfs8ttcra7v4ze03f98lj8qrxp7hl5s',
            $wallet->deriveAddress(self::TESTNET_VPUB, 0, 'testnet')
        );
        $this->assertSame(
            'tb1q4cja6kp3lj95esdvvxms0ggjkpk6vyl69v6qkl',
            $wallet->deriveAddress(self::TESTNET_VPUB, 1, 'testnet')
        );
    }

    public function test_accepts_testnet3_and_testnet4_aliases(): void
    {
        $wallet = new HdWallet();

        $this->assertSame(
            'tb1qnj02g6mvfs8ttcra7v4ze03f98lj8qrxp7hl5s',
            $wallet->deriveAddress(self::TESTNET_VPUB, 0, 'testnet4')
        );
        $this->assertSame(
            'tb1qnj02g6mvfs8ttcra7v4ze03f98lj8qrxp7hl5s',
            $wallet->deriveAddress(self::TESTNET_VPUB, 0, 'testnet3')
        );
    }

    public function test_derives_mainnet_zpub_on_external_chain(): void
    {
        $wallet = new HdWallet();

        $this->assertSame(
            'bc1qr6ddtxn4k8jmg75ejgqg525wwzmtur7zy0nnr0',
            $wallet->deriveAddress(self::MAINNET_ZPUB, 0, 'mainnet')
        );
        $this->assertSame(
            'bc1qfeppl07nsme4va0vcpw0vv0me58wvnst434fph',
            $wallet->deriveAddress(self::MAINNET_ZPUB, 1, 'mainnet')
        );
    }

    public function test_derives_bip86_taproot_addresses_from_bare_xpub(): void
    {
        $wallet = new HdWallet();

        $this->assertSame(
            'bc1p5cyxnuxmeuwuvkwfem96lqzszd02n6xdcjrs20cac6yqjjwudpxqkedrcr',
            $wallet->deriveAddress(self::MAINNET_BIP86_XPUB, 0, 'mainnet', 'bip86')
        );
        $this->assertSame(
            'bc1p4qhjn9zdvkux4e44uhx8tc55attvtyu358kutcqkudyccelu0was9fqzwh',
            $wallet->deriveAddress(self::MAINNET_BIP86_XPUB, 1, 'mainnet', 'bip86')
        );
    }

    public function test_bare_xpub_defaults_to_bip84_segwit(): void
    {
        $wallet = new HdWallet();

        $this->assertStringStartsWith(
            'bc1q',
            $wallet->deriveAddress(self::MAINNET_BIP86_XPUB, 0, 'mainnet')
        );
    }

    public function test_rejects_slip132_key_with_conflicting_script_type(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Script type conflicts');

        $wallet = new HdWallet();
        $wallet->deriveAddress(self::MAINNET_ZPUB, 0, 'mainnet', 'bip86');
    }

    public function test_rejects_mismatched_network(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Network mismatch');

        $wallet = new HdWallet();
        $wallet->deriveAddress(self::TESTNET_VPUB, 0, 'mainnet');
    }
}
