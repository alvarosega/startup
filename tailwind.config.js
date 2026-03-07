/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    container: {
      center: true,
      padding: '1rem',
    },
    extend: {
      colors: {
        /* Tailwind usará automáticamente la paleta correcta según el Silo */
        background: 'hsl(var(--background) / <alpha-value>)',
        foreground: 'hsl(var(--foreground) / <alpha-value>)',
        border: 'hsl(var(--border) / <alpha-value>)',
        input: 'hsl(var(--input) / <alpha-value>)',
        primary: {
          DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
          foreground: 'hsl(var(--primary-foreground) / <alpha-value>)',
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary) / <alpha-value>)',
          foreground: 'hsl(var(--secondary-foreground) / <alpha-value>)',
        },
        accent: {
          DEFAULT: 'hsl(var(--accent) / <alpha-value>)',
          foreground: 'hsl(var(--accent-foreground) / <alpha-value>)',
        },
        destructive: {
          DEFAULT: 'hsl(var(--destructive) / <alpha-value>)',
          foreground: 'hsl(var(--destructive-foreground) / <alpha-value>)',
        },
        card: {
          DEFAULT: 'hsl(var(--card) / <alpha-value>)',
          foreground: 'hsl(var(--card-foreground) / <alpha-value>)',
        },
        
        /* Alias específicos para Customer (Mapas heredados del código de Vue anterior) */
        surface: 'hsl(var(--card) / <alpha-value>)',
        muted: 'hsl(var(--muted) / <alpha-value>)',
        'f1-red': 'hsl(var(--primary) / <alpha-value>)',
        'f1-red-hover': 'hsl(var(--primary) / 0.8)',
        'telemetry-green': 'hsl(var(--c-green-sys) / <alpha-value>)',
        tech: 'hsl(var(--border) / <alpha-value>)',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],        
        display: ['Orbitron', 'sans-serif'],  
        mono: ['JetBrains Mono', 'monospace'], 
      },
      transitionDuration: {
        '0': '0ms',
        '50': '50ms',
      },
      boxShadow: {
        'neon': '0 0 var(--neon-blur) hsl(var(--primary) / 0.5)',
        'neon-strong': '0 0 calc(var(--neon-blur) * 2) hsl(var(--primary) / 0.8)',
      },
    },
  },
  plugins: [],
}