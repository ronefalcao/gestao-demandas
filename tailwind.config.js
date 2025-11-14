const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './app/View/**/*.php',
    './vendor/filament/**/*.blade.php',
  ],
  safelist: ['bg-primary', 'hover:bg-primary/90', 'focus:ring-primary/60', 'shadow-primary/40', 'text-primary'],
  theme: {
    extend: {
      colors: {
        primary: '#0F6EBF',
        secondary: '#08253D',
      },
      fontFamily: {
        sans: ['"Open Sans"', ...defaultTheme.fontFamily.sans],
        heading: ['"Roboto"', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
