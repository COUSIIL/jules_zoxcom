export default defineNuxtRouteMiddleware((to, from) => {
  // Client-side only check to avoid SSR issues with localStorage if not handled gracefully
  if (process.server) return

  const { isAuthenticated, hasPermission } = useAuth()

  if (to.meta.permission) {
    if (!isAuthenticated()) {
      return navigateTo('/connexion')
    }
    if (!hasPermission(to.meta.permission)) {
      console.warn('Access denied. Missing permission:', to.meta.permission)
      return navigateTo('/') // Redirect to home/dashboard
    }
  }
})
