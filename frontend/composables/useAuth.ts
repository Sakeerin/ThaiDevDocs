import type { User } from '~/types'

export const useAuth = () => {
  const user = useState<User | null>('auth-user', () => null)
  const token = useCookie('auth-token', { maxAge: 60 * 60 * 24 * 30 }) // 30 days
  const { $api } = useNuxtApp()

  const isAuthenticated = computed(() => !!user.value)

  const fetchUser = async () => {
    if (!token.value) {
      user.value = null
      return
    }

    try {
      const response = await $api<{ data: User }>('/user')
      user.value = response.data
    } catch (e) {
      token.value = null
      user.value = null
    }
  }

  const login = async (email: string, password: string) => {
    const response = await $api<{ data: { token: string; user: User } }>('/auth/login', {
      method: 'POST',
      body: { email, password },
    })

    token.value = response.data.token
    user.value = response.data.user
  }

  const register = async (name: string, email: string, password: string, password_confirmation: string) => {
    const response = await $api<{ data: { token: string; user: User } }>('/auth/register', {
      method: 'POST',
      body: { name, email, password, password_confirmation },
    })

    token.value = response.data.token
    user.value = response.data.user
  }

  const logout = async () => {
    try {
      await $api('/auth/logout', { method: 'POST' })
    } catch (e) {
      // Ignore errors
    } finally {
      token.value = null
      user.value = null
      navigateTo('/auth/login')
    }
  }

  const updateProfile = async (data: Partial<User>) => {
    const response = await $api<{ data: User }>('/user', {
      method: 'PUT',
      body: data,
    })
    user.value = response.data
  }

  // Initialize auth state on app load
  if (token.value && !user.value) {
    fetchUser()
  }

  return {
    user: readonly(user),
    token: readonly(token),
    isAuthenticated,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
  }
}
