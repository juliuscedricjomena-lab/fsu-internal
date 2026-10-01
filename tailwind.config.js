/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./resources/**/*.{js,ts,jsx,tsx,vue}",
  ],
  theme: {
    extend: {
      colors: {
        fsu: {
          primary: '#003a70',
          secondary: '#0066cc',
          accent: '#ff6b35',
        }
      }
    },
  },
  plugins: [],
}
