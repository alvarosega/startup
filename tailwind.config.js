import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                skin: {
                    fill: 'rgb(var(--color-fill) / <alpha-value>)',
                    'fill-card': 'rgb(var(--color-fill-card) / <alpha-value>)',
                    'fill-hover': 'rgb(var(--color-fill-hover) / <alpha-value>)',
                    
                    primary: 'rgb(var(--color-primary) / <alpha-value>)',
                    'primary-hover': 'rgb(var(--color-primary-hover) / <alpha-value>)',
                    'primary-text': 'rgb(var(--color-primary-text) / <alpha-value>)',

                    border: 'rgb(var(--color-border) / <alpha-value>)',
                    muted: 'rgb(var(--color-text-muted) / <alpha-value>)',
                    base: 'rgb(var(--color-text-base) / <alpha-value>)',
                    inverted: 'rgb(var(--color-text-inverted) / <alpha-value>)',
                    
                    danger: 'rgb(var(--color-danger) / <alpha-value>)',
                    success: 'rgb(var(--color-success) / <alpha-value>)',
                }
            },
            borderRadius: {
                'global': 'var(--radius)', 
            }
        },
    },

    plugins: [forms],
};
