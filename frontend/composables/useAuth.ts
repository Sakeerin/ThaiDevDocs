export interface AuthUser {
  id: number
  name: string
  email: string
  avatar: string | null
  bio: string | null
  website: string | null
  github_username: string | null
  role: string
  contribution_points: number
  email_verified: boolean
  preferences: {
    theme: string
    font_size: string
    code_theme: string
    email_notifications: boolean
    weekly_digest: boolean
  } | null
  created_at?: string
}

export const useAuth = () => {
  const user = useState<AuthUser | null>('auth-user', () => null)
  const isInitialized = useState('auth-initialized', () => false)
  const token = useCookie('auth-token', { maxAge: 60 * 60 * 24 * 30 })
  const { $api } = useNuxtApp()

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAuthReady = computed(() => isInitialized.value)

  const fetchUser = async () => {
    if (!token.value) {
      user.value = null
      isInitialized.value = true
      return
    }

    try {
      const response = await $api<{ data: { user: AuthUser } }>('/auth/user')
      user.value = response.data.user
    } catch {
      token.value = null
      user.value = null
    } finally {
      isInitialized.value = true
    }
  }

  const initAuth = async () => {
    if (isInitialized.value) return
    await fetchUser()
  }

  const login = async (email: string, password: string, remember = false) => {
    const response = await $api<{ data: { token: string; user: AuthUser } }>('/auth/login', {
      method: 'POST',
      body: { email, password, remember },
    })

    token.value = response.data.token
    user.value = response.data.user
    isInitialized.value = true
  }

  const register = async (
    name: string,
    email: string,
    password: string,
    password_confirmation: string,
  ) => {
    const response = await $api<{ data: { token: string; user: AuthUser } }>('/auth/register', {
      method: 'POST',
      body: { name, email, password, password_confirmation },
    })

    token.value = response.data.token
    user.value = response.data.user
    isInitialized.value = true
  }

  const logout = async () => {
    try {
      await $api('/auth/logout', { method: 'POST' })
    } catch {
      // Ignore errors — clear local session regardless
    } finally {
      token.value = null
      user.value = null
      isInitialized.value = true
      await navigateTo('/')
    }
  }

  const updateProfile = async (data: Partial<AuthUser>) => {
    const response = await $api<{ data: { user: AuthUser } }>('/auth/user', {
      method: 'PATCH',
      body: data,
    })
    user.value = response.data.user
  }

  const forgotPassword = async (email: string) => {
    await $api('/auth/forgot-password', {
      method: 'POST',
      body: { email },
    })
  }

  const resetPassword = async (
    email: string,
    token: string,
    password: string,
    password_confirmation: string,
  ) => {
    await $api('/auth/reset-password', {
      method: 'POST',
      body: { email, token, password, password_confirmation },
    })
  }

  return {
    user: readonly(user),
    token: readonly(token),
    isAuthenticated,
    isAuthReady,
    isInitialized: readonly(isInitialized),
    login,
    register,
    logout,
    fetchUser,
    initAuth,
    updateProfile,
    forgotPassword,
    resetPassword,
  }
}
