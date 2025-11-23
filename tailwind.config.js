/**
 * Tailwind configuration for Jasamedika project
 * Adds CSS-variable-backed colors for primary and secondary so
 * classes like `bg-primary` or `text-primary` can be used via Tailwind.
 */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  'var(--color-primary-50)',
          100: 'var(--color-primary-100)',
          200: 'var(--color-primary-200)',
          300: 'var(--color-primary-300)',
          400: 'var(--color-primary-400)',
          500: 'var(--color-primary-500)',
          600: 'var(--color-primary-600)',
          700: 'var(--color-primary-700)',
          800: 'var(--color-primary-800)',
          DEFAULT: 'var(--color-primary-500)'
        },
        secondary: {
          DEFAULT: 'var(--color-secondary)',
          600: 'var(--color-secondary-600)'
        }
      },
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif']
      }
    }
  },
  plugins: []
};
