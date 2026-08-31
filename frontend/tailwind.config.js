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
          50: '#f0f4f8',
          100: '#d9e2ec',
          200: '#bcccdc',
          300: '#9fb3c8',
          400: '#627d98',
          500: '#486581',
          600: '#004c8f', // Institutional Federal / NHS Blue
          700: '#0a3663',
          800: '#06284a',
          900: '#001e3d',
          950: '#001326',
        },
        gov: {
          navy: '#0a2540',
          darkBlue: '#112e51',
          blue: '#005ea2',
          lightBlue: '#d9e8f6',
          border: '#d1d5db',
          borderDark: '#9ca3af',
          bg: '#f8fafc',
          card: '#ffffff',
          text: '#111827',
          textMuted: '#4b5563',
          red: '#b91c1c',
          green: '#15803d',
          amber: '#b45309',
        },
        medicon: {
          dark: '#0f172a',
          surface: '#1e293b',
          border: '#cbd5e1',
          accent: '#005ea2',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
      },
      boxShadow: {
        'crisp': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'panel': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)',
      },
      borderRadius: {
        'none': '0px',
        'sm': '2px',
        'DEFAULT': '4px',
        'md': '6px',
        'lg': '8px',
        'xl': '8px',
        '2xl': '8px',
        '3xl': '8px',
        'full': '9999px',
      }
    },
  },
  plugins: [],
}
