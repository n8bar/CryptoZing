<?php

namespace App\Http\Requests\Concerns;

use App\Services\WalletKeyInput;
use InvalidArgumentException;

trait NormalizesWalletKeyInput
{
    private ?string $walletKeyInputError = null;

    /**
     * Parse the raw key input before whitespace normalization so seed phrases
     * and descriptors are recognized as pasted. Merges the extracted key and
     * the effective script type (stated by the key > chosen > bip84).
     */
    protected function prepareWalletKeyInput(): void
    {
        $raw = $this->input('bip84_xpub');
        if (! is_string($raw) || trim($raw) === '') {
            return;
        }

        $chosen = $this->input('script_type');
        $chosen = in_array($chosen, ['bip84', 'bip86'], true) ? $chosen : null;

        try {
            $parsed = WalletKeyInput::parse($raw);
        } catch (InvalidArgumentException $e) {
            $this->walletKeyInputError = match ($e->getMessage()) {
                'signing-material' => 'That looks like a private key or seed phrase. Never paste those here — only the public account key or receive descriptor.',
                'malformed-descriptor' => 'We could not read that descriptor. Copy the receive descriptor exactly as your wallet exports it.',
                default => 'That key format is not supported. Paste an account public key or a wpkh()/tr() receive descriptor.',
            };
            $this->merge(['script_type' => $chosen ?? 'bip84']);

            return;
        }

        $stated = $parsed['script_type'];
        if ($stated !== null && $chosen !== null && $stated !== $chosen) {
            $this->walletKeyInputError = 'That key states its own address type, which conflicts with your selection. The key decides — match the selection to the key or paste a different one.';
        }

        $this->merge([
            'bip84_xpub' => preg_replace('/\s+/', '', $parsed['key']),
            'script_type' => $stated ?? $chosen ?? 'bip84',
        ]);
    }

    protected function walletKeyRules(string $network, int $maxLength = 256): array
    {
        $prefixes = $network === 'mainnet' ? 'xpub|zpub' : 'tpub|vpub';

        return [
            'bip84_xpub' => [
                'required',
                'bail',
                function ($attribute, $value, $fail) {
                    if ($this->walletKeyInputError !== null) {
                        $fail($this->walletKeyInputError);
                    }
                },
                'string',
                "max:{$maxLength}",
                "regex:/^({$prefixes})[A-Za-z0-9]+$/",
            ],
            'script_type' => ['required', 'in:bip84,bip86'],
        ];
    }

    protected function walletKeyMessages(): array
    {
        return [
            'bip84_xpub.required' => 'Please paste your wallet account key.',
            'bip84_xpub.regex' => 'That key does not look right. Check you copied the full account public key (no spaces or line breaks).',
        ];
    }
}
