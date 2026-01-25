<template>
  <header class="sticky top-0 z-50 w-full bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-800">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-500 flex items-center justify-center">
            <span class="text-white font-bold text-lg">ไ</span>
          </div>
          <span class="font-bold text-xl text-gray-900 dark:text-white">
            ThaiDevDocs
          </span>
        </NuxtLink>

        <!-- Search Bar -->
        <div class="hidden md:flex flex-1 max-w-xl mx-8">
          <button
            @click="openSearch"
            class="w-full flex items-center gap-3 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          >
            <MagnifyingGlassIcon class="w-5 h-5" />
            <span class="flex-1 text-left">ค้นหาเอกสาร...</span>
            <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 bg-white dark:bg-gray-900 rounded text-xs text-gray-500 border border-gray-300 dark:border-gray-600">
              <span>⌘</span>
              <span>K</span>
            </kbd>
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex items-center gap-4">
          <NuxtLink
            to="/docs"
            class="hidden md:block text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            เอกสาร
          </NuxtLink>
          <NuxtLink
            to="/learn"
            class="hidden md:block text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            เรียนรู้
          </NuxtLink>
          <NuxtLink
            to="/glossary"
            class="hidden md:block text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            คำศัพท์
          </NuxtLink>

          <!-- Theme Toggle -->
          <ThemeToggle />

          <!-- Mobile Menu Button -->
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            <Bars3Icon v-if="!isMobileMenuOpen" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
          </button>

          <!-- User Menu -->
          <div v-if="isAuthenticated" class="relative">
            <UserMenu />
          </div>
          <NuxtLink
            v-else
            to="/auth/login"
            class="btn-primary text-sm"
          >
            เข้าสู่ระบบ
          </NuxtLink>
        </nav>
      </div>

      <!-- Mobile Menu -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 -translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-1"
      >
        <div v-if="isMobileMenuOpen" class="md:hidden py-4 border-t border-gray-200 dark:border-gray-800">
          <nav class="flex flex-col gap-2">
            <NuxtLink to="/docs" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg">
              เอกสาร
            </NuxtLink>
            <NuxtLink to="/learn" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg">
              เรียนรู้
            </NuxtLink>
            <NuxtLink to="/glossary" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg">
              คำศัพท์
            </NuxtLink>
          </nav>
        </div>
      </Transition>
    </div>

    <!-- Search Modal -->
    <SearchModal v-model="isSearchOpen" />
  </header>
</template>

<script setup lang="ts">
import { MagnifyingGlassIcon, Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const isMobileMenuOpen = ref(false)
const isSearchOpen = ref(false)
const isAuthenticated = ref(false) // TODO: Connect to auth store

const openSearch = () => {
  isSearchOpen.value = true
}

// Keyboard shortcut for search
onMounted(() => {
  const handleKeydown = (e: KeyboardEvent) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault()
      openSearch()
    }
  }
  window.addEventListener('keydown', handleKeydown)
  onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
})
</script>
