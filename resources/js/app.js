import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('walletValidation', (config = {}) => ({
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

    init() {
        if (this.$refs.input) {
            const existingValue = this.$refs.input.value;
            if (existingValue && !this.value) {
                this.value = existingValue;
            }
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

    handleInput() {
        this.hasServerError = false;
        this.lastValidatedValue = null;

        if (this.status !== 'idle') {
            this.status = 'idle';
            this.message = '';
            this.address = '';
        }
    },

    handleBlur() {
        if (!this.validationUrl) {
            return;
        }

        if (this.cleanedValue()) {
            this.validate();
        }
    },

    async validate({ force = false } = {}) {
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
                body: JSON.stringify({ bip84_xpub: cleaned }),
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
}));

Alpine.data('newClientPicker', (config = {}) => ({
    storeUrl: config.storeUrl || '',
    csrfToken: config.csrfToken || '',
    previousSelection: config.selected || '',
    promptOpen: false,
    submitting: false,
    form: { name: '', email: '' },
    errors: {},

    onSelectChange(event) {
        if (event.target.value === '__new__') {
            event.target.value = this.previousSelection;
            this.openPrompt();
            return;
        }
        this.previousSelection = event.target.value;
    },

    openPrompt() {
        this.form = { name: '', email: '' };
        this.errors = {};
        this.promptOpen = true;
        this.$dispatch('open-modal', 'create-client');
    },

    onModalClosed(name) {
        if (name !== 'create-client' || !this.promptOpen) {
            return;
        }
        this.promptOpen = false;
        this.$refs.clientSelect.focus();
    },

    async submit() {
        if (this.submitting) {
            return;
        }
        this.submitting = true;
        this.errors = {};

        try {
            const response = await fetch(this.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(this.form),
            });

            if (response.status === 201) {
                this.insertClient(await response.json());
                this.$dispatch('close-modal', 'create-client');
                return;
            }

            if (response.status === 422) {
                const payload = await response.json();
                this.errors = Object.fromEntries(
                    Object.entries(payload.errors || {}).map(([field, messages]) => [field, messages[0]])
                );
                return;
            }

            this.errors = { form: 'Could not create the client. Refresh the page and try again.' };
        } catch (error) {
            this.errors = { form: 'Could not reach the server. Check your connection and try again.' };
        } finally {
            this.submitting = false;
        }
    },

    insertClient(client) {
        const select = this.$refs.clientSelect;
        const option = new Option(client.name, client.id);
        const clientOptions = [...select.options].filter(
            (o) => o.value !== '' && o.value !== '__new__' && !o.disabled
        );
        const before = clientOptions.find(
            (o) => o.text.localeCompare(client.name, undefined, { sensitivity: 'base' }) > 0
        );
        select.add(option, before || null);
        select.value = String(client.id);
        this.previousSelection = select.value;
    },
}));

Alpine.start();

const initHorizontalScrollFades = () => {
    const wrappers = document.querySelectorAll('[data-scroll-fade-wrapper]');

    wrappers.forEach((wrapper) => {
        const container = wrapper.querySelector('[data-scroll-fade-container]');
        const fade = wrapper.querySelector('[data-scroll-fade]');

        if (!container || !fade) {
            return;
        }

        const sync = () => {
            const hasOverflow = container.scrollWidth > (container.clientWidth + 1);
            fade.classList.toggle('hidden', !hasOverflow);
        };

        sync();

        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(sync);
            observer.observe(container);

            const table = container.querySelector('table');
            if (table) {
                observer.observe(table);
            }
        }

        container.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        window.addEventListener('orientationchange', sync);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHorizontalScrollFades, { once: true });
} else {
    initHorizontalScrollFades();
}
