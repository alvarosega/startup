/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
      './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
      './storage/framework/views/*.php',
      './resources/views/**/*.blade.php',
      './resources/js/**/*.vue', // Esta es la línea vital
  ],
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--background) / <alpha-value>)',
        foreground: 'hsl(var(--foreground) / <alpha-value>)',
        primary: {
          DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
          foreground: 'hsl(var(--primary-foreground) / <alpha-value>)',
        },
        card: {
          DEFAULT: 'hsl(var(--card) / <alpha-value>)',
          foreground: 'hsl(var(--card-foreground) / <alpha-value>)',
          border: 'hsl(var(--card-border) / <alpha-value>)',
        },
        accent: 'hsl(var(--accent) / <alpha-value>)',
        border: 'hsl(var(--border) / <alpha-value>)',
        input: 'hsl(var(--input) / <alpha-value>)',
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
      },
      borderRadius: {
        '3xl': '24px', /* Ley de Radio establecida en DRC */
        'xl': '12px',
      },
      boxShadow: {
        'apple-soft': '0 8px 30px rgba(0, 0, 0, 0.04)',
        'f1-glow': '0 0 15px -3px hsl(var(--primary) / 0.5), 0 0 6px -2px hsl(var(--primary) / 0.3)',
      },
      transitionTimingFunction: {
        'ios': 'cubic-bezier(0.32, 0.72, 0, 1)',
      }
    },
  },
  plugins: [],
}