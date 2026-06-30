import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                playfair: ['"Playfair Display"', 'serif'],
            },
            colors: {
                hotel: {
                    gold: '#c8a96e',
                    'gold-hover': '#b8935a',
                    dark: '#1a1a2e',
                    accent: '#16213e',
                    light: '#f8f5f0',
                }
            }
        },
    },

    plugins: [forms, typography],
};
