import { defineConfig } from 'vitest/config';

/**
 * The JS tests get their own config so vite.config.js stays a build config.
 * Loading it here would pull in laravel-vite-plugin, which refuses to start
 * under CI — it reads any Vite server as the HMR dev server. These tests import
 * plain ES modules and need none of it; `?raw` imports are core Vite.
 */
export default defineConfig({
    test: {
        include: ['tests/js/**/*.test.js'],
        environment: 'node',
    },
});
