<template>
  <Menu as="div" class="relative">
    <MenuButton class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
      <img
        v-if="user?.avatar"
        :src="user.avatar"
        :alt="user.name"
        class="w-8 h-8 rounded-full"
      />
      <div v-else class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
        <span class="text-primary-600 dark:text-primary-400 font-medium text-sm">
          {{ user?.name?.charAt(0) || 'U' }}
        </span>
      </div>
    </MenuButton>

    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <MenuItems class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white dark:bg-gray-900 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-800 focus:outline-none divide-y divide-gray-100 dark:divide-gray-800">
        <!-- User Info -->
        <div class="px-4 py-3">
          <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
          <p class="text-sm text-gray-500 truncate">{{ user?.email }}</p>
        </div>

        <!-- Links -->
        <div class="py-1">
          <MenuItem v-slot="{ active }">
            <NuxtLink
              to="/user/profile"
              :class="[
                active ? 'bg-gray-50 dark:bg-gray-800' : '',
                'flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200'
              ]"
            >
              <UserIcon class="w-5 h-5 text-gray-400" />
              โปรไฟล์
            </NuxtLink>
          </MenuItem>
          <MenuItem v-slot="{ active }">
            <NuxtLink
              to="/user/bookmarks"
              :class="[
                active ? 'bg-gray-50 dark:bg-gray-800' : '',
                'flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200'
              ]"
            >
              <BookmarkIcon class="w-5 h-5 text-gray-400" />
              บุ๊คมาร์ค
            </NuxtLink>
          </MenuItem>
          <MenuItem v-slot="{ active }">
            <NuxtLink
              to="/user/collections"
              :class="[
                active ? 'bg-gray-50 dark:bg-gray-800' : '',
                'flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200'
              ]"
            >
              <FolderIcon class="w-5 h-5 text-gray-400" />
              คอลเลกชัน
            </NuxtLink>
          </MenuItem>
          <MenuItem v-slot="{ active }">
            <NuxtLink
              to="/user/settings"
              :class="[
                active ? 'bg-gray-50 dark:bg-gray-800' : '',
                'flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200'
              ]"
            >
              <Cog6ToothIcon class="w-5 h-5 text-gray-400" />
              ตั้งค่า
            </NuxtLink>
          </MenuItem>
        </div>

        <!-- Logout -->
        <div class="py-1">
          <MenuItem v-slot="{ active }">
            <button
              @click="logout"
              :class="[
                active ? 'bg-gray-50 dark:bg-gray-800' : '',
                'flex w-full items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200'
              ]"
            >
              <ArrowRightOnRectangleIcon class="w-5 h-5 text-gray-400" />
              ออกจากระบบ
            </button>
          </MenuItem>
        </div>
      </MenuItems>
    </Transition>
  </Menu>
</template>

<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import {
  UserIcon,
  BookmarkIcon,
  FolderIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

// TODO: Connect to auth store
const user = ref({
  name: 'Test User',
  email: 'test@example.com',
  avatar: null,
})

const logout = async () => {
  // TODO: Implement logout
  console.log('Logout')
}
</script>
