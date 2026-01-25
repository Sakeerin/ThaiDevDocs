<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">โปรไฟล์ของฉัน</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Sidebar -->
      <div class="lg:col-span-1">
        <!-- Avatar Section -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
          <div class="text-center">
            <div class="relative inline-block">
              <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-4xl font-bold mx-auto">
                {{ user?.name?.charAt(0).toUpperCase() }}
              </div>
              <button class="absolute bottom-0 right-0 w-10 h-10 bg-white dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-full flex items-center justify-center shadow-lg hover:bg-gray-50 dark:hover:bg-slate-600">
                <Icon name="heroicons:camera" class="w-5 h-5 text-gray-600 dark:text-gray-300" />
              </button>
            </div>
            <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">{{ user?.name }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
            <div class="mt-4 flex justify-center gap-4">
              <div class="text-center">
                <p class="text-2xl font-bold text-primary-600">{{ user?.contribution_points || 0 }}</p>
                <p class="text-xs text-gray-500">แต้มสะสม</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-emerald-600">{{ user?.articles_count || 0 }}</p>
                <p class="text-xs text-gray-500">บทความ</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="mt-6 bg-white dark:bg-slate-800 rounded-2xl p-4 border border-gray-200 dark:border-slate-700">
          <ul class="space-y-1">
            <li v-for="item in navItems" :key="item.to">
              <NuxtLink
                :to="item.to"
                :class="[
                  'flex items-center gap-3 px-4 py-3 rounded-xl transition-colors',
                  route.path === item.to
                    ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700'
                ]"
              >
                <Icon :name="item.icon" class="w-5 h-5" />
                {{ item.label }}
              </NuxtLink>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Profile Form -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">ข้อมูลส่วนตัว</h3>
          
          <form @submit.prevent="updateProfile" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ชื่อที่แสดง</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ชื่อผู้ใช้</label>
                <input
                  v-model="profileForm.username"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">อีเมล</label>
              <input
                :value="user?.email"
                type="email"
                disabled
                class="w-full px-4 py-3 bg-gray-100 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-600 rounded-xl text-gray-500 cursor-not-allowed"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">เกี่ยวกับฉัน</label>
              <textarea
                v-model="profileForm.bio"
                rows="3"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white resize-none"
                placeholder="บอกเล่าเกี่ยวกับตัวคุณ..."
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">เว็บไซต์</label>
              <input
                v-model="profileForm.website"
                type="url"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
                placeholder="https://yourwebsite.com"
              />
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="isSaving"
                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
              >
                {{ isSaving ? 'กำลังบันทึก...' : 'บันทึกการเปลี่ยนแปลง' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">เปลี่ยนรหัสผ่าน</h3>
          
          <form @submit.prevent="changePassword" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">รหัสผ่านปัจจุบัน</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">รหัสผ่านใหม่</label>
                <input
                  v-model="passwordForm.password"
                  type="password"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ยืนยันรหัสผ่านใหม่</label>
                <input
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
                />
              </div>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="isChangingPassword"
                class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
              >
                {{ isChangingPassword ? 'กำลังเปลี่ยน...' : 'เปลี่ยนรหัสผ่าน' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const route = useRoute()
const { user, updateProfile: authUpdateProfile } = useAuth()

const isSaving = ref(false)
const isChangingPassword = ref(false)

const profileForm = reactive({
  name: user.value?.name || '',
  username: user.value?.username || '',
  bio: user.value?.bio || '',
  website: user.value?.website || '',
})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const navItems = [
  { to: '/user/profile', label: 'โปรไฟล์', icon: 'heroicons:user' },
  { to: '/user/bookmarks', label: 'บุ๊กมาร์ก', icon: 'heroicons:bookmark' },
  { to: '/user/history', label: 'ประวัติการอ่าน', icon: 'heroicons:clock' },
  { to: '/user/preferences', label: 'การตั้งค่า', icon: 'heroicons:cog-6-tooth' },
  { to: '/user/contributions', label: 'การมีส่วนร่วม', icon: 'heroicons:pencil-square' },
]

watch(user, (newUser) => {
  if (newUser) {
    profileForm.name = newUser.name || ''
    profileForm.username = newUser.username || ''
    profileForm.bio = newUser.bio || ''
    profileForm.website = newUser.website || ''
  }
}, { immediate: true })

const updateProfile = async () => {
  isSaving.value = true
  try {
    await authUpdateProfile(profileForm)
  } catch (e) {
    console.error('Failed to update profile:', e)
  } finally {
    isSaving.value = false
  }
}

const changePassword = async () => {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    alert('รหัสผ่านไม่ตรงกัน')
    return
  }

  isChangingPassword.value = true
  try {
    await $fetch('/api/v1/user/password', {
      method: 'PUT',
      body: passwordForm,
    })
    alert('เปลี่ยนรหัสผ่านเรียบร้อยแล้ว')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (e: any) {
    alert(e.data?.message || 'เกิดข้อผิดพลาด')
  } finally {
    isChangingPassword.value = false
  }
}
</script>
