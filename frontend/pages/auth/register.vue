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
        <h2 class="mt-6 text-3xl font-bold text-white">สมัครสมาชิก</h2>
        <p class="mt-2 text-gray-400">
          มีบัญชีอยู่แล้ว?
          <NuxtLink to="/auth/login" class="text-primary-400 hover:text-primary-300">
            เข้าสู่ระบบ
          </NuxtLink>
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="register" class="mt-8 space-y-6 bg-slate-800/50 backdrop-blur-xl p-8 rounded-2xl border border-slate-700">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-300">ชื่อที่แสดง</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              placeholder="ชื่อของคุณ"
            />
          </div>

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
                minlength="8"
                class="block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                placeholder="อย่างน้อย 8 ตัวอักษร"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-300"
              >
                <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-5 h-5" />
              </button>
            </div>
            <!-- Password Strength -->
            <div class="mt-2">
              <div class="flex gap-1">
                <div
                  v-for="i in 4"
                  :key="i"
                  :class="[
                    'h-1 flex-1 rounded-full transition-all',
                    i <= passwordStrength ? strengthColors[passwordStrength - 1] : 'bg-slate-700'
                  ]"
                />
              </div>
              <p class="text-xs text-gray-500 mt-1">{{ strengthLabels[passwordStrength] || 'ใส่รหัสผ่าน' }}</p>
            </div>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-300">ยืนยันรหัสผ่าน</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showPassword ? 'text' : 'password'"
              required
              class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              placeholder="ใส่รหัสผ่านอีกครั้ง"
            />
          </div>
        </div>

        <label class="flex items-start gap-3">
          <input
            v-model="form.agree_terms"
            type="checkbox"
            required
            class="mt-1 rounded border-slate-600 bg-slate-900/50 text-primary-500 focus:ring-primary-500"
          />
          <span class="text-sm text-gray-400">
            ฉันยอมรับ
            <NuxtLink to="/terms" class="text-primary-400 hover:underline">ข้อกำหนดการใช้งาน</NuxtLink>
            และ
            <NuxtLink to="/privacy" class="text-primary-400 hover:underline">นโยบายความเป็นส่วนตัว</NuxtLink>
          </span>
        </label>

        <div v-if="errors.length > 0" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(err, i) in errors" :key="i">{{ err }}</li>
          </ul>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Icon v-if="isLoading" name="heroicons:arrow-path" class="w-5 h-5 mr-2 animate-spin" />
          {{ isLoading ? 'กำลังสมัครสมาชิก...' : 'สมัครสมาชิก' }}
        </button>

        <!-- Social Register -->
        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-700" />
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-slate-800/50 text-gray-500">หรือสมัครด้วย</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <button
            type="button"
            @click="registerWithProvider('github')"
            class="flex items-center justify-center gap-2 py-3 px-4 border border-slate-600 rounded-xl text-gray-300 hover:bg-slate-700/50 transition-all"
          >
            <Icon name="mdi:github" class="w-5 h-5" />
            GitHub
          </button>
          <button
            type="button"
            @click="registerWithProvider('google')"
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

const { register: authRegister } = useAuth()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  agree_terms: false,
})

const showPassword = ref(false)
const isLoading = ref(false)
const errors = ref<string[]>([])

const strengthColors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-emerald-500']
const strengthLabels = ['', 'อ่อน', 'พอใช้', 'ดี', 'ดีมาก']

const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return 0
  
  let strength = 0
  if (password.length >= 8) strength++
  if (/[A-Z]/.test(password)) strength++
  if (/[0-9]/.test(password)) strength++
  if (/[^A-Za-z0-9]/.test(password)) strength++
  
  return strength
})

const register = async () => {
  errors.value = []

  if (form.password !== form.password_confirmation) {
    errors.value.push('รหัสผ่านไม่ตรงกัน')
    return
  }

  if (!form.agree_terms) {
    errors.value.push('กรุณายอมรับข้อกำหนดการใช้งาน')
    return
  }

  isLoading.value = true

  try {
    await authRegister(form.name, form.email, form.password, form.password_confirmation)
    router.push('/auth/verify-email')
  } catch (e: any) {
    if (e.data?.errors) {
      errors.value = Object.values(e.data.errors).flat() as string[]
    } else {
      errors.value = [e.data?.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก']
    }
  } finally {
    isLoading.value = false
  }
}

const registerWithProvider = (provider: string) => {
  console.log('Register with:', provider)
}
</script>
