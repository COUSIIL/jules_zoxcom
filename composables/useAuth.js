import { ref } from 'vue'

const user = ref(null) // Stocker l'utilisateur

export const useAuth = () => {
  // ➡️ Fonction pour se connecter
  const login = (userData) => {
    user.value = userData
    localStorage.setItem('user', JSON.stringify(userData));
  }

  // ➡️ Fonction pour récupérer l'utilisateur connecté
  const getUser = () => {
    if (!user.value) {
      const storedUser = localStorage.getItem('user');
      if (storedUser) {
        user.value = JSON.parse(storedUser)
      }
    }
    return user.value
  }

  // ➡️ Fonction pour vérifier si l'utilisateur est connecté
  const isAuthenticated = () => {
    return !!getUser()
  }

  // ➡️ Fonction pour se déconnecter
  const logout = () => {
    user.value = null
    localStorage.removeItem('user')
  }

  return {
    user,
    login,
    getUser,
    isAuthenticated,
    logout
  }
}
