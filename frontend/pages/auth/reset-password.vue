<template>
  <AuthPageHeader title="ตั้งรหัสผ่านใหม่" @submit="submit">
    <template #subtitle>
      ใส่รหัสผ่านใหม่สำหรับบัญชีของคุณ
    </template>

    <div v-if="!hasToken" class="space-y-4">
      <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
        ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุแล้ว
      </div>
      <NuxtLink
        to="/auth/forgot-password"
        class="block w-full text-center py-3 px-4 rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 font-medium transition-all"
      >
        ขอลิงก์ใหม่
      </NuxtLink>
    </div>

    <template v-else>
      <div>
        <label for="email" class="block text-sm font-medium text-gray-300">อีเมล</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          readonly
          class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-gray-400 cursor-not-allowed"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-300">รหัสผ่านใหม่</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          minlength="8"
          autocomplete="new-password"
          class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
          placeholder="อย่างน้อย 8 ตัวอักษร"
        />
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-300">ยืนยันรหัสผ่านใหม่</label>
        <input
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          autocomplete="new-password"
          class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
          placeholder="ใส่รหัสผ่านอีกครั้ง"
        />
      </div>

      <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
        {{ error }}
      </div>

      <button
        type="submit"
        :disabled="isLoading"
        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isLoading ? 'กำลังบันทึก...' : 'ตั้งรหัสผ่านใหม่' }}
      </button>
    </template>
  </AuthPageHeader>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

useHead({
  title: 'ตั้งรหัสผ่านใหม่',
})

const route = useRoute()
const router = useRouter()
const { resetPassword } = useAuth()

const token = computed(() => (route.query.token as string) || '')
const hasToken = computed(() => !!token.value && !!route.query.email)

const form = reactive({
  email: (route.query.email as string) || '',
  password: '',
  password_confirmation: '',
})

const isLoading = ref(false)
const error = ref('')

const submit = async () => {
  if (!hasToken.value) return

  error.value = ''

  if (form.password !== form.password_confirmation) {
    error.value = 'รหัสผ่านไม่ตรงกัน'
    return
  }

  isLoading.value = true

  try {
    await resetPassword(form.email, token.value, form.password, form.password_confirmation)
    await router.push('/auth/login')
  } catch (e: any) {
    error.value = e?.error?.message || e?.message || 'ไม่สามารถตั้งรหัสผ่านใหม่ได้ กรุณาขอลิงก์ใหม่'
  } finally {
    isLoading.value = false
  }
}
</script>
