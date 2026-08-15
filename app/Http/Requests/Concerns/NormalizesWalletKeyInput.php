<?php

namespace App\Http\Requests\Concerns;

use App\Services\WalletKeyInput;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use InvalidArgumentException;

trait NormalizesWalletKeyInput
{
    private ?string $walletKeyInputError = null;

    private bool $walletKeyInputSafeToEcho = false;

    /**
     * Failed validation flashes the input to the session, which for a pasted
     * seed phrase or private key would persist signing material in plaintext
     * and echo it back into the form. Only recognizable public key material is
     * worth repopulating, so anything else is scrubbed once the rejection
     * message has been produced. The handler flashes the underlying request,
     * not this one.
     */
    protected function failedValidation(ValidatorContract $validator): void
    {
        if (! $this->walletKeyInputSafeToEcho) {
            $this->merge(['bip84_xpub' => '']);
            app('request')->merge(['bip84_xpub' => '']);
        }

        parent::failedValidation($validator);
    }

    /**
     * Whether a rejected value may be echoed back into the form. The browser
     * strips whitespace before submitting, so a pasted seed phrase arrives
     * concatenated and never reaches the signing-material check — recognizing
     * what is safe beats trying to enumerate what is not.
     */
    private function walletKeyInputIsSafeToEcho(string $raw): bool
    {
        $compact = (string) preg_replace('/\s+/', '', $raw);

        if (preg_match('/prv/i', $compact)) {
            return false;
        }

        return preg_match('/^(x|y|z|t|u|v)pub[A-Za-z0-9]*$/i', $compact) === 1
            || str_contains($compact, '(');
    }

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

        $this->walletKeyInputSafeToEcho = $this->walletKeyInputIsSafeToEcho($raw);

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
