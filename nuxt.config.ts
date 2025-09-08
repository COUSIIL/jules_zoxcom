import { defineNuxtConfig } from 'nuxt/config'

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
  css: [
    '~/assets/css/reset.css',
    '~/assets/css/variables.css',
    '~/assets/css/layout.css',
    '~/assets/css/components.css',
    '~/assets/css/utilities.css',
    '~/assets/css/main.css' // Keep for font imports
  ],

  vite: {
    css: {
      devSourcemap: true,
    }
  },

})