export default defineNuxtRouteMiddleware(async () => {
  const { isAuthenticated, initAuth } = useAuth()

  await initAuth()

  if (isAuthenticated.value) {
    return navigateTo('/')
  }
})
