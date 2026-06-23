<template>
  <div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">การตั้งค่า</h1>

    <div class="space-y-8">
      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <PaintBrushIcon class="w-5 h-5" />
          การแสดงผล
        </h2>

        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ธีม</label>
            <div class="grid grid-cols-3 gap-4">
              <button
                v-for="theme in themes"
                :key="theme.value"
                type="button"
                :class="[
                  'p-4 rounded-xl border-2 transition-all',
                  preferencesStore.api.theme === theme.value
                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                    : 'border-gray-200 dark:border-slate-600 hover:border-gray-300 dark:hover:border-slate-500',
                ]"
                @click="preferencesStore.setTheme(theme.value)"
              >
                <component :is="theme.icon" class="w-6 h-6 mx-auto mb-2 text-gray-500" />
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ theme.label }}</p>
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ขนาดตัวอักษร</label>
            <div class="flex items-center gap-4">
              <button
                type="button"
                class="p-2 rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600"
                @click="changeFontSize(-2)"
              >
                <MinusIcon class="w-5 h-5" />
              </button>
              <div class="flex-1">
                <input
                  :value="preferencesStore.fontSizePx"
                  type="range"
                  min="12"
                  max="24"
                  step="2"
                  class="w-full accent-primary-600"
                  @input="onFontSizeInput"
                >
              </div>
              <button
                type="button"
                class="p-2 rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600"
                @click="changeFontSize(2)"
              >
                <PlusIcon class="w-5 h-5" />
              </button>
              <span class="w-12 text-center text-gray-700 dark:text-gray-300">{{ preferencesStore.fontSizePx }}px</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">ธีมโค้ด</label>
            <select
              v-model="preferencesStore.api.code_theme"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl dark:text-white"
            >
              <option value="github-dark">GitHub Dark</option>
              <option value="github-light">GitHub Light</option>
              <option value="monokai">Monokai</option>
              <option value="dracula">Dracula</option>
              <option value="one-dark">One Dark</option>
            </select>
          </div>
        </div>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <BookOpenIcon class="w-5 h-5" />
          การอ่าน
        </h2>

        <div class="space-y-4">
          <label v-for="item in readingToggles" :key="item.key" class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">{{ item.label }}</p>
              <p class="text-sm text-gray-500">{{ item.description }}</p>
            </div>
            <input
              v-model="preferencesStore.local[item.key]"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            >
          </label>
        </div>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <BellIcon class="w-5 h-5" />
          การแจ้งเตือน
        </h2>

        <div class="space-y-4">
          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">อีเมลสรุปรายสัปดาห์</p>
              <p class="text-sm text-gray-500">รับสรุปบทความใหม่ทุกสัปดาห์</p>
            </div>
            <input v-model="preferencesStore.api.weekly_digest" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
          </label>

          <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-slate-900 cursor-pointer">
            <div>
              <p class="font-medium text-gray-900 dark:text-white">การแจ้งเตือนทางอีเมล</p>
              <p class="text-sm text-gray-500">รับอีเมลเกี่ยวกับความคิดเห็นและการมีส่วนร่วม</p>
            </div>
            <input v-model="preferencesStore.api.email_notifications" type="checkbox" class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
          </label>
        </div>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
          <LanguageIcon class="w-5 h-5" />
          ภาษา
        </h2>
        <select
          v-model="preferencesStore.local.preferred_language"
          class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl dark:text-white"
        >
          <option value="th">ไทย</option>
          <option value="en">English</option>
          <option value="both">ทั้งสองภาษา</option>
        </select>
      </section>

      <div class="flex justify-end">
        <button
          type="button"
          :disabled="preferencesStore.isSaving"
          class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
          @click="savePreferences"
        >
          {{ preferencesStore.isSaving ? 'กำลังบันทึก...' : 'บันทึกการตั้งค่า' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  PaintBrushIcon,
  BookOpenIcon,
  BellIcon,
  LanguageIcon,
  SunIcon,
  MoonIcon,
  ComputerDesktopIcon,
  MinusIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'
import type { ThemePreference } from '~/stores/preferences'

definePageMeta({
  middleware: 'auth',
})

const preferencesStore = usePreferencesStore()

const themes: Array<{ value: ThemePreference; label: string; icon: any }> = [
  { value: 'light', label: 'สว่าง', icon: SunIcon },
  { value: 'dark', label: 'มืด', icon: MoonIcon },
  { value: 'system', label: 'ตามระบบ', icon: ComputerDesktopIcon },
]

const readingToggles = [
  { key: 'save_reading_history' as const, label: 'บันทึกประวัติการอ่าน', description: 'บันทึกบทความที่คุณอ่านเพื่อดูภายหลัง' },
  { key: 'remember_scroll_position' as const, label: 'จดจำตำแหน่งการอ่าน', description: 'เมื่อกลับมาอ่านใหม่จะเปิดที่ตำแหน่งเดิม' },
  { key: 'show_toc' as const, label: 'แสดงสารบัญ', description: 'แสดงสารบัญด้านข้างเมื่ออ่านบทความ' },
  { key: 'show_line_numbers' as const, label: 'แสดงโค้ดพร้อม Line Numbers', description: 'แสดงเลขบรรทัดในบล็อกโค้ด' },
]

onMounted(async () => {
  if (!preferencesStore.isLoaded) {
    await preferencesStore.fetchFromApi()
  }
})

function onFontSizeInput(event: Event) {
  const value = Number((event.target as HTMLInputElement).value)
  preferencesStore.setFontSizeFromPx(value)
}

function changeFontSize(delta: number) {
  preferencesStore.setFontSizeFromPx(preferencesStore.fontSizePx + delta)
}

async function savePreferences() {
  preferencesStore.saveLocalToStorage()
  await preferencesStore.saveToApi()
}
</script>
