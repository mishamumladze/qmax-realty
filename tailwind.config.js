/** @type {import('tailwindcss').Config} */
module.exports = {
  // ─── Purge sources ────────────────────────────────────────────────────────
  // Tailwind scans these files for class names and removes everything else.
  // Add any new PHP or JS files here if they introduce new Tailwind classes.
  content: [
      './*.php',
      './includes/*.php',
      './config/*.php',
      './js/*.js',
      './listings/**/*.php',
      './booking/includes/*.php',
      './admin/*.php',       // ← add this
  ],

  theme: {
    extend: {
      // Keep custom colours consistent with the emerald brand
      colors: {
        brand: {
          DEFAULT: '#047857', // emerald-600
          hover:   '#059669', // emerald-700
          light:   '#ecfdf5', // emerald-50
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },

  plugins: [],
};