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
        screens: {
          sm: '640px',
          md: '768px',
          lg: '1024px',
          xl: '1280px',
          '2xl': '1400px',
        },
      },
      extend: {
        colors: {
          border: 'hsl(var(--border))',
          input: 'hsl(var(--input))',
          ring: 'hsl(var(--ring))',
          background: 'hsl(var(--background))',
          foreground: 'hsl(var(--foreground))',
          primary: {
            DEFAULT: 'hsl(var(--primary))',
            foreground: 'hsl(var(--primary-foreground))',
          },
          secondary: {
            DEFAULT: 'hsl(var(--secondary))',
            foreground: 'hsl(var(--secondary-foreground))',
          },
          destructive: {
            DEFAULT: 'hsl(var(--destructive))',
            foreground: 'hsl(var(--destructive-foreground))',
          },
          muted: {
            DEFAULT: 'hsl(var(--muted))',
            foreground: 'hsl(var(--muted-foreground))',
          },
          accent: {
            DEFAULT: 'hsl(var(--accent))',
            foreground: 'hsl(var(--accent-foreground))',
          },
          popover: {
            DEFAULT: 'hsl(var(--popover))',
            foreground: 'hsl(var(--popover-foreground))',
          },
          card: {
            DEFAULT: 'hsl(var(--card))',
            foreground: 'hsl(var(--card-foreground))',
          },
        },
        borderRadius: {
          lg: 'var(--radius-lg)',
          md: 'var(--radius-md)',
          sm: 'var(--radius-sm)',
          xl: 'var(--radius-xl)',
        },
        boxShadow: {
          sm: 'var(--shadow-sm)',
          md: 'var(--shadow-md)',
          lg: 'var(--shadow-lg)',
          xl: 'var(--shadow-xl)',
        },
        animation: {
          'fade-in': 'fadeIn var(--duration-fast) var(--ease-smooth)',
          'fade-out': 'fadeOut var(--duration-fast) var(--ease-smooth)',
          'slide-up': 'slideUp var(--duration-base) var(--ease-elastic)',
          'slide-down': 'slideDown var(--duration-base) var(--ease-elastic)',
          'scale-in': 'scaleIn var(--duration-fast) var(--ease-elastic)',
          'scale-out': 'scaleOut var(--duration-fast) var(--ease-elastic)',
          'spin-slow': 'spin 3s linear infinite',
          'pulse-subtle': 'pulse 2s var(--ease-smooth) infinite',
          'shimmer': 'shimmer 2s linear infinite',
        },
        transitionTimingFunction: {
          'elastic': 'var(--ease-elastic)',
          'exponential': 'var(--ease-exponential)',
          'smooth': 'var(--ease-smooth)',
        },
        transitionDuration: {
          'instant': 'var(--duration-instant)',
          'fast': 'var(--duration-fast)',
          'base': 'var(--duration-base)',
          'slow': 'var(--duration-slow)',
        },
        fontFamily: {
          sans: ['Inter', 'system-ui', 'sans-serif'],
          display: ['Plus Jakarta Sans', 'sans-serif'],
          mono: ['JetBrains Mono', 'monospace'],
        },
        keyframes: {
          fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
          fadeOut: { '0%': { opacity: '1' }, '100%': { opacity: '0' } },
          slideUp: { '0%': { transform: 'translateY(10px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
          slideDown: { '0%': { transform: 'translateY(-10px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
          scaleIn: { '0%': { transform: 'scale(0.95)', opacity: '0' }, '100%': { transform: 'scale(1)', opacity: '1' } },
          scaleOut: { '0%': { transform: 'scale(1)', opacity: '1' }, '100%': { transform: 'scale(0.95)', opacity: '0' } },
          shimmer: { '0%': { backgroundPosition: '-200% center' }, '100%': { backgroundPosition: '200% center' } },
        },
      },
    },
    plugins: [
      // Los plugins se han removido temporalmente
      // Puedes instalarlos después si los necesitas
    ],
  }