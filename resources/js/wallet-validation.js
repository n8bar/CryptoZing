import { SIGNING_MATERIAL_MESSAGE, containsSigningMaterial } from './signing-material';

export const walletValidation = (config = {}) => ({
    value: config.initialValue || '',
    initialValue: config.initialValue || '',
    status: 'idle',
    message: '',
    address: '',
    isValidating: false,
    isSubmitting: false,
    validationUrl: config.validationUrl || '',
    expectedPrefix: config.expectedPrefix || '',
    hasServerError: Boolean(config.hasServerError),
    lastValidatedValue: null,
    scriptType: config.scriptType || 'bip84',

    init() {
        if (this.$refs.input) {
            const existingValue = this.$refs.input.value;
            if (existingValue && !this.value) {
                this.value = existingValue;
            }
            // Bound here rather than in the template so the guard travels with
            // the component. A paste has to be cancelled on the paste event
            // itself; by the time @input fires the phrase is already in the
            // field.
            this.$refs.input.addEventListener('paste', (event) => this.handlePaste(event));
        }
        if (this.hasServerError) {
            this.$nextTick(() => this.focusInput());
        }
    },

    focusInput() {
        if (this.$refs.input) {
            this.$refs.input.focus();
        }
    },

    cleanedValue() {
        return this.normalizeValue(this.value);
    },

    normalizeValue(input) {
        return (input || '').replace(/\s+/g, '');
    },

    hasValueChanged() {
        return this.normalizeValue(this.value) !== this.normalizeValue(this.initialValue);
    },

    // A key that states its own script type (SLIP-132 prefix or descriptor
    // wrapper) never shows the choice; only bare xpub/tpub keys ask.
    statedScriptType() {
        const cleaned = this.cleanedValue();
        if (/^tr\(/i.test(cleaned)) {
            return 'bip86';
        }
        if (/^wpkh\(/i.test(cleaned) || /^(zpub|vpub)/.test(cleaned)) {
            return 'bip84';
        }
        return null;
    },

    showsScriptTypeChoice() {
        const cleaned = this.cleanedValue();
        return /^(xpub|tpub)/.test(cleaned) && !cleaned.includes('(');
    },

    handleScriptTypeChange() {
        this.lastValidatedValue = null;
        if (this.cleanedValue()) {
            this.validate({ force: true });
        }
    },

    handleInput() {
        this.hasServerError = false;
        this.lastValidatedValue = null;

        const stated = this.statedScriptType();
        if (stated) {
            this.scriptType = stated;
        }

        if (this.status !== 'idle') {
            this.status = 'idle';
            this.message = '';
            this.address = '';
        }
    },

    /**
     * Signing material is checked twice: once as typed, and once in the
     * whitespace-stripped form that would actually be sent. The detector
     * mirrors the server byte for byte, and the server strips a narrower set of
     * whitespace than the browser does, so a phrase separated by non-breaking
     * spaces only looks like one after normalization.
     */
    isSigningMaterial(raw) {
        return containsSigningMaterial(raw) || containsSigningMaterial(this.normalizeValue(raw));
    },

    /**
     * Clearing the field runs against the usual rule that input is preserved on
     * error — that is the point here. Leaving a seed phrase in the textarea
     * keeps it on screen, in the DOM, and one submit away from the session's
     * flash data. The warning renders in the field's existing live region, so
     * it is announced and reserves its own space rather than shifting layout.
     */
    warnSigningMaterial() {
        this.value = '';
        if (this.$refs.input) {
            this.$refs.input.value = '';
        }

        this.address = '';
        this.lastValidatedValue = null;
        this.hasServerError = false;
        this.isValidating = false;
        this.status = 'error';
        this.message = SIGNING_MATERIAL_MESSAGE;
        this.focusInput();
    },

    rejectSigningMaterial(raw) {
        if (!this.isSigningMaterial(raw)) {
            return false;
        }

        this.warnSigningMaterial();

        return true;
    },

    handlePaste(event) {
        const pasted = event?.clipboardData?.getData?.('text') || '';

        if (!pasted || !this.isSigningMaterial(pasted)) {
            return;
        }

        // Cancel the paste so the phrase never reaches the field at all.
        event.preventDefault();
        this.warnSigningMaterial();
    },

    handleBlur() {
        if (this.rejectSigningMaterial(this.value)) {
            return;
        }

        if (!this.validationUrl) {
            return;
        }

        if (this.cleanedValue()) {
            return this.validate();
        }
    },

    async validate({ force = false } = {}) {
        // Every path to the preview endpoint runs through here — blur, submit,
        // the address-type radios, and the "Re-run validation" button — so this
        // is the single place that guarantees signing material never reaches
        // the network, and never reports back as validated.
        if (this.rejectSigningMaterial(this.value)) {
            return 'error';
        }

        if (!this.validationUrl) {
            return 'unknown';
        }

        const cleaned = this.cleanedValue();

        if (!cleaned) {
            this.status = 'error';
            this.message = 'Please paste your wallet account key.';
            this.address = '';
            this.focusInput();
            return 'error';
        }

        if (cleaned !== this.value) {
            this.value = cleaned;
        }

        if (!force && cleaned === this.lastValidatedValue && this.status === 'success') {
            return 'success';
        }

        this.isValidating = true;
        this.status = 'validating';
        this.message = '';
        this.address = '';

        try {
            const response = await fetch(this.validationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ bip84_xpub: cleaned, script_type: this.scriptType }),
            });

            let payload = {};
            try {
                payload = await response.json();
            } catch (error) {
                payload = {};
            }

            if (!response.ok) {
                const message =
                    payload?.errors?.bip84_xpub?.[0] ||
                    payload?.message ||
                    'That key does not look right. Check you copied the full account public key (no spaces or line breaks).';

                this.status = 'error';
                this.message = message;
                this.address = '';
                this.isValidating = false;
                this.focusInput();
                this.lastValidatedValue = cleaned;
                return 'error';
            }

            this.status = 'success';
            this.message = 'Address validated for this key.';
            this.address = payload.address || '';
            this.isValidating = false;
            this.lastValidatedValue = cleaned;
            return 'success';
        } catch (error) {
            this.status = 'error';
            this.message = 'We could not validate this key right now. Please try again.';
            this.address = '';
            this.isValidating = false;
            return 'unknown';
        }
    },

    async handleSubmit(event) {
        if (this.isSubmitting) {
            return;
        }

        this.isSubmitting = true;

        const result = await this.validate({ force: true });

        if (result === 'success' || result === 'unknown') {
            event.target.submit();
            return;
        }

        this.isSubmitting = false;
    },
});
