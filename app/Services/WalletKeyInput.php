<?php

namespace App\Services;

use InvalidArgumentException;

class WalletKeyInput
{
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

        if (preg_match('/[xtzv]prv[A-Za-z0-9]+/', $trimmed)) {
            throw new InvalidArgumentException('signing-material');
        }
        if (preg_match('/^[a-z]+( +[a-z]+){11,}$/i', $trimmed)) {
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
}
