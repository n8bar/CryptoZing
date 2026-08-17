import './bootstrap';

import Alpine from 'alpinejs';

import { walletValidation } from './wallet-validation';

window.Alpine = Alpine;

Alpine.data('walletValidation', walletValidation);

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
