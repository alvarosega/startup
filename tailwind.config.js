/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },
        card: {
          DEFAULT: 'hsl(var(--card))',
          foreground: 'hsl(var(--card-foreground))',
          border: 'hsl(var(--card-border))',
        },
        accent: 'hsl(var(--accent))', // Verde de ofertas independiente
        border: 'hsl(var(--border))',
        input: 'hsl(var(--input))',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      borderRadius: {
        lg: '12px',
        md: '8px',
        sm: '4px',
      },
      boxShadow: {
        // Efecto Apple (Sutil)
        'apple-soft': '0 8px 30px rgba(0, 0, 0, 0.04)',
        // Efecto F1 (Glow)
        'f1-glow': '0 0 15px -3px hsl(var(--primary) / 0.5), 0 0 6px -2px hsl(var(--primary) / 0.3)',
      },
    },
  },
  plugins: [],
}