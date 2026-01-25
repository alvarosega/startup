/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './storage/framework/views/*.php',
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
        // ===== COLORES DE MARCA (white-labeling) =====
        primary: {
          DEFAULT: 'hsl(var(--primary) / <alpha-value>)',
          foreground: 'hsl(var(--primary-foreground) / <alpha-value>)',
          light: 'hsl(var(--primary-light) / <alpha-value>)',
          dark: 'hsl(var(--primary-dark) / <alpha-value>)',
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary) / <alpha-value>)',
          foreground: 'hsl(var(--secondary-foreground) / <alpha-value>)',
        },
        accent: {
          DEFAULT: 'hsl(var(--accent) / <alpha-value>)',
          foreground: 'hsl(var(--accent-foreground) / <alpha-value>)',
        },
        
        // ===== COLORES SEMÁNTICOS (relativos a marca) =====
        success: {
          DEFAULT: 'hsl(var(--success) / <alpha-value>)',
          foreground: 'hsl(var(--success-foreground) / <alpha-value>)',
        },
        warning: {
          DEFAULT: 'hsl(var(--warning) / <alpha-value>)',
          foreground: 'hsl(var(--warning-foreground) / <alpha-value>)',
        },
        error: {
          DEFAULT: 'hsl(var(--error) / <alpha-value>)',
          foreground: 'hsl(var(--error-foreground) / <alpha-value>)',
        },
        info: {
          DEFAULT: 'hsl(var(--info) / <alpha-value>)',
          foreground: 'hsl(var(--info-foreground) / <alpha-value>)',
        },
        
        // ===== COLORES DEL SISTEMA =====
        background: 'hsl(var(--background) / <alpha-value>)',
        foreground: 'hsl(var(--foreground) / <alpha-value>)',
        card: {
          DEFAULT: 'hsl(var(--card) / <alpha-value>)',
          foreground: 'hsl(var(--card-foreground) / <alpha-value>)',
        },
        popover: {
          DEFAULT: 'hsl(var(--popover) / <alpha-value>)',
          foreground: 'hsl(var(--popover-foreground) / <alpha-value>)',
        },
        muted: {
          DEFAULT: 'hsl(var(--muted) / <alpha-value>)',
          foreground: 'hsl(var(--muted-foreground) / <alpha-value>)',
        },
        border: 'hsl(var(--border) / <alpha-value>)',
        input: 'hsl(var(--input) / <alpha-value>)',
        ring: 'hsl(var(--ring) / <alpha-value>)',
      },
      
      // ===== SISTEMA DE BORDER RADIUS =====
      borderRadius: {
        sm: 'var(--radius-sm)',
        md: 'var(--radius-md)',
        lg: 'var(--radius-lg)',
        xl: 'var(--radius-xl)',
        full: 'var(--radius-full)',
      },
      
      // ===== SISTEMA DE SOMBRAS =====
      boxShadow: {
        sm: 'var(--shadow-sm)',
        md: 'var(--shadow-md)',
        lg: 'var(--shadow-lg)',
        xl: 'var(--shadow-xl)',
        inner: 'var(--shadow-inner)',
      },
      
      // ===== ANIMACIONES Y TRANSICIONES =====
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
        'bounce-subtle': 'bounceSubte 1s infinite',
        'ping-subtle': 'pingSubte 2s cubic-bezier(0, 0, 0.2, 1) infinite',
      },
      
      transitionTimingFunction: {
        'elastic': 'var(--ease-elastic)',
        'smooth': 'var(--ease-smooth)',
      },
      
      transitionDuration: {
        'instant': 'var(--duration-instant)',
        'fast': 'var(--duration-fast)',
        'base': 'var(--duration-base)',
        'slow': 'var(--duration-slow)',
      },
      
      // ===== TIPOGRAFÍA =====
      fontFamily: {
        sans: 'var(--font-sans)',
        display: 'var(--font-display)',
        mono: 'var(--font-mono)',
      },
      
      // ===== ESPACIADO SISTEMÁTICO =====
      spacing: {
        '0': 'var(--space-0)',
        '1': 'var(--space-1)',
        '2': 'var(--space-2)',
        '3': 'var(--space-3)',
        '4': 'var(--space-4)',
        '6': 'var(--space-6)',
        '8': 'var(--space-8)',
        '12': 'var(--space-12)',
        '16': 'var(--space-16)',
        '20': 'var(--space-20)',
        '24': 'var(--space-24)',
      },
      
      // ===== UTILIDADES PARA WHITE-LABELING =====
      backgroundImage: {
        'brand-gradient': 'linear-gradient(to right, hsl(var(--primary)), hsl(var(--secondary)))',
        'brand-gradient-diagonal': 'linear-gradient(135deg, hsl(var(--primary)), hsl(var(--accent)))',
        'brand-gradient-vertical': 'linear-gradient(to bottom, hsl(var(--primary)), hsl(var(--secondary)))',
        'brand-radial': 'radial-gradient(circle, hsl(var(--primary)), hsl(var(--secondary)))',
      },
      
      // ===== KEYFRAMES PARA ANIMACIONES =====
      keyframes: {
        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
        fadeOut: { '0%': { opacity: '1' }, '100%': { opacity: '0' } },
        slideUp: { '0%': { transform: 'translateY(10px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
        slideDown: { '0%': { transform: 'translateY(-10px)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
        scaleIn: { '0%': { transform: 'scale(0.95)', opacity: '0' }, '100%': { transform: 'scale(1)', opacity: '1' } },
        scaleOut: { '0%': { transform: 'scale(1)', opacity: '1' }, '100%': { transform: 'scale(0.95)', opacity: '0' } },
        shimmer: { '0%': { backgroundPosition: '-200% center' }, '100%': { backgroundPosition: '200% center' } },
        bounceSubte: {
          '0%, 100%': { transform: 'translateY(-5%)', animationTimingFunction: 'cubic-bezier(0.8,0,1,1)' },
          '50%': { transform: 'none', animationTimingFunction: 'cubic-bezier(0,0,0.2,1)' },
        },
        pingSubte: {
          '75%, 100%': { transform: 'scale(1.5)', opacity: '0' },
        },
      },
      
      // ===== UTILIDADES EXTRA =====
      opacity: {
        '15': '0.15',
        '35': '0.35',
        '65': '0.65',
        '85': '0.85',
      },
      
      zIndex: {
        '60': '60',
        '70': '70',
        '80': '80',
        '90': '90',
        '100': '100',
      },
    },
  },
  plugins: [],
  safelist: [
    // Clases que Tailwind no puede detectar pero necesitas
    'brand-bolivia',
    'brand-peru',
    'brand-chile',
    'brand-argentina',
    'brand-vibrant',
    'brand-subtle',
    'brand-light',
    'brand-dark',
    // Gradientes de marca
    'bg-brand-gradient',
    'bg-brand-gradient-diagonal',
    'bg-brand-gradient-vertical',
    'bg-brand-radial',
  ],
}