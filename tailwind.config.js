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
            colors: {
                neutral: {
                    50: 'hsl(var(--neutral-50) / <alpha-value>)',
                    100: 'hsl(var(--neutral-100) / <alpha-value>)',
                    200: 'hsl(var(--neutral-200) / <alpha-value>)',
                    300: 'hsl(var(--neutral-300) / <alpha-value>)',
                    400: 'hsl(var(--neutral-400) / <alpha-value>)',
                    500: 'hsl(var(--neutral-500) / <alpha-value>)',
                    600: 'hsl(var(--neutral-600) / <alpha-value>)',
                    700: 'hsl(var(--neutral-700) / <alpha-value>)',
                    800: 'hsl(var(--neutral-800) / <alpha-value>)',
                    900: 'hsl(var(--neutral-900) / <alpha-value>)',
                    950: 'hsl(var(--neutral-950) / <alpha-value>)',
                },
                background: 'hsl(var(--background) / <alpha-value>)',
                foreground: 'hsl(var(--foreground) / <alpha-value>)',
                primary: {
                    DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
                    aaa: 'hsl(var(--primary-text-aaa) / <alpha-value>)',
                    foreground: 'hsl(var(--primary-foreground) / <alpha-value>)',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary) / <alpha-value>)',
                    foreground: 'hsl(var(--secondary-foreground) / <alpha-value>)',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted) / <alpha-value>)',
                    foreground: 'hsl(var(--muted-foreground) / <alpha-value>)',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive) / <alpha-value>)',
                    foreground: 'hsl(var(--destructive-foreground) / <alpha-value>)',
                    aaa: 'hsl(var(--destructive-text-aaa) / <alpha-value>)',
                },
                card: {
                    DEFAULT: 'hsl(var(--card) / <alpha-value>)',
                    foreground: 'hsl(var(--card-foreground) / <alpha-value>)',
                    border: 'hsl(var(--card-border) / <alpha-value>)',
                },
                success: 'hsl(var(--success) / <alpha-value>)',
                warning: 'hsl(var(--warning) / <alpha-value>)',
                error: 'hsl(var(--error) / <alpha-value>)',
                info: 'hsl(var(--info) / <alpha-value>)',
                accent: 'hsl(var(--accent) / <alpha-value>)',
                border: 'hsl(var(--border) / <alpha-value>)',
                input: 'hsl(var(--input) / <alpha-value>)',
                ring: 'hsl(var(--ring) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['var(--font-sans)', 'sans-serif'],
            },
            borderRadius: {
                'sm': 'var(--radius-sm, 4px)',
                'md': 'var(--radius-md, 6px)',
                'lg': 'var(--radius-lg, 8px)',
                'xl': 'var(--radius-xl, 12px)',
            },
            boxShadow: {
                'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'flat': 'var(--shadow-flat)',
                'subtle': 'var(--shadow-subtle)',
                'f1-glow': 'var(--shadow-f1-glow)',
            }
        },
    },
    plugins: [],
}