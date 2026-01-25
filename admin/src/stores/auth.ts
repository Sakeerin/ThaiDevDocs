import { defineStore } from 'pinia'
import api from '@/services/api'

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
  role: string
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
    isAdmin: (state) => ['admin', 'super_admin'].includes(state.user?.role || ''),
    isEditor: (state) => ['editor', 'admin', 'super_admin'].includes(state.user?.role || ''),
  },

  actions: {
    async init() {
      const storedToken = localStorage.getItem('admin-token')
      
      if (storedToken) {
        this.token = storedToken
        api.setToken(storedToken)
        await this.fetchUser()
      }
    },

    async login(email: string, password: string) {
      this.isLoading = true

      try {
        const response = await api.post<any>('/auth/login', { email, password })
        
        this.token = response.data.token
        this.user = response.data.user

        localStorage.setItem('admin-token', this.token!)
        api.setToken(this.token!)

        return response
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      }

      this.user = null
      this.token = null
      localStorage.removeItem('admin-token')
      api.setToken('')
    },

    async fetchUser() {
      try {
        const response = await api.get<any>('/auth/user')
        this.user = response.data.user
        
        // Check if user has admin access
        if (!['editor', 'admin', 'super_admin'].includes(this.user?.role || '')) {
          throw new Error('Access denied')
        }
      } catch (error) {
        console.error('Failed to fetch user:', error)
        await this.logout()
      }
    },
  },

  persist: {
    key: 'admin-auth',
    paths: ['token'],
  },
})
