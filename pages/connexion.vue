<template>
  <div class="flex items-center justify-center h-screen">
    <div class="w-full max-w-sm p-8 space-y-6 rounded-xl shadow-md bg-whitly dark:bg-darkly">
      <h2 class="text-2xl font-bold text-center">{{ t('connexion') }}</h2>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <!-- Champ username sécurisé -->
        <input 
          type="text" 
          v-model.trim="username" 
          :placeholder="t('user name')" 
          required
          maxlength="50"
          autocomplete="username"
          class="w-full px-4 py-2 border rounded-lg bg-transparent border-whity dark:border-darky focus:outline-none focus:ring-2 focus:ring-ioly"
        />

        <!-- Champ mot de passe avec toggle -->
        <div class="relative">
          <input
            :type="showPassword ? 'text' : 'password'"
            v-model.trim="password"
            :placeholder="t('password')"
            required
            minlength="6"
            autocomplete="current-password"
            class="w-full px-4 py-2 border rounded-lg bg-transparent border-whity dark:border-darky focus:outline-none focus:ring-2 focus:ring-ioly"
          />
          <button 
            type="button" 
            class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-600"
            @click="showPassword = !showPassword"
            :aria-label="showPassword ? t('hide password') : t('show password')"
            v-html="showPassword ? resizeSvg(icons['hide'], 18, 18) : resizeSvg(icons['view'], 18, 18)"
          >
          </button>
        </div>

        <button type="submit" :disabled="loading" class="w-full px-4 py-2 text-white transition-colors rounded-lg bg-ioly hover:bg-opacity-80 disabled:bg-gray-400 disabled:cursor-not-allowed">
          {{ loading ? t('loading...') : t('log in') }}
        </button>

        <p v-if="errorMessage" class="mt-2 text-red-500">{{ errorMessage }}</p>
      </form>
    </div>
  </div>
</template>
  
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const siteKey = '6LeAL7QrAAAAABSuiOr3Thi0S_c13dxTzt0R6FKT'
useHead({
  script: [
    {
      src: `https://www.google.com/recaptcha/api.js?render=${siteKey}`,
      async: true,
      defer: true,
    },
  ],
})

import icons from '~/public/icons.json'

const { t } = useLang()

const username = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)
const showPassword = ref(false)

const router = useRouter()

onMounted(() => {
  testLogin()
})

var resizeSvg = (svg, width, height) => {
  return svg
  .replace(/width="[^"]+"/, `width="${width}"`)
  .replace(/height="[^"]+"/, `height="${height}"`)
}

const testLogin = async () => {
  const stored = localStorage.getItem('auth')
  if (stored) {
    const data = JSON.parse(stored)
    if (data && data.user) {
      console.log('data.user: ', data.user, ' ', 'data: ', data)
      router.push({ path: '/team/' + data.user}).then(() => {
        router.go(0) // recharge la page
      })
    }
  }


}

const handleLogin = async () => {
  errorMessage.value = ''
  loading.value = true

  // 🛡️ Vérifications côté client
  if (!username.value || !password.value) {
    errorMessage.value = t('pleas fill all the inputs')
    loading.value = false
    return
  }

  try {
    // ⚡ Récupérer le token reCAPTCHA v3
    const token = await new Promise((resolve, reject) => {
      if (!window.grecaptcha) return reject(t('recaptcha not charged'))
      grecaptcha.ready(() => {
        grecaptcha.execute(siteKey, { action: 'login' }).then(resolve).catch(reject)
      })
    })

    // 🚀 Envoyer la requête avec le token
    const response = await fetch(
      'https://management.hoggari.com/backend/api.php?action=connexion',
      {
        method: 'POST',
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          username: username.value,
          password: password.value,
          recaptcha: token,
        }),
      }
    )

    if (!response.ok) {
      errorMessage.value = t('error server')
      return
    }

    const data = await response.json()
    
    const token2 = data.data['token']

    if (data.success) {
      const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=checkConnexion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: token2 }),
      })

      if (!response2.ok) throw new Error('Invalid response')

      const result = await response2.json()
      const data2 = { ...result.data, token: token2 }

      localStorage.setItem('auth', JSON.stringify(data2))

      router.push({ path: '/team/' + result.data.username }).then(() => {
        router.go(0) // recharge la page
      })
      
    } else {
      errorMessage.value = data.message || t('identitie not correct')
    }
  } catch (error) {
    errorMessage.value = t('error connexion')
  } finally {
    loading.value = false
  }
}
</script>
