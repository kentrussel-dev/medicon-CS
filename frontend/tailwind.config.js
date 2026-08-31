/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f0f6ff',
          100: '#e0edff',
          200: '#c7ddff',
          300: '#9ec4ff',
          400: '#6ea3ff',
          500: '#3b82f6',
          600: '#1d6bf3', // Primary vibrant blue from design
          700: '#1554c0',
          800: '#17479b',
          900: '#183d7a',
          950: '#10264d',
        },
        docwise: {
          blue: '#1d6bf3',
          lightBlue: '#f5f9ff',
          sky: '#38bdf8',
          cardBorder: '#e2e8f0',
          softRose: '#fff1f5',
          roseBorder: '#fbcfe8',
          softOrange: '#fff7ed',
          softGreen: '#f0fdf4',
          darkText: '#0f172a',
          mutedText: '#64748b',
        },
        medicon: {
          dark: '#0f172a',
          surface: '#1e293b',
          border: '#334155',
          accent: '#1d6bf3',
        }
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        'card': '0 4px 20px -2px rgba(29, 107, 243, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04)',
        'hero-card': '0 20px 40px -15px rgba(29, 107, 243, 0.15), 0 0 1px 1px rgba(29, 107, 243, 0.05)',
      }
    },
  },
  plugins: [],
}
