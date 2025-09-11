import { ref } from 'vue'

const auth = ref(null) // Stocker l'utilisateur

export const useAuth = () => {
  // ➡️ Fonction pour se connecter
  const login = (authData) => {
    auth.value = authData
    localStorage.setItem('auth', JSON.stringify(authData));
  }

  // ➡️ Fonction pour récupérer l'utilisateur connecté
  const getauth = () => {
    if (!auth.value) {
      const storedUser = localStorage.getItem('auth');
      if (storedUser) {
        auth.value = JSON.parse(storedUser)
      }
    }
    return auth.value
  }

  // ➡️ Fonction pour vérifier si l'utilisateur est connecté
  const isAuthenticated = () => {
    return !!getauth()
  }

  // ➡️ Fonction pour se déconnecter
  const logout = () => {
    auth.value = null
    localStorage.removeItem('auth')
  }

  return {
    auth,
    login,
    getauth,
    isAuthenticated,
    logout
  }
}
