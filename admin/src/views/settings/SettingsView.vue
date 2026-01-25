<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">การตั้งค่า</h1>
      <p class="text-gray-600 dark:text-gray-400">จัดการการตั้งค่าระบบ</p>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-gray-700">
      <nav class="flex -mb-px gap-6">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'py-3 px-1 border-b-2 text-sm font-medium transition-colors',
            activeTab === tab.id
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
          ]"
        >
          <component :is="tab.icon" class="w-5 h-5 inline-block mr-2" />
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- General Settings -->
    <div v-if="activeTab === 'general'" class="card p-6 space-y-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-white">ตั้งค่าทั่วไป</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="label">ชื่อเว็บไซต์</label>
          <input v-model="settings.site_name" type="text" class="input" />
        </div>
        <div>
          <label class="label">ชื่อเว็บไซต์ (ภาษาอังกฤษ)</label>
          <input v-model="settings.site_name_en" type="text" class="input" />
        </div>
        <div class="md:col-span-2">
          <label class="label">คำอธิบายเว็บไซต์</label>
          <textarea v-model="settings.site_description" class="input resize-none h-24" />
        </div>
        <div>
          <label class="label">อีเมลติดต่อ</label>
          <input v-model="settings.contact_email" type="email" class="input" />
        </div>
        <div>
          <label class="label">URL เว็บไซต์</label>
          <input v-model="settings.site_url" type="url" class="input" />
        </div>
      </div>

      <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="font-medium text-gray-900 dark:text-white mb-4">การแสดงผล</h3>
        <div class="space-y-4">
          <label class="flex items-center gap-3">
            <input
              type="checkbox"
              v-model="settings.show_reading_time"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <span class="text-gray-700 dark:text-gray-300">แสดงเวลาอ่านโดยประมาณ</span>
          </label>
          <label class="flex items-center gap-3">
            <input
              type="checkbox"
              v-model="settings.show_difficulty"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <span class="text-gray-700 dark:text-gray-300">แสดงระดับความยาก</span>
          </label>
          <label class="flex items-center gap-3">
            <input
              type="checkbox"
              v-model="settings.show_author"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <span class="text-gray-700 dark:text-gray-300">แสดงชื่อผู้เขียน</span>
          </label>
          <label class="flex items-center gap-3">
            <input
              type="checkbox"
              v-model="settings.show_last_updated"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            />
            <span class="text-gray-700 dark:text-gray-300">แสดงวันที่อัปเดตล่าสุด</span>
          </label>
        </div>
      </div>
    </div>

    <!-- SEO Settings -->
    <div v-if="activeTab === 'seo'" class="card p-6 space-y-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-white">ตั้งค่า SEO</h2>

      <div class="space-y-4">
        <div>
          <label class="label">Default Meta Title</label>
          <input v-model="settings.default_meta_title" type="text" class="input" maxlength="60" />
          <p class="text-xs text-gray-400 mt-1">{{ settings.default_meta_title?.length || 0 }}/60</p>
        </div>
        <div>
          <label class="label">Default Meta Description</label>
          <textarea v-model="settings.default_meta_description" class="input resize-none h-20" maxlength="160" />
          <p class="text-xs text-gray-400 mt-1">{{ settings.default_meta_description?.length || 0 }}/160</p>
        </div>
        <div>
          <label class="label">Google Analytics ID</label>
          <input v-model="settings.google_analytics_id" type="text" class="input" placeholder="G-XXXXXXXXXX" />
        </div>
        <div>
          <label class="label">Google Search Console Verification</label>
          <input v-model="settings.google_verification" type="text" class="input" />
        </div>
      </div>

      <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="font-medium text-gray-900 dark:text-white mb-4">Social Media</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">OG Image URL</label>
            <input v-model="settings.og_image" type="url" class="input" />
          </div>
          <div>
            <label class="label">Twitter Handle</label>
            <input v-model="settings.twitter_handle" type="text" class="input" placeholder="@thaidevdocs" />
          </div>
          <div>
            <label class="label">Facebook Page URL</label>
            <input v-model="settings.facebook_url" type="url" class="input" />
          </div>
          <div>
            <label class="label">GitHub URL</label>
            <input v-model="settings.github_url" type="url" class="input" />
          </div>
        </div>
      </div>
    </div>

    <!-- Comments Settings -->
    <div v-if="activeTab === 'comments'" class="card p-6 space-y-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-white">ตั้งค่าความคิดเห็น</h2>

      <div class="space-y-4">
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            v-model="settings.comments_enabled"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-gray-700 dark:text-gray-300">เปิดใช้งานระบบความคิดเห็น</span>
        </label>
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            v-model="settings.comments_require_login"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-gray-700 dark:text-gray-300">ต้องเข้าสู่ระบบก่อนแสดงความคิดเห็น</span>
        </label>
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            v-model="settings.comments_require_approval"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-gray-700 dark:text-gray-300">ต้องอนุมัติความคิดเห็นก่อนแสดง</span>
        </label>
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            v-model="settings.comments_allow_replies"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-gray-700 dark:text-gray-300">อนุญาตให้ตอบกลับความคิดเห็น</span>
        </label>
      </div>

      <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">ความยาวความคิดเห็นสูงสุด</label>
            <div class="flex gap-2">
              <input v-model.number="settings.comment_max_length" type="number" class="input" min="100" max="5000" />
              <span class="text-gray-500 self-center">ตัวอักษร</span>
            </div>
          </div>
          <div>
            <label class="label">ระดับการตอบกลับสูงสุด</label>
            <select v-model.number="settings.comment_max_depth" class="input">
              <option :value="1">1 ระดับ</option>
              <option :value="2">2 ระดับ</option>
              <option :value="3">3 ระดับ</option>
              <option :value="5">5 ระดับ</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Contributions Settings -->
    <div v-if="activeTab === 'contributions'" class="card p-6 space-y-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-white">ตั้งค่าการมีส่วนร่วม</h2>

      <div class="space-y-4">
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            v-model="settings.contributions_enabled"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-gray-700 dark:text-gray-300">เปิดใช้งานระบบการแนะนำแก้ไข</span>
        </label>
        <label class="flex items-center gap-3">
          <input
            type="checkbox"
            v-model="settings.contributions_require_login"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <span class="text-gray-700 dark:text-gray-300">ต้องเข้าสู่ระบบก่อนแนะนำแก้ไข</span>
        </label>
      </div>

      <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h3 class="font-medium text-gray-900 dark:text-white mb-4">แต้มสะสม</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="label">แก้ไขได้รับอนุมัติ</label>
            <input v-model.number="settings.points_edit_approved" type="number" class="input" min="0" />
          </div>
          <div>
            <label class="label">เขียนบทความใหม่</label>
            <input v-model.number="settings.points_new_article" type="number" class="input" min="0" />
          </div>
          <div>
            <label class="label">ความคิดเห็นมีประโยชน์</label>
            <input v-model.number="settings.points_helpful_comment" type="number" class="input" min="0" />
          </div>
        </div>
      </div>
    </div>

    <!-- Email Settings -->
    <div v-if="activeTab === 'email'" class="card p-6 space-y-6">
      <h2 class="text-lg font-medium text-gray-900 dark:text-white">ตั้งค่าอีเมล</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="label">SMTP Host</label>
          <input v-model="settings.smtp_host" type="text" class="input" />
        </div>
        <div>
          <label class="label">SMTP Port</label>
          <input v-model.number="settings.smtp_port" type="number" class="input" />
        </div>
        <div>
          <label class="label">SMTP Username</label>
          <input v-model="settings.smtp_username" type="text" class="input" />
        </div>
        <div>
          <label class="label">SMTP Password</label>
          <input v-model="settings.smtp_password" type="password" class="input" />
        </div>
        <div>
          <label class="label">From Email</label>
          <input v-model="settings.mail_from_address" type="email" class="input" />
        </div>
        <div>
          <label class="label">From Name</label>
          <input v-model="settings.mail_from_name" type="text" class="input" />
        </div>
      </div>

      <button @click="testEmail" class="btn-outline" :disabled="testingEmail">
        {{ testingEmail ? 'กำลังส่ง...' : 'ทดสอบส่งอีเมล' }}
      </button>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
      <button @click="saveSettings" :disabled="isSaving" class="btn-primary">
        <ArrowPathIcon v-if="isSaving" class="w-5 h-5 mr-2 animate-spin" />
        {{ isSaving ? 'กำลังบันทึก...' : 'บันทึกการตั้งค่า' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, markRaw } from 'vue'
import {
  Cog6ToothIcon,
  GlobeAltIcon,
  ChatBubbleLeftRightIcon,
  UserGroupIcon,
  EnvelopeIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import api from '@/services/api'

const activeTab = ref('general')
const isSaving = ref(false)
const testingEmail = ref(false)

const tabs = [
  { id: 'general', name: 'ทั่วไป', icon: markRaw(Cog6ToothIcon) },
  { id: 'seo', name: 'SEO', icon: markRaw(GlobeAltIcon) },
  { id: 'comments', name: 'ความคิดเห็น', icon: markRaw(ChatBubbleLeftRightIcon) },
  { id: 'contributions', name: 'การมีส่วนร่วม', icon: markRaw(UserGroupIcon) },
  { id: 'email', name: 'อีเมล', icon: markRaw(EnvelopeIcon) },
]

const settings = reactive({
  // General
  site_name: 'Thai Developer Docs',
  site_name_en: 'Thai Developer Docs',
  site_description: 'แหล่งเรียนรู้การพัฒนาเว็บไซต์ภาษาไทย',
  contact_email: 'contact@thaidevdocs.com',
  site_url: 'https://thaidevdocs.com',
  show_reading_time: true,
  show_difficulty: true,
  show_author: true,
  show_last_updated: true,

  // SEO
  default_meta_title: 'Thai Developer Docs - เอกสารสำหรับนักพัฒนาไทย',
  default_meta_description: 'เรียนรู้การพัฒนาเว็บไซต์ด้วยเอกสารภาษาไทยที่เข้าใจง่าย พร้อมตัวอย่างโค้ดและแบบฝึกหัด',
  google_analytics_id: '',
  google_verification: '',
  og_image: '',
  twitter_handle: '',
  facebook_url: '',
  github_url: '',

  // Comments
  comments_enabled: true,
  comments_require_login: true,
  comments_require_approval: false,
  comments_allow_replies: true,
  comment_max_length: 2000,
  comment_max_depth: 3,

  // Contributions
  contributions_enabled: true,
  contributions_require_login: true,
  points_edit_approved: 10,
  points_new_article: 50,
  points_helpful_comment: 5,

  // Email
  smtp_host: '',
  smtp_port: 587,
  smtp_username: '',
  smtp_password: '',
  mail_from_address: '',
  mail_from_name: '',
})

onMounted(fetchSettings)

async function fetchSettings() {
  try {
    const response = await api.get<any>('/admin/settings')
    Object.assign(settings, response.data)
  } catch (error) {
    console.error('Failed to fetch settings:', error)
  }
}

async function saveSettings() {
  isSaving.value = true
  try {
    await api.put('/admin/settings', settings)
    alert('บันทึกการตั้งค่าเรียบร้อยแล้ว')
  } catch (error: any) {
    console.error('Failed to save settings:', error)
    alert(error.response?.data?.error?.message || 'เกิดข้อผิดพลาด')
  } finally {
    isSaving.value = false
  }
}

async function testEmail() {
  testingEmail.value = true
  try {
    await api.post('/admin/settings/test-email')
    alert('ส่งอีเมลทดสอบเรียบร้อยแล้ว')
  } catch (error: any) {
    console.error('Failed to send test email:', error)
    alert(error.response?.data?.error?.message || 'ไม่สามารถส่งอีเมลได้')
  } finally {
    testingEmail.value = false
  }
}
</script>
