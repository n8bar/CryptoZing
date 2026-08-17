import wordlistSource from '../wordlists/bip39-english.txt?raw';

/**
 * Browser-side mirror of the signing-material branch of
 * App\Services\WalletKeyInput::parse. The server stays the authority — this
 * exists only so a pasted seed phrase or private key is caught before it can
 * cross the wire to the address-preview endpoint.
 *
 * The checks below deliberately reproduce the PHP semantics rather than the
 * idiomatic JavaScript ones, so both sides return the same verdict for the
 * same input:
 *
 *  - PHP's `trim()` charlist and preg's `\s` class are narrower than
 *    JavaScript's, which treats non-breaking and other Unicode spaces as
 *    whitespace. Widening them here would make the client reject input the
 *    server accepts.
 *  - PHP's `strtolower()` is byte-wise ASCII; `String#toLowerCase` is
 *    Unicode-aware and would fold characters such as U+212A into the a-z range.
 *
 * The wordlist is imported from the same vendored file the server reads, so
 * there is one source of truth. Truncating it to BIP39's unique four-letter
 * prefixes saves roughly 1.5 KB gzipped but cannot work here: the segmenter
 * needs each word's real length to split a phrase that arrives with its
 * whitespace already stripped.
 */

const MIN_MNEMONIC_WORDS = 12;
const MIN_WORD_LENGTH = 3;
const MAX_WORD_LENGTH = 8;
const MAX_MNEMONIC_LENGTH = 256;

/** PHP `trim()` default charlist: " \t\n\r\0\x0B". */
const PHP_TRIM = /^[ \t\n\r\0\v]+|[ \t\n\r\0\v]+$/g;

/** PHP preg `\s` without the /u modifier. */
const PHP_WHITESPACE = /[ \t\n\v\f\r]+/g;

const XPRV_PATTERN = /[xyztuvYZUV]prv[A-Za-z0-9]+/;
const WIF_PATTERN = /^[59KLc][1-9A-HJ-NP-Za-km-z]{50,51}$/;
const BIP38_PATTERN = /^6P[1-9A-HJ-NP-Za-km-z]{56}$/;
const HEX_KEY_PATTERN = /^[0-9a-f]{64}$/i;
const NON_WORDLIST_CHARACTER = /[^a-z]/;

const wordlist = new Set(
    wordlistSource
        .split('\n')
        .map((word) => word.trim())
        .filter((word) => word !== '')
);

/** Byte-wise ASCII lowercasing, matching PHP's `strtolower()`. */
const asciiLowercase = (value) => value.replace(/[A-Z]/g, (character) => character.toLowerCase());

/**
 * The message the server returns for signing material. Kept identical so the
 * user reads the same sentence whichever side catches it.
 */
export const SIGNING_MATERIAL_MESSAGE =
    'That looks like a private key or seed phrase. Never paste those here — only the public account key or receive descriptor.';

/**
 * Whether the input segments into BIP39 wordlist entries. Whitespace is removed
 * first, because the form strips it before submitting and a pasted phrase
 * arrives concatenated. Account keys and descriptors cannot reach the
 * segmenter: they carry digits, capitals, or parentheses, and the wordlist is
 * lowercase letters only.
 */
function looksLikeMnemonic(value) {
    const compact = asciiLowercase(value.replace(PHP_WHITESPACE, ''));
    const { length } = compact;

    if (
        length < MIN_MNEMONIC_WORDS * MIN_WORD_LENGTH ||
        length > MAX_MNEMONIC_LENGTH ||
        NON_WORDLIST_CHARACTER.test(compact)
    ) {
        return false;
    }

    // Words share prefixes ("add" and "address"), so count the best
    // segmentation rather than taking the first one greedily.
    const best = new Array(length + 1).fill(-1);
    best[0] = 0;

    for (let at = 0; at < length; at += 1) {
        if (best[at] < 0) {
            continue;
        }

        for (let take = MIN_WORD_LENGTH; take <= MAX_WORD_LENGTH && at + take <= length; take += 1) {
            if (wordlist.has(compact.slice(at, at + take))) {
                best[at + take] = Math.max(best[at + take], best[at] + 1);
            }
        }
    }

    return best[length] >= MIN_MNEMONIC_WORDS;
}

/**
 * WIF, BIP38, and raw hex private keys. An account public key is far longer
 * than any of these, so none of them can collide with one.
 */
function looksLikePrivateKey(value) {
    const compact = value.replace(PHP_WHITESPACE, '');

    return (
        WIF_PATTERN.test(compact) || BIP38_PATTERN.test(compact) || HEX_KEY_PATTERN.test(compact)
    );
}

/**
 * True when the input contains an extended private key, a BIP39 mnemonic, or a
 * WIF/BIP38/raw-hex private key.
 *
 * @param {unknown} value
 * @returns {boolean}
 */
export function containsSigningMaterial(value) {
    if (typeof value !== 'string') {
        return false;
    }

    const trimmed = value.replace(PHP_TRIM, '');

    return (
        XPRV_PATTERN.test(trimmed) || looksLikeMnemonic(trimmed) || looksLikePrivateKey(trimmed)
    );
}
