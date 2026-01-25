<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="max-w-md w-full space-y-8">
      <!-- Logo -->
      <div class="text-center">
        <NuxtLink to="/" class="inline-flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-xl">ไทย</span>
          </div>
          <span class="text-2xl font-bold text-white">DevDocs</span>
        </NuxtLink>
        <h2 class="mt-6 text-3xl font-bold text-white">เข้าสู่ระบบ</h2>
        <p class="mt-2 text-gray-400">
          ยังไม่มีบัญชี?
          <NuxtLink to="/auth/register" class="text-primary-400 hover:text-primary-300">
            สมัครสมาชิก
          </NuxtLink>
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="login" class="mt-8 space-y-6 bg-slate-800/50 backdrop-blur-xl p-8 rounded-2xl border border-slate-700">
        <div class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-300">อีเมล</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              placeholder="your@email.com"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-300">รหัสผ่าน</label>
            <div class="relative mt-1">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                placeholder="••••••••"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-300"
              >
                <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <input
              v-model="form.remember"
              type="checkbox"
              class="rounded border-slate-600 bg-slate-900/50 text-primary-500 focus:ring-primary-500"
            />
            <span class="ml-2 text-sm text-gray-400">จดจำการเข้าสู่ระบบ</span>
          </label>

          <NuxtLink to="/auth/forgot-password" class="text-sm text-primary-400 hover:text-primary-300">
            ลืมรหัสผ่าน?
          </NuxtLink>
        </div>

        <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Icon v-if="isLoading" name="heroicons:arrow-path" class="w-5 h-5 mr-2 animate-spin" />
          {{ isLoading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
        </button>

        <!-- Social Login -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-700" />
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-slate-800/50 text-gray-500">หรือเข้าสู่ระบบด้วย</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <button
            type="button"
            @click="loginWithProvider('github')"
            class="flex items-center justify-center gap-2 py-3 px-4 border border-slate-600 rounded-xl text-gray-300 hover:bg-slate-700/50 transition-all"
          >
            <Icon name="mdi:github" class="w-5 h-5" />
            GitHub
          </button>
          <button
            type="button"
            @click="loginWithProvider('google')"
            class="flex items-center justify-center gap-2 py-3 px-4 border border-slate-600 rounded-xl text-gray-300 hover:bg-slate-700/50 transition-all"
          >
            <Icon name="mdi:google" class="w-5 h-5" />
            Google
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

const { login: authLogin } = useAuth()
const router = useRouter()
const route = useRoute()

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)
const isLoading = ref(false)
const error = ref('')

const login = async () => {
  isLoading.value = true
  error.value = ''

  try {
    await authLogin(form.email, form.password)
    const redirect = route.query.redirect as string || '/'
    router.push(redirect)
  } catch (e: any) {
    error.value = e.data?.message || 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'
  } finally {
    isLoading.value = false
  }
}

const loginWithProvider = (provider: string) => {
  // OAuth login would be implemented here
  console.log('Login with:', provider)
}
</script>
