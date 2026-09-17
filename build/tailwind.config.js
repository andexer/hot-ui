/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './app/Views/**/*.php',
    './app/Views/**/*.html',
    './app/Components/**/*.php',
    './resources/views/**/*.php',
    './resources/views/**/*.html',
  ],
  theme: {
    extend: {
      // Hot-UI theme tokens are loaded from hot-ui.css via CSS custom properties
      // No need to duplicate them here
    },
  },
  plugins: [
    // Add Tailwind v4 plugins here if needed
    // @tailwindcss/forms,
    // @tailwindcss/typography,
  ],
};
