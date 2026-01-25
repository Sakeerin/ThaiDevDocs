<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from './stores/auth'
import { onMounted } from 'vue'

const authStore = useAuthStore()

onMounted(async () => {
  await authStore.init()
})
</script>
