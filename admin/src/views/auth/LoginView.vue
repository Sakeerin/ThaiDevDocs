<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4">
    <div class="max-w-md w-full">
      <div class="card p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary-600 to-primary-500 flex items-center justify-center mx-auto mb-4">
            <span class="text-white font-bold text-3xl">ไ</span>
          </div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            ThaiDevDocs Admin
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            เข้าสู่ระบบเพื่อจัดการเนื้อหา
          </p>
        </div>

        <!-- Error Message -->
        <div
          v-if="error"
          class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800"
        >
          <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label for="email" class="label">อีเมล</label>
            <input
              id="email"
              v-model="email"
              type="email"
              required
              class="input"
              placeholder="admin@example.com"
            />
          </div>

          <div>
            <label for="password" class="label">รหัสผ่าน</label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              class="input"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="btn-primary w-full"
          >
            <span v-if="isLoading" class="flex items-center gap-2">
              <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              กำลังเข้าสู่ระบบ...
            </span>
            <span v-else>เข้าสู่ระบบ</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  error.value = ''
  isLoading.value = true

  try {
    await authStore.login(email.value, password.value)
    
    const redirect = route.query.redirect as string
    router.push(redirect || '/')
  } catch (err: any) {
    error.value = err.response?.data?.error?.message || 'เกิดข้อผิดพลาด กรุณาลองอีกครั้ง'
  } finally {
    isLoading.value = false
  }
}
</script>
