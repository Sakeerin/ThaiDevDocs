<template>
  <AuthPageHeader title="ลืมรหัสผ่าน?" @submit="submit">
    <template #subtitle>
      จำรหัสผ่านได้แล้ว?
      <NuxtLink to="/auth/login" class="text-primary-400 hover:text-primary-300">
        กลับไปเข้าสู่ระบบ
      </NuxtLink>
    </template>

    <template v-if="isSuccess">
      <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-emerald-400 text-sm">
        หากอีเมลนี้มีอยู่ในระบบ เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปให้แล้ว กรุณาตรวจสอบกล่องจดหมายของคุณ
      </div>
      <NuxtLink
        to="/auth/login"
        class="block w-full text-center py-3 px-4 rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 font-medium transition-all"
      >
        กลับไปเข้าสู่ระบบ
      </NuxtLink>
    </template>

    <template v-else>
      <p class="text-sm text-gray-400">
        ใส่อีเมลที่ใช้สมัครสมาชิก เราจะส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ให้คุณ
      </p>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-300">อีเมล</label>
        <input
          id="email"
          v-model="email"
          type="email"
          required
          autocomplete="email"
          class="mt-1 block w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
          placeholder="your@email.com"
        />
      </div>

      <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
        {{ error }}
      </div>

      <button
        type="submit"
        :disabled="isLoading"
        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ isLoading ? 'กำลังส่งลิงก์...' : 'ส่งลิงก์รีเซ็ตรหัสผ่าน' }}
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
  title: 'ลืมรหัสผ่าน',
})

const { forgotPassword } = useAuth()

const email = ref('')
const isLoading = ref(false)
const isSuccess = ref(false)
const error = ref('')

const submit = async () => {
  isLoading.value = true
  error.value = ''

  try {
    await forgotPassword(email.value)
    isSuccess.value = true
  } catch (e: any) {
    error.value = e?.error?.message || e?.message || 'ไม่สามารถส่งลิงก์รีเซ็ตรหัสผ่านได้ กรุณาลองใหม่'
  } finally {
    isLoading.value = false
  }
}
</script>
