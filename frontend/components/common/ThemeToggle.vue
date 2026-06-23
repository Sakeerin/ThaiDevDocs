<template>
  <button
    type="button"
    class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
    :aria-label="isDark ? 'เปลี่ยนเป็นโหมดสว่าง' : 'เปลี่ยนเป็นโหมดมืด'"
    @click="toggleTheme"
  >
    <SunIcon v-if="isDark" class="w-5 h-5" />
    <MoonIcon v-else class="w-5 h-5" />
  </button>
</template>

<script setup lang="ts">
import { SunIcon, MoonIcon } from '@heroicons/vue/24/outline'

const colorMode = useColorMode()
const preferencesStore = usePreferencesStore()

const isDark = computed(() => colorMode.value === 'dark')

const toggleTheme = async () => {
  const nextTheme = colorMode.value === 'dark' ? 'light' : 'dark'
  preferencesStore.setTheme(nextTheme)

  const { isAuthenticated } = useAuth()
  if (isAuthenticated.value) {
    try {
      await preferencesStore.saveToApi()
    } catch {
      // Theme still applied locally
    }
  }
}
</script>
