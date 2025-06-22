import { defineNuxtConfig } from 'nuxt/config'
import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  app: {
    head: {
      title: 'ZoXcom',
      meta: [
        { name: 'description', content: 'Description de ton site ici' }
      ]
    }
  },
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  css: ['~/assets/css/main.css'],

  vite: {
    css: {
      devSourcemap: true,
    },
    plugins: [tailwindcss()],
  },

})