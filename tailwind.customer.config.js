/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  // SOLO escanea el silo Customer
  content: [
    './resources/js/Pages/Customer/**/*.vue',
    './resources/js/Components/Shop/**/*.vue',
    './resources/js/Layouts/ShopLayout.vue',
  ],
  theme: {
    extend: {
      colors: {
        background: 'var(--bg-main)',
        surface: 'var(--bg-surface)',
        primary: 'var(--text-primary)',
        muted: 'var(--text-muted)',
        'f1-red': 'var(--f1-red)',
        'f1-red-hover': 'var(--f1-red-hover)',
        'telemetry-green': 'var(--data-mono)',
        'telemetry-warn': 'var(--data-warning)',
        tech: 'var(--border-tech)',
      },
      fontFamily: {
        sans: ['"Titillium Web"', 'system-ui', 'sans-serif'], 
        mono: ['"Fira Code"', 'monospace'], 
      },
      spacing: {
        nav: 'var(--nav-height)',
      },
      transitionDuration: {
        '150': '150ms',
      },
      boxShadow: {
        'tech': '0 10px 15px -3px rgba(0, 0, 0, 0.5)', 
      },
    },
  },
  plugins: [],
}