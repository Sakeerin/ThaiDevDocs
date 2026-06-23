import { defineStore } from 'pinia'

export type FontSizePreference = 'small' | 'medium' | 'large'
export type ThemePreference = 'light' | 'dark' | 'system'

export interface ApiPreferences {
  theme: ThemePreference
  font_size: FontSizePreference
  code_theme: string
  email_notifications: boolean
  weekly_digest: boolean
}

export interface LocalPreferences {
  save_reading_history: boolean
  remember_scroll_position: boolean
  show_toc: boolean
  show_line_numbers: boolean
  preferred_language: string
}

const LOCAL_KEY = 'thaiddevdocs-local-preferences'

const defaultApiPreferences = (): ApiPreferences => ({
  theme: 'system',
  font_size: 'medium',
  code_theme: 'github-dark',
  email_notifications: true,
  weekly_digest: true,
})

const defaultLocalPreferences = (): LocalPreferences => ({
  save_reading_history: true,
  remember_scroll_position: true,
  show_toc: true,
  show_line_numbers: true,
  preferred_language: 'th',
})

export const usePreferencesStore = defineStore('preferences', {
  state: () => ({
    api: defaultApiPreferences(),
    local: defaultLocalPreferences(),
    isLoaded: false,
    isSaving: false,
  }),

  getters: {
    theme: (state) => state.api.theme,
    fontSizePx: (state) => {
      const map: Record<FontSizePreference, number> = {
        small: 14,
        medium: 16,
        large: 20,
      }
      return map[state.api.font_size] || 16
    },
  },

  actions: {
    loadLocalFromStorage() {
      if (!import.meta.client) return

      try {
        const stored = localStorage.getItem(LOCAL_KEY)
        if (stored) {
          this.local = { ...defaultLocalPreferences(), ...JSON.parse(stored) }
        }
      } catch {
        this.local = defaultLocalPreferences()
      }
    },

    saveLocalToStorage() {
      if (!import.meta.client) return
      localStorage.setItem(LOCAL_KEY, JSON.stringify(this.local))
    },

    applyTheme() {
      if (!import.meta.client) return
      const colorMode = useColorMode()
      colorMode.preference = this.api.theme
    },

    applyFontSize() {
      if (!import.meta.client) return
      document.documentElement.style.setProperty('--content-font-size', `${this.fontSizePx}px`)
    },

    async fetchFromApi() {
      const { $api } = useNuxtApp()
      const { isAuthenticated } = useAuth()

      this.loadLocalFromStorage()

      if (!isAuthenticated.value) {
        this.applyTheme()
        this.applyFontSize()
        this.isLoaded = true
        return
      }

      try {
        const response = await $api<{ data: { preferences: ApiPreferences } }>('/user/preferences')
        this.api = { ...defaultApiPreferences(), ...response.data.preferences }
      } catch {
        // keep defaults
      } finally {
        this.applyTheme()
        this.applyFontSize()
        this.isLoaded = true
      }
    },

    setTheme(theme: ThemePreference) {
      this.api.theme = theme
      this.applyTheme()
    },

    setFontSizeFromPx(px: number) {
      if (px <= 14) this.api.font_size = 'small'
      else if (px >= 20) this.api.font_size = 'large'
      else this.api.font_size = 'medium'
      this.applyFontSize()
    },

    async saveToApi() {
      const { $api } = useNuxtApp()
      this.isSaving = true

      try {
        const response = await $api<{ data: { preferences: ApiPreferences } }>('/user/preferences', {
          method: 'PATCH',
          body: this.api,
        })
        this.api = { ...defaultApiPreferences(), ...response.data.preferences }
        this.saveLocalToStorage()
        this.applyTheme()
        this.applyFontSize()
      } finally {
        this.isSaving = false
      }
    },
  },
})
