const colors = require('tailwindcss/colors')
const plugin = require('tailwindcss/plugin');
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
  content: [
    './app/Filament/**/*.php',
    './resources/**/*.blade.php',
    './vendor/filament/**/*.blade.php',
    "./vendor/suleymanozev/**/*.blade.php", // Add this line

  ],
  safelist: [
        'border-green-600',
        'border-l-2',
        'border-orange-600',
        'dark:border-orange-300',
        'opacity-30',
    ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        danger: colors.red,
        primary: colors.blue,
        secondary: colors.slate,
        success: colors.green,
        warning: colors.amber,
        info: colors.purple,
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    plugin(function ({ addComponents }) {
      addComponents({
        // '.filament-button, ': {
        //     borderRadius: '2px !important'
        // },
        // '.filament-sidebar-item > a' :{
        //     borderRadius: '0 !important'
        // },
        '.filament-tables-pagination div': {
          borderRadius: '0 !important'
        },
        '.ring-2': {
          borderRadius: '0 !important'
        },
        // 'input,select' :{
        //     borderRadius: '0 !important'
        // }
      })
    })
  ],
}
