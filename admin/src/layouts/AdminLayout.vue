<template>
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-200 ease-in-out"
      :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    >
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center gap-3 h-16 px-4 border-b border-gray-200 dark:border-gray-700">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-500 flex items-center justify-center">
            <span class="text-white font-bold text-lg">ไ</span>
          </div>
          <span class="font-bold text-lg text-gray-900 dark:text-white">Admin Panel</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
          <router-link
            v-for="item in navigation"
            :key="item.name"
            :to="item.path"
            class="sidebar-link"
            :class="{ active: isActive(item.path) }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            {{ item.name }}
          </router-link>
        </nav>

        <!-- User -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
              <span class="text-primary-600 dark:text-primary-400 font-medium">
                {{ authStore.user?.name?.charAt(0) || 'A' }}
              </span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ authStore.user?.name }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ authStore.user?.email }}
              </p>
            </div>
            <button @click="logout" class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
              <ArrowRightOnRectangleIcon class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-gray-900/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64">
      <!-- Header -->
      <header class="sticky top-0 z-10 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center px-4 gap-4">
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 lg:hidden"
        >
          <Bars3Icon class="w-6 h-6" />
        </button>

        <div class="flex-1">
          <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ pageTitle }}
          </h1>
        </div>

        <!-- Theme Toggle -->
        <button
          @click="toggleTheme"
          class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
          <SunIcon v-if="isDark" class="w-5 h-5" />
          <MoonIcon v-else class="w-5 h-5" />
        </button>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Bars3Icon,
  HomeIcon,
  DocumentTextIcon,
  FolderIcon,
  TagIcon,
  UsersIcon,
  ChatBubbleLeftIcon,
  PhotoIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
  SunIcon,
  MoonIcon,
  RectangleStackIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const sidebarOpen = ref(false)
const isDark = ref(document.documentElement.classList.contains('dark'))

const navigation = [
  { name: 'แดชบอร์ด', path: '/', icon: HomeIcon },
  { name: 'บทความ', path: '/articles', icon: DocumentTextIcon },
  { name: 'หมวดหมู่', path: '/categories', icon: FolderIcon },
  { name: 'หัวข้อ', path: '/topics', icon: RectangleStackIcon },
  { name: 'แท็ก', path: '/tags', icon: TagIcon },
  { name: 'ผู้ใช้', path: '/users', icon: UsersIcon },
  { name: 'ความคิดเห็น', path: '/comments', icon: ChatBubbleLeftIcon },
  { name: 'สื่อ', path: '/media', icon: PhotoIcon },
  { name: 'วิเคราะห์', path: '/analytics', icon: ChartBarIcon },
  { name: 'ตั้งค่า', path: '/settings', icon: Cog6ToothIcon },
]

const pageTitle = computed(() => {
  const current = navigation.find(item => {
    if (item.path === '/') return route.path === '/'
    return route.path.startsWith(item.path)
  })
  return current?.name || 'Admin'
})

const isActive = (path: string) => {
  if (path === '/') return route.path === '/'
  return route.path.startsWith(path)
}

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
