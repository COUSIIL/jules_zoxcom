/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./error.vue",
  ],
  theme: {
    extend: {
      colors: {
        'whitly': '#fefaff',
        'wagly': '#f3edf7',
        'whiby': '#e9f4f2',
        'whity': '#f8eeff',
        'darky': '#0e0812',
        'darkly': '#2a222f',
        'darkow': '#131212',
        'darkiw': '#311a2f',
        'garry': '#69626e',
        'gorry': '#8c8391',
        'yelly': '#ffd103',
        'rangy': '#f39204',
        'hoggari': '#ff5800',
        'rady': '#ff5555',
        'greny': '#5bac1c',
        'ioly': '#aa72c6',
        'tioly': '#aa72c660',
        'zioly1': '#C9B1CC',
        'zioly2': '#7D698E',
        'zioly3': '#002654',
        'zioly4': '#2D0460',
        'whizy': '#ebdcee',
      }
    },
  },
  plugins: [],
}
