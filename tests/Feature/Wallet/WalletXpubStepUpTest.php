<?php

namespace Tests\Feature\Wallet;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Models\WalletSetting;
use App\Services\HdWallet;
use App\Services\TwoFactor\TwoFactorCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WalletXpubStepUpTest extends TestCase
{
    use RefreshDatabase;

    private const OLD_XPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    private const NEW_XPUB = 'tpubDCebkncrKQyknyD2vUPDtF3WN62cQUMqj5Md3roBSosCf1KePmyZshW3sNhBrKmNsuB9SSxxcq2bat68jkyajPcThA1jJqHgfByb8rNz7tV';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('wallet.default_network', 'testnet');

        // Skip real key derivation (mirrors the existing wallet-settings tests).
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')->andReturn('tb1qtestaddress00000000000000000000000');
        });
    }

    public function test_repoint_is_rejected_without_the_current_password(): void
    {
        $user = $this->userWithWallet();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::NEW_XPUB,
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertSame(self::OLD_XPUB, $this->currentXpub($user));
    }

    public function test_repoint_requires_a_valid_2fa_code_when_2fa_enabled(): void
    {
        $user = $this->userWithWallet(['two_factor_email_enabled_at' => now()]);

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::NEW_XPUB,
            'current_password' => 'password',
            'two_factor_code' => '000000',
        ]);

        $response->assertSessionHasErrors('two_factor_code');
        $this->assertSame(self::OLD_XPUB, $this->currentXpub($user));
    }

    public function test_repoint_succeeds_with_password_when_no_two_factor(): void
    {
        $user = $this->userWithWallet();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::NEW_XPUB,
            'current_password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(self::NEW_XPUB, $this->currentXpub($user));
    }

    public function test_repoint_succeeds_with_password_and_valid_two_factor_code(): void
    {
        Mail::fake();
        $user = $this->userWithWallet(['two_factor_email_enabled_at' => now()]);

        app(TwoFactorCodeService::class)->sendCode($user);
        $code = null;
        Mail::assertSent(TwoFactorCodeMail::class, function (TwoFactorCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::NEW_XPUB,
            'current_password' => 'password',
            'two_factor_code' => $code,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(self::NEW_XPUB, $this->currentXpub($user));
    }

    public function test_first_onboarding_is_exempt_from_step_up(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::NEW_XPUB,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(self::NEW_XPUB, $this->currentXpub($user));
    }

    private function userWithWallet(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);

        $user->walletSetting()->create([
            'network' => 'testnet',
            'bip84_xpub' => self::OLD_XPUB,
            'onboarded_at' => now(),
        ]);

        return $user;
    }

    private function currentXpub(User $user): string
    {
        return WalletSetting::where('user_id', $user->id)->firstOrFail()->bip84_xpub;
    }
}
