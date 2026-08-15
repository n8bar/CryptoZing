import { describe, expect, it } from 'vitest';

import { containsSigningMaterial } from '../../resources/js/signing-material';

// Mirrors tests/Unit/WalletKeyInputTest.php. The client detector must return
// the same verdict as App\Services\WalletKeyInput::parse for the same input,
// so every case here has been checked against the PHP implementation.
//
// Key-shaped strings below are synthesized to satisfy the format regexes and
// hold no real key material. The BIP39 vectors are the published all-zeros
// test vector already tracked in the PHP test.

const XPUB =
    'xpub6BgBgsespWvERF3LHQu6CnqdvfEvtMcQjYrcRzx53QJjSxarj2afYWcLteoGVky7D3UKDP9QyrLprQ3VCECoY49yfdDEHGCtMMj92pReUsQ';
const TPUB =
    'tpubDCBWBScQPGv4Xk3JSbhw6wYYpayMjb2eAYyArpbSqQTbLDpphHGAetB6VQgVeftLML8vDSUEWcC2xDi3qJJ3YCDChJDvqVzpgoYSuT52MhJ';
const ZPUB =
    'zpub6qmcgewKLxt6CpdEi5YU3Kq66trggdaeYvVoGuN56Qegm5oZKs8r7t6gqXeD9mNrScTs8RjHk6JGefcpEapt4Ph3CPbsRQ8AkhbZH92xNDx';

// Shape-valid stand-ins: they match the detector's regexes without being keys.
const XPRV_SHAPED = 'xprv' + '9s21ZrQH143K3QTDL4LXw2F7HEK3wJUD2nW2nRk4stbPy6cq3jPPqjiChkVvvNKmPGJ';
const WIF_SHAPED = 'K' + 'z'.repeat(51);
const WIF_TESTNET_SHAPED = 'c' + 'z'.repeat(51);
const WIF_UNCOMPRESSED_SHAPED = '5' + 'z'.repeat(50);
const BIP38_SHAPED = '6P' + 'z'.repeat(56);
const HEX64 = 'a1b2c3d4'.repeat(8);

const TWELVE_WORDS =
    'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about';

describe('containsSigningMaterial — extended private keys', () => {
    it('detects an xprv', () => {
        expect(containsSigningMaterial(XPRV_SHAPED)).toBe(true);
    });

    it.each(['yprv', 'zprv', 'tprv', 'uprv', 'vprv', 'Yprv', 'Zprv', 'Uprv', 'Vprv'])(
        'detects the SLIP-132 private prefix %s',
        (prefix) => {
            expect(containsSigningMaterial(prefix + 'ABCdef0123456789')).toBe(true);
        }
    );

    it('detects an xprv wrapped in a descriptor', () => {
        expect(containsSigningMaterial(`tr(${XPRV_SHAPED}/0/*)`)).toBe(true);
    });

    it('detects an xprv with surrounding whitespace', () => {
        expect(containsSigningMaterial(`  \n${XPRV_SHAPED}\t `)).toBe(true);
    });
});

describe('containsSigningMaterial — BIP39 mnemonics', () => {
    it('detects a space-separated twelve-word phrase', () => {
        expect(containsSigningMaterial(TWELVE_WORDS)).toBe(true);
    });

    it('detects a twelve-word phrase with the whitespace stripped', () => {
        // The form strips whitespace before submitting, so this is the shape a
        // pasted mnemonic actually reaches the server in.
        expect(containsSigningMaterial(TWELVE_WORDS.replace(/\s+/g, ''))).toBe(true);
    });

    it('detects a phrase broken across lines and tabs', () => {
        expect(
            containsSigningMaterial(
                'legal\nwinner\tthank  year wave sausage worth useful legal winner thank yellow'
            )
        ).toBe(true);
    });

    it('detects a twenty-four-word phrase', () => {
        expect(containsSigningMaterial('abandon '.repeat(23) + 'art')).toBe(true);
    });

    it('detects a phrase typed in mixed case', () => {
        expect(containsSigningMaterial(TWELVE_WORDS.toUpperCase())).toBe(true);
    });

    it('counts the best segmentation, not a greedy one', () => {
        // "add" and "address" share a prefix; a greedy left-to-right pass can
        // strand the tail, so the segmenter has to consider both.
        const phrase = 'address '.repeat(11) + 'add';
        expect(containsSigningMaterial(phrase)).toBe(true);
    });

    it('ignores an eleven-word phrase', () => {
        expect(containsSigningMaterial('abandon '.repeat(11))).toBe(false);
        expect(
            containsSigningMaterial(
                'legal winner thank year wave sausage worth useful legal winner thank'
            )
        ).toBe(false);
    });

    it('ignores wordlist-only input longer than the 256 character cap', () => {
        expect(containsSigningMaterial('abandon '.repeat(40))).toBe(false);
    });

    it('ignores ordinary English prose', () => {
        expect(
            containsSigningMaterial('the quick brown fox jumps over the lazy dog again and again')
        ).toBe(false);
        expect(
            containsSigningMaterial(
                'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt'
            )
        ).toBe(false);
    });
});

describe('containsSigningMaterial — WIF, BIP38 and raw hex keys', () => {
    it.each([
        ['compressed WIF', WIF_SHAPED],
        ['testnet WIF', WIF_TESTNET_SHAPED],
        ['uncompressed WIF', WIF_UNCOMPRESSED_SHAPED],
        ['BIP38 encrypted key', BIP38_SHAPED],
    ])('detects a %s', (_label, value) => {
        expect(containsSigningMaterial(value)).toBe(true);
    });

    it('detects a raw 64-character hex private key in either case', () => {
        expect(containsSigningMaterial(HEX64)).toBe(true);
        expect(containsSigningMaterial(HEX64.toUpperCase())).toBe(true);
    });

    it('detects a raw hex private key pasted with whitespace in it', () => {
        expect(containsSigningMaterial(`${HEX64.slice(0, 32)} ${HEX64.slice(32)}`)).toBe(true);
    });

    it('ignores a 63 or 65 character hex string', () => {
        expect(containsSigningMaterial(HEX64.slice(0, 63))).toBe(false);
        expect(containsSigningMaterial(HEX64 + 'a')).toBe(false);
    });
});

describe('containsSigningMaterial — public keys and descriptors pass through', () => {
    it.each([
        ['mainnet xpub', XPUB],
        ['testnet tpub', TPUB],
        ['SLIP-132 zpub', ZPUB],
        ['tr() descriptor', `tr(${XPUB}/0/*)`],
        ['wpkh() descriptor', `wpkh(${XPUB})`],
        ['descriptor with key origin and checksum', `tr([73c5da0a/86h/0h/0h]${XPUB}/0/*)#a1b2c3d4`],
        ['truncated xpub', XPUB.slice(0, -1)],
        ['xpub with a transposed character', XPUB.slice(0, 10) + 'Q' + XPUB.slice(11)],
        ['unrelated junk', 'not-a-key'],
        ['empty string', ''],
        ['whitespace only', '   \n\t  '],
    ])('does not flag a %s', (_label, value) => {
        expect(containsSigningMaterial(value)).toBe(false);
    });

    it('does not flag "xpub" appearing next to the word prv', () => {
        expect(containsSigningMaterial('my prv notes about xpub keys')).toBe(false);
    });
});

describe('containsSigningMaterial — input handling', () => {
    it('returns false for non-string input', () => {
        expect(containsSigningMaterial(null)).toBe(false);
        expect(containsSigningMaterial(undefined)).toBe(false);
        expect(containsSigningMaterial(42)).toBe(false);
        expect(containsSigningMaterial({})).toBe(false);
        expect(containsSigningMaterial([])).toBe(false);
    });

    it('takes no arguments as false', () => {
        expect(containsSigningMaterial()).toBe(false);
    });
});
