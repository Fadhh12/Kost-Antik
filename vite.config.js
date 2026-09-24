import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // map.js dan chart.js hanya dimuat di halaman yang membutuhkan.
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/map.js'],
            refresh: true,
        }),
    ],
});
