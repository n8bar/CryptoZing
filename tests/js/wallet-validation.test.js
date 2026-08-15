import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { SIGNING_MATERIAL_MESSAGE } from '../../resources/js/signing-material';
import { walletValidation } from '../../resources/js/wallet-validation';

const XPUB =
    'xpub6BgBgsespWvERF3LHQu6CnqdvfEvtMcQjYrcRzx53QJjSxarj2afYWcLteoGVky7D3UKDP9QyrLprQ3VCECoY49yfdDEHGCtMMj92pReUsQ';
const TWELVE_WORDS =
    'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about';

const okResponse = () => ({
    ok: true,
    json: async () => ({ address: 'bc1qexampleaddress' }),
});

function mount(config = {}) {
    const input = { value: '', focus: vi.fn() };
    const component = walletValidation({ validationUrl: '/wallet/settings/validate', ...config });

    component.$refs = { input };
    component.$nextTick = (fn) => {
        if (fn) {
            fn();
        }
        return Promise.resolve();
    };

    return { component, input };
}

beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn(okResponse));
    vi.stubGlobal('document', {
        querySelector: () => ({ getAttribute: () => 'test-csrf-token' }),
    });
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('warning copy', () => {
    it('matches the server rejection message verbatim', () => {
        const source = readFileSync(
            resolve(__dirname, '../../app/Http/Requests/Concerns/NormalizesWalletKeyInput.php'),
            'utf8'
        );
        const match = source.match(/'signing-material' => '([^']*)'/);

        expect(match, 'server signing-material message not found — has it moved?').not.toBeNull();
        expect(SIGNING_MATERIAL_MESSAGE).toBe(match[1]);
    });
});

describe('blur', () => {
    it('rejects a pasted seed phrase without calling the validation endpoint', () => {
        const { component } = mount();
        component.value = TWELVE_WORDS;

        component.handleBlur();

        expect(fetch).not.toHaveBeenCalled();
        expect(component.status).toBe('error');
        expect(component.message).toBe(SIGNING_MATERIAL_MESSAGE);
    });

    it('clears the field so the signing material is not left on screen', () => {
        const { component, input } = mount();
        component.value = TWELVE_WORDS;

        component.handleBlur();

        expect(component.value).toBe('');
        expect(input.value).toBe('');
    });

    it('returns focus to the field', () => {
        const { component, input } = mount();
        component.value = TWELVE_WORDS;

        component.handleBlur();

        expect(input.focus).toHaveBeenCalled();
    });

    it('rejects a seed phrase that only looks like one once whitespace is stripped', () => {
        const { component } = mount();
        // Non-breaking spaces survive the server's whitespace strip but not the
        // form's, so the concatenated form is what would reach the wire.
        component.value = TWELVE_WORDS.split(' ').join('\u00a0');

        component.handleBlur();

        expect(fetch).not.toHaveBeenCalled();
        expect(component.status).toBe('error');
    });

    it('still validates an ordinary account key', async () => {
        const { component } = mount();
        component.value = XPUB;

        await component.handleBlur();

        expect(fetch).toHaveBeenCalledTimes(1);
        expect(component.status).toBe('success');
    });
});

describe('paste', () => {
    const pasteEvent = (text) => ({
        clipboardData: { getData: () => text },
        preventDefault: vi.fn(),
    });

    it('blocks the paste so the seed phrase never reaches the field', () => {
        const { component, input } = mount();
        const event = pasteEvent(TWELVE_WORDS);

        component.handlePaste(event);

        expect(event.preventDefault).toHaveBeenCalled();
        expect(component.value).toBe('');
        expect(input.value).toBe('');
    });

    it('warns and does not call the validation endpoint', () => {
        const { component } = mount();

        component.handlePaste(pasteEvent(TWELVE_WORDS));

        expect(fetch).not.toHaveBeenCalled();
        expect(component.status).toBe('error');
        expect(component.message).toBe(SIGNING_MATERIAL_MESSAGE);
    });

    it('discards anything already typed in the field', () => {
        const { component } = mount();
        component.value = 'xpub';

        component.handlePaste(pasteEvent(TWELVE_WORDS));

        expect(component.value).toBe('');
    });

    it('allows an ordinary account key through untouched', () => {
        const { component } = mount();
        const event = pasteEvent(XPUB);

        component.handlePaste(event);

        expect(event.preventDefault).not.toHaveBeenCalled();
        expect(component.status).toBe('idle');
    });

    it('survives a paste event with no clipboard data', () => {
        const { component } = mount();
        const event = { preventDefault: vi.fn() };

        expect(() => component.handlePaste(event)).not.toThrow();
        expect(event.preventDefault).not.toHaveBeenCalled();
    });
});

describe('validate', () => {
    it('refuses to send signing material even when called directly', async () => {
        const { component } = mount();
        component.value = TWELVE_WORDS;

        const result = await component.validate({ force: true });

        expect(result).toBe('error');
        expect(fetch).not.toHaveBeenCalled();
        expect(component.message).toBe(SIGNING_MATERIAL_MESSAGE);
    });
});

describe('submit', () => {
    it('does not submit the form when the field holds signing material', async () => {
        const { component } = mount();
        component.value = TWELVE_WORDS;
        const event = { target: { submit: vi.fn() } };

        await component.handleSubmit(event);

        expect(fetch).not.toHaveBeenCalled();
        expect(event.target.submit).not.toHaveBeenCalled();
        expect(component.isSubmitting).toBe(false);
        expect(component.message).toBe(SIGNING_MATERIAL_MESSAGE);
    });

    it('does not submit when the network call fails but the field is signing material', async () => {
        vi.stubGlobal(
            'fetch',
            vi.fn(() => Promise.reject(new Error('offline')))
        );
        const { component } = mount();
        component.value = TWELVE_WORDS;
        const event = { target: { submit: vi.fn() } };

        await component.handleSubmit(event);

        expect(event.target.submit).not.toHaveBeenCalled();
    });

    it('submits an ordinary account key', async () => {
        const { component } = mount();
        component.value = XPUB;
        const event = { target: { submit: vi.fn() } };

        await component.handleSubmit(event);

        expect(fetch).toHaveBeenCalledTimes(1);
        expect(event.target.submit).toHaveBeenCalled();
    });
});
