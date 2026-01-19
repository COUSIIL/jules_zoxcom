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

  postcss: {
    plugins: {
      "@tailwindcss/postcss": {},  // ✅ Nouveau plugin pour Tailwind v4
      autoprefixer: {}
    }
  },

  runtimeConfig: {
    public: {
      // Clé VAPID publique de votre config.php
      vapidPublicKey: 'BCcIPD0QlNkfi3Zaw93Sd0D7Y5WvZlLAlaDfsjppa3yeYkLo_f_t0p1dEPy-mgUYN3Yb_Fz8CegClBa8ymz_xeQ'
    }
  },


  vite: {
    css: {
      devSourcemap: true,
    },
    server: {
      proxy: {
        '/backend': {
          target: 'https://management.hoggari.com',
          changeOrigin: true
        }
      }
    }
  }
})
