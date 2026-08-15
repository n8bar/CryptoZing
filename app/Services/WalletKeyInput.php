<?php

namespace App\Services;

use InvalidArgumentException;

class WalletKeyInput
{
    private const MIN_MNEMONIC_WORDS = 12;
    private const MIN_WORD_LENGTH = 3;
    private const MAX_WORD_LENGTH = 8;

    /** @var array<string, true>|null */
    private static ?array $wordlist = null;

    /**
     * Parse raw wallet-key input: a bare account key, a SLIP-132 key, or a
     * wpkh()/tr() descriptor (key origins, a /0/* suffix, and a trailing
     * checksum tolerated).
     *
     * Returns ['key' => string, 'script_type' => 'bip84'|'bip86'|null];
     * script_type is null when the input does not state it (bare xpub/tpub).
     * Bare strings that are not descriptors pass through untyped so the
     * existing format validation produces the standard rejection.
     *
     * @throws InvalidArgumentException message is a reason code:
     *         signing-material | unsupported-format | malformed-descriptor
     */
    public static function parse(string $raw): array
    {
        $trimmed = trim($raw);

        if (preg_match('/[xyztuvYZUV]prv[A-Za-z0-9]+/', $trimmed)) {
            throw new InvalidArgumentException('signing-material');
        }
        if (self::looksLikeMnemonic($trimmed) || self::looksLikePrivateKey($trimmed)) {
            throw new InvalidArgumentException('signing-material');
        }

        if (!str_contains($trimmed, '(')) {
            $scriptType = null;
            if (preg_match('/^(zpub|vpub)[A-Za-z0-9]+$/', $trimmed)) {
                $scriptType = 'bip84';
            }

            return ['key' => $trimmed, 'script_type' => $scriptType];
        }

        $noChecksum = preg_replace('/#[a-z0-9]{8}$/', '', $trimmed);
        if (!preg_match('/^([a-z]+)\((.+)\)$/s', $noChecksum, $m)) {
            throw new InvalidArgumentException('malformed-descriptor');
        }

        $scriptType = match ($m[1]) {
            'wpkh' => 'bip84',
            'tr' => 'bip86',
            default => throw new InvalidArgumentException('unsupported-format'),
        };

        $inner = preg_replace('/^\[[^\]]*\]/', '', $m[2]);
        if (!preg_match('/^([xtzv]pub[A-Za-z0-9]+)(\/0\/\*)?$/', $inner, $k)) {
            throw new InvalidArgumentException('malformed-descriptor');
        }

        return ['key' => $k[1], 'script_type' => $scriptType];
    }

    /**
     * Whether the input segments into BIP39 wordlist entries. Whitespace is
     * removed first, because the browser strips it before submitting and a
     * pasted phrase arrives concatenated. Account keys and descriptors cannot
     * reach the segmenter: they carry digits, capitals, or parentheses, and
     * the wordlist is lowercase letters only.
     */
    private static function looksLikeMnemonic(string $value): bool
    {
        $compact = strtolower((string) preg_replace('/\s+/', '', $value));
        $length = strlen($compact);

        if ($length < self::MIN_MNEMONIC_WORDS * 3 || $length > 256 || preg_match('/[^a-z]/', $compact)) {
            return false;
        }

        $words = self::wordlist();

        // Words share prefixes ("add" and "address"), so count the best
        // segmentation rather than taking the first one greedily.
        $best = array_fill(0, $length + 1, -1);
        $best[0] = 0;

        for ($at = 0; $at < $length; $at++) {
            if ($best[$at] < 0) {
                continue;
            }

            for ($take = self::MIN_WORD_LENGTH; $take <= self::MAX_WORD_LENGTH && $at + $take <= $length; $take++) {
                if (isset($words[substr($compact, $at, $take)])) {
                    $best[$at + $take] = max($best[$at + $take], $best[$at] + 1);
                }
            }
        }

        return $best[$length] >= self::MIN_MNEMONIC_WORDS;
    }

    /**
     * WIF, BIP38, and raw hex private keys. An account public key is far
     * longer than any of these, so none of them can collide with one.
     */
    private static function looksLikePrivateKey(string $value): bool
    {
        $compact = (string) preg_replace('/\s+/', '', $value);

        return preg_match('/^[59KLc][1-9A-HJ-NP-Za-km-z]{50,51}$/', $compact) === 1
            || preg_match('/^6P[1-9A-HJ-NP-Za-km-z]{56}$/', $compact) === 1
            || preg_match('/^[0-9a-f]{64}$/i', $compact) === 1;
    }

    /**
     * @return array<string, true>
     */
    private static function wordlist(): array
    {
        if (self::$wordlist !== null) {
            return self::$wordlist;
        }

        $path = resource_path('wordlists/bip39-english.txt');
        $words = is_readable($path)
            ? (array) file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            : [];

        return self::$wordlist = array_fill_keys($words, true);
    }
}
