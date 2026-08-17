import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/memorify-animations.js', 'resources/js/home-animations.js', 'resources/js/dashboard-animations.js', 'resources/js/connections-animations.js', 'resources/js/memories-animations.js', 'resources/js/letters-animations.js', 'resources/js/gallery-animations.js', 'resources/js/calendar-animations.js', 'resources/js/timeline-animations.js', 'resources/js/favorites-animations.js', 'resources/js/shared-animations.js', 'resources/js/important-dates-animations.js', 'resources/js/couple-timeline-animations.js', 'resources/js/bucket-list-animations.js', 'resources/js/playlists-animations.js', 'resources/js/notifications-animations.js', 'resources/js/about-animations.js', 'resources/js/features-animations.js', 'resources/js/memories-show-animations.js', 'resources/js/letters-show-animations.js', 'resources/js/search-animations.js', 'resources/js/profile-animations.js', 'resources/js/contact-animations.js', 'resources/js/auth-animations.js', 'resources/js/settings-animations.js', 'resources/js/memories-form-animations.js', 'resources/js/letters-form-animations.js', 'resources/js/shared-form-animations.js', 'resources/js/calendar-events-animations.js', 'resources/js/important-dates-form-animations.js', 'resources/js/playlists-form-animations.js'],
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
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
