/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}", // Esto le dice que busque clases en tus componentes Vue
  ],
  theme: {
    extend: {
      colors: {
        'arenas-red': '#dc2626',   //
        'arenas-black': '#000000', //
      }
    },
  },
  plugins: [],
}