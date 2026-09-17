import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/builder-canva-pro.css',
                'resources/css/builder-canva-ultra.css',
                'resources/css/builder-canva-ultimate.css',
                'resources/css/builder-layers-enhancement.css',
                'resources/js/app.js',
                'resources/js/passkeys.js',
                'resources/js/builder-studio.js',
                'resources/js/builder-interactions.js',
                'resources/js/builder-canva.js',
                'resources/js/builder-canva-runtime.js',
                'resources/js/builder-canva-pro.js',
                'resources/js/builder-elements-library.js',
                'resources/js/builder-canva-ultra-safe.js',
                'resources/js/builder-canva-ultimate.js',
                'resources/js/builder-layers-enhancement.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});
