<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">การตั้งค่า</h1>

    <div class="space-y-8">
      <!-- Appearance -->
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="heroicons:paint-brush" class="w-5 h-5" />
          การแสดงผล
        </h2>

        <div class="space-y-6">
          <!-- Theme -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ธีม</label>
            <div class="grid grid-cols-3 gap-4">
              <button
                v-for="theme in themes"
                :key="theme.value"
                @click="preferences.theme = theme.value"
                :class="[
                  'p-4 rounded-xl border-2 transition-all',
                  preferences.theme === theme.value
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                    : 'border-gray-200 dark:border-slate-600 hover:border-gray-300 dark:hover:border-slate-500'
                ]"
              >
                <Icon :name="theme.icon" class="w-6 h-6 mx-auto mb-2" :class="preferences.theme === theme.value ? 'text-primary-600' : 'text-gray-500'" />
                <p :class="preferences.theme === theme.value ? 'text-primary-600' : 'text-gray-700 dark:text-gray-300'" class="text-sm font-medium">
                  {{ theme.label }}
                </p>
              </button>
            </div>
          </div>

          <!-- Font Size -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ขนาดตัวอักษร</label>
            <div class="flex items-center gap-4">
              <button
                @click="preferences.font_size = Math.max(12, preferences.font_size - 2)"
                class="p-2 rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600"
              >
                <Icon name="heroicons:minus" class="w-5 h-5" />
              </button>
              <div class="flex-1">
                <input
                  v-model.number="preferences.font_size"
                  type="range"
                  min="12"
                  max="24"
                  step="2"
                  class="w-full accent-primary-600"
                />
              </div>
              <button
                @click="preferences.font_size = Math.min(24, preferences.font_size + 2)"
                class="p-2 rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600"
              >
                <Icon name="heroicons:plus" class="w-5 h-5" />
              </button>
              <span class="w-12 text-center text-gray-700 dark:text-gray-300">{{ preferences.font_size }}px</span>
            </div>
            <p class="mt-2 text-gray-500 text-sm" :style="{ fontSize: preferences.font_size + 'px' }">
              ตัวอย่างข้อความ - สวัสดีครับ/ค่ะ
            </p>
          </div>

          <!-- Code Theme -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ธีมโค้ด</label>
            <select v-model="preferences.code_theme" class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl dark:text-white">
              <option value="github-dark">GitHub Dark</option>
              <option value="github-light">GitHub Light</option>
              <option value="monokai">Monokai</option>
              <option value="dracula">Dracula</option>
              <option value="one-dark">One Dark</option>
            </select>
          </div>
        </div>
      </section>

      <!-- Reading -->
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="heroicons:book-open" class="w-5 h-5" />
          การอ่าน
        </h2>

        <div class="space-y-4">
          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">บันทึกประวัติการอ่าน</p>
              <p class="text-sm text-gray-500">บันทึกบทความที่คุณอ่านเพื่อดูภายหลัง</p>
            </div>
            <input
              v-model="preferences.save_reading_history"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>

          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">จดจำตำแหน่งการอ่าน</p>
              <p class="text-sm text-gray-500">เมื่อกลับมาอ่านใหม่จะเปิดที่ตำแหน่งเดิม</p>
            </div>
            <input
              v-model="preferences.remember_scroll_position"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>

          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">แสดงสารบัญ</p>
              <p class="text-sm text-gray-500">แสดงสารบัญด้านข้างเมื่ออ่านบทความ</p>
            </div>
            <input
              v-model="preferences.show_toc"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>

          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">แสดงโค้ดพร้อม Line Numbers</p>
              <p class="text-sm text-gray-500">แสดงเลขบรรทัดในบล็อกโค้ด</p>
            </div>
            <input
              v-model="preferences.show_line_numbers"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>
        </div>
      </section>

      <!-- Notifications -->
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="heroicons:bell" class="w-5 h-5" />
          การแจ้งเตือน
        </h2>

        <div class="space-y-4">
          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">อีเมลสรุปรายสัปดาห์</p>
              <p class="text-sm text-gray-500">รับสรุปบทความใหม่ทุกสัปดาห์</p>
            </div>
            <input
              v-model="preferences.email_weekly_digest"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>

          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">แจ้งเตือนตอบกลับความคิดเห็น</p>
              <p class="text-sm text-gray-500">รับอีเมลเมื่อมีคนตอบความคิดเห็นของคุณ</p>
            </div>
            <input
              v-model="preferences.email_comment_replies"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>

          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">แจ้งเตือนการแก้ไขได้รับอนุมัติ</p>
              <p class="text-sm text-gray-500">รับอีเมลเมื่อการแก้ไขของคุณได้รับอนุมัติ</p>
            </div>
            <input
              v-model="preferences.email_contribution_updates"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
          </label>
        </div>
      </section>

      <!-- Language -->
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <Icon name="heroicons:language" class="w-5 h-5" />
          ภาษา
        </h2>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ภาษาที่ต้องการ</label>
          <select v-model="preferences.preferred_language" class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl dark:text-white">
            <option value="th">ไทย</option>
            <option value="en">English</option>
            <option value="both">ทั้งสองภาษา</option>
          </select>
        </div>
      </section>

      <!-- Save Button -->
      <div class="flex justify-end">
        <button
          @click="savePreferences"
          :disabled="isSaving"
          class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
        >
          <Icon v-if="isSaving" name="heroicons:arrow-path" class="w-5 h-5 inline-block mr-2 animate-spin" />
          {{ isSaving ? 'กำลังบันทึก...' : 'บันทึกการตั้งค่า' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { $api } = useNuxtApp()
const colorMode = useColorMode()

const isSaving = ref(false)

const preferences = reactive({
  theme: 'system',
  font_size: 16,
  code_theme: 'github-dark',
  save_reading_history: true,
  remember_scroll_position: true,
  show_toc: true,
  show_line_numbers: true,
  email_weekly_digest: true,
  email_comment_replies: true,
  email_contribution_updates: true,
  preferred_language: 'th',
})

const themes = [
  { value: 'light', label: 'สว่าง', icon: 'heroicons:sun' },
  { value: 'dark', label: 'มืด', icon: 'heroicons:moon' },
  { value: 'system', label: 'ตามระบบ', icon: 'heroicons:computer-desktop' },
]

onMounted(async () => {
  try {
    const { data } = await $api('/user/preferences')
    Object.assign(preferences, data)
  } catch (e) {
    console.error('Failed to fetch preferences:', e)
  }
})

watch(() => preferences.theme, (newTheme) => {
  colorMode.preference = newTheme
})

async function savePreferences() {
  isSaving.value = true
  try {
    await $api('/user/preferences', {
      method: 'PUT',
      body: preferences,
    })
    // Could show a toast notification
  } catch (e) {
    console.error('Failed to save preferences:', e)
  } finally {
    isSaving.value = false
  }
}
</script>
