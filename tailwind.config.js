/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.php",
    "./src/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50:  '#fff0f4',
          100: '#ffd6e2',
          200: '#ffadc5',
          600: '#ff0a52',
          700: '#d40047',
        },
      },
      fontFamily: {
        sans:  ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        serif: ['Roboto Slab', 'ui-serif', 'Georgia', 'serif'],
      },
    },
  },
  plugins: [],
}
