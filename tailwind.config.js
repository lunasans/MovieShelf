/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        shelf: {
          bg: '#0a0a0f',
          card: '#12121a',
          border: '#1e1e2e',
          accent: '#3b82f6',
        },
      },
    },
  },
  plugins: [],
}
