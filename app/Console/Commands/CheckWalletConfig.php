<?php

namespace App\Console\Commands;

use App\Models\UserWalletAccount;
use App\Models\WalletSetting;
use App\Services\DonationKey;
use App\Services\WalletKeyInput;
use Illuminate\Console\Command;
use InvalidArgumentException;

/**
 * Deploy-time check on operator wallet configuration. A donation key that is
 * malformed (#149) or that duplicates an onboarded invoice key (#146) should
 * fail here, not at the first donor: a shared key derives the same addresses on
 * both chains, which collides payments and destroys attribution.
 */
class CheckWalletConfig extends Command
{
    protected $signature = 'wallet:check-config';

    protected $description = 'Verify operator wallet configuration (donation key format, and that it is not the invoice key)';

    public function handle(): int
    {
        $network = (string) config('wallet.default_network', 'testnet');
        $problem = DonationKey::problem();

        if ($problem === 'unconfigured') {
            $this->line('DONATION_WALLET_XPUB is not set — /donate is disabled on this deployment.');

            return self::SUCCESS;
        }

        if ($problem !== null) {
            $this->error('DONATION_WALLET_XPUB ' . $this->explain($problem, $network));

            return self::FAILURE;
        }

        $parsed = DonationKey::parsed();
        $collision = $this->collidingWallet($parsed['key']);

        if ($collision !== null) {
            $this->error("DONATION_WALLET_XPUB is the same account key as the onboarded invoice wallet ({$collision}). Both chains would derive the same addresses, so donation and invoice payments collide and attribution is lost.");

            return self::FAILURE;
        }

        $this->info(sprintf(
            'DONATION_WALLET_XPUB is a usable %s %s key and does not match any onboarded invoice key.',
            $network,
            $parsed['script_type'] ?? 'bip84'
        ));

        return self::SUCCESS;
    }

    private function explain(string $problem, string $network): string
    {
        return match ($problem) {
            'signing-material' => 'looks like a private key or seed phrase. It must be a watch-only account public key or a wpkh()/tr() receive descriptor.',
            'malformed-descriptor' => 'is a descriptor we could not read. Copy it exactly as the wallet exports it.',
            'wrong-network' => "is an account key for the other network, but this deployment runs {$network}.",
            default => 'is not a supported key format. Paste an account public key or a wpkh()/tr() receive descriptor.',
        };
    }

    /**
     * @return string|null a human label for the wallet that shares the key
     */
    private function collidingWallet(string $donationKey): ?string
    {
        foreach (WalletSetting::query()->with('user')->cursor() as $setting) {
            if ($this->sameKey($setting->bip84_xpub, $donationKey)) {
                return 'wallet settings for ' . ($setting->user?->email ?? "user {$setting->user_id}");
            }
        }

        foreach (UserWalletAccount::query()->with('user')->cursor() as $account) {
            if ($this->sameKey($account->bip84_xpub, $donationKey)) {
                return sprintf(
                    'account "%s" for %s',
                    $account->label,
                    $account->user?->email ?? "user {$account->user_id}"
                );
            }
        }

        return null;
    }

    /**
     * Compare account keys, not the strings as stored: a bare key and the same
     * key inside a tr() descriptor are the same wallet, which is exactly the
     * paste that hides itself.
     */
    private function sameKey(?string $stored, string $donationKey): bool
    {
        if ($stored === null || $stored === '') {
            return false;
        }

        try {
            $stored = WalletKeyInput::parse($stored)['key'];
        } catch (InvalidArgumentException $e) {
            // Unparseable stored value cannot be the key we resolved.
        }

        return $stored === $donationKey;
    }
}
