import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
  bio: string | null
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
}

interface AuthState {
  user: User | null
  token: string | null
  isLoading: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    isLoading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.role === 'admin' || state.user?.role === 'super_admin',
    isEditor: (state) => ['editor', 'admin', 'super_admin'].includes(state.user?.role || ''),
  },

  actions: {
    async init() {
      const tokenCookie = useCookie('auth-token')
      
      if (tokenCookie.value) {
        this.token = tokenCookie.value
        await this.fetchUser()
      }
    },

    async login(email: string, password: string, remember: boolean = false) {
      const api = useApi()
      this.isLoading = true

      try {
        const response = await api.post<any>('/auth/login', {
          email,
          password,
          remember,
        })

        this.token = response.data.token
        this.user = response.data.user

        // Store token in cookie
        const tokenCookie = useCookie('auth-token', {
          maxAge: remember ? 60 * 60 * 24 * 30 : 60 * 60 * 24 * 7, // 30 days or 7 days
        })
        tokenCookie.value = this.token

        return response
      } finally {
        this.isLoading = false
      }
    },

    async register(name: string, email: string, password: string, password_confirmation: string) {
      const api = useApi()
      this.isLoading = true

      try {
        const response = await api.post<any>('/auth/register', {
          name,
          email,
          password,
          password_confirmation,
        })

        this.token = response.data.token
        this.user = response.data.user

        // Store token in cookie
        const tokenCookie = useCookie('auth-token', {
          maxAge: 60 * 60 * 24 * 7, // 7 days
        })
        tokenCookie.value = this.token

        return response
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      const api = useApi()
      
      try {
        await api.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      }

      this.user = null
      this.token = null

      const tokenCookie = useCookie('auth-token')
      tokenCookie.value = null

      navigateTo('/')
    },

    async fetchUser() {
      const api = useApi()
      
      try {
        const response = await api.get<any>('/auth/user')
        this.user = response.data.user
      } catch (error) {
        console.error('Failed to fetch user:', error)
        this.logout()
      }
    },

    async updateProfile(data: Partial<User>) {
      const api = useApi()
      
      try {
        const response = await api.patch<any>('/auth/user', data)
        this.user = response.data.user
        return response
      } catch (error) {
        throw error
      }
    },

    async updatePreferences(preferences: any) {
      const api = useApi()
      
      try {
        const response = await api.patch<any>('/user/preferences', preferences)
        if (this.user) {
          this.user.preferences = response.data.preferences
        }
        return response
      } catch (error) {
        throw error
      }
    },
  },
})
