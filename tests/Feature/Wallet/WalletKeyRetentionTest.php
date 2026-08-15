<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WalletKeyRetentionTest extends TestCase
{
    use RefreshDatabase;

    private const SEED_PHRASE = 'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about';
    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';
    private const XPRV = 'xprv9s21ZrQH143K3QTDL4LXw2F7HEK3wJUD2nW2nRk4stbPy6cq3jPPqjiChkVvvNKmPGJxWUtg6LnF5kejMRNNU3TGtRBeJgk33yuGBxrMPHi';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('wallet.default_network', 'testnet');

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')->andReturn('tb1qtestaddress00000000000000000000000');
        });
    }

    public function test_rejected_seed_phrase_is_not_flashed_to_the_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::SEED_PHRASE,
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringNotContainsString(self::SEED_PHRASE, json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }

    public function test_rejected_private_key_is_not_flashed_to_the_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::XPRV,
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringNotContainsString(self::XPRV, json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }

    public function test_whitespace_stripped_seed_phrase_is_not_flashed_to_the_session(): void
    {
        $user = User::factory()->create();
        $stripped = str_replace(' ', '', self::SEED_PHRASE);

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => $stripped,
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringNotContainsString($stripped, json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }

    public function test_wif_private_key_is_not_flashed_to_the_session(): void
    {
        $user = User::factory()->create();
        $wif = '5HueCGU8rMjxEXxiPuD5BDku4MkFqeZyd4dZ1jvhTVqvbTLvyTJ';

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => $wif,
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringNotContainsString($wif, json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }

    public function test_unrecognized_input_is_still_flashed_so_the_form_repopulates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => 'not-a-key',
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringContainsString('not-a-key', json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }

    public function test_rejected_descriptor_is_still_flashed_so_the_form_repopulates(): void
    {
        $user = User::factory()->create();
        $typo = 'tr(' . self::TPUB . '/0/';

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => $typo,
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringContainsString($typo, json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }

    public function test_rejected_public_key_is_still_flashed_so_the_form_repopulates(): void
    {
        $user = User::factory()->create();
        $wrongNetworkKey = 'xpub6CVRu64f49yeofVCH2SnFbJNvG7ttEoEHvRUtE9jVMBtdPz8HPWdWLhJFiQrbhcEwkLQcXniuqmDeYBTk4azbEuCR4iGSzuChP8Ti3ZqWcJ';

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => $wrongNetworkKey,
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertStringContainsString($wrongNetworkKey, json_encode(session()->all(), JSON_UNESCAPED_SLASHES));
    }
}
