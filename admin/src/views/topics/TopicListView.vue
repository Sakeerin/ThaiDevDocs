<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">หัวข้อ</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการหัวข้อเอกสาร</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        เพิ่มหัวข้อ
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="flex gap-4">
        <select v-model="selectedCategory" @change="fetchTopics" class="input w-64">
          <option value="">หมวดหมู่ทั้งหมด</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="ค้นหาหัวข้อ..."
          class="input flex-1"
          @input="debouncedSearch"
        />
      </div>
    </div>

    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>หัวข้อ</th>
            <th>หมวดหมู่</th>
            <th>บทความ</th>
            <th>ลำดับ</th>
            <th>สถานะ</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="topic in topics" :key="topic.id">
            <td>
              <div class="flex items-center gap-3">
                <span v-if="topic.icon" class="text-xl">{{ topic.icon }}</span>
                <div>
                  <p class="font-medium">{{ topic.name }}</p>
                  <p class="text-xs text-gray-500">{{ topic.slug }}</p>
                </div>
              </div>
            </td>
            <td>
              <span class="badge" :style="{ backgroundColor: topic.category?.color + '20', color: topic.category?.color }">
                {{ topic.category?.name }}
              </span>
            </td>
            <td>{{ topic.articles_count }}</td>
            <td>
              <div class="flex items-center gap-1">
                <button
                  @click="moveUp(topic)"
                  :disabled="topic.order_index === 0"
                  class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                >
                  <ChevronUpIcon class="w-4 h-4" />
                </button>
                <span class="text-sm text-gray-500 w-6 text-center">{{ topic.order_index + 1 }}</span>
                <button
                  @click="moveDown(topic)"
                  class="p-1 text-gray-400 hover:text-gray-600"
                >
                  <ChevronDownIcon class="w-4 h-4" />
                </button>
              </div>
            </td>
            <td>
              <span :class="topic.is_active ? 'badge-success' : 'badge-danger'">
                {{ topic.is_active ? 'เปิด' : 'ปิด' }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openModal(topic)"
                  class="p-2 text-gray-500 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>
                <button
                  @click="confirmDelete(topic)"
                  class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="topics.length === 0">
            <td colspan="6" class="text-center py-8 text-gray-500">
              ไม่พบหัวข้อ
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <TransitionRoot appear :show="modalOpen" as="template">
      <Dialog as="div" class="relative z-50" @close="modalOpen = false">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/25 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-xl transition-all">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ editingTopic ? 'แก้ไขหัวข้อ' : 'เพิ่มหัวข้อ' }}
                </DialogTitle>

                <form @submit.prevent="saveTopic" class="mt-4 space-y-4">
                  <div>
                    <label class="label">หมวดหมู่ <span class="text-red-500">*</span></label>
                    <select v-model="form.category_id" class="input" required>
                      <option value="">เลือกหมวดหมู่</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="label">ชื่อหัวข้อ (ภาษาไทย) <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="input" required />
                  </div>
                  <div>
                    <label class="label">ชื่อหัวข้อ (ภาษาอังกฤษ) <span class="text-red-500">*</span></label>
                    <input v-model="form.name_en" type="text" class="input" required />
                  </div>
                  <div>
                    <label class="label">คำอธิบาย</label>
                    <textarea v-model="form.description" class="input resize-none h-20" />
                  </div>
                  <div>
                    <label class="label">ไอคอน</label>
                    <input v-model="form.icon" type="text" class="input" placeholder="📄" />
                  </div>
                  <label class="flex items-center gap-3">
                    <input
                      type="checkbox"
                      v-model="form.is_active"
                      class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">เปิดใช้งาน</span>
                  </label>

                  <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="modalOpen = false" class="btn-secondary">
                      ยกเลิก
                    </button>
                    <button type="submit" class="btn-primary" :disabled="isSaving">
                      บันทึก
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { PlusIcon, PencilIcon, TrashIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface Topic {
  id: number
  name: string
  name_en: string
  slug: string
  description?: string
  icon?: string
  order_index: number
  is_active: boolean
  articles_count: number
  category?: {
    id: number
    name: string
    color?: string
  }
}

const topics = ref<Topic[]>([])
const categories = ref<any[]>([])
const modalOpen = ref(false)
const editingTopic = ref<Topic | null>(null)
const isSaving = ref(false)
const selectedCategory = ref('')
const searchQuery = ref('')
let searchTimeout: NodeJS.Timeout

const form = reactive({
  category_id: '',
  name: '',
  name_en: '',
  description: '',
  icon: '',
  is_active: true,
})

onMounted(async () => {
  await Promise.all([fetchTopics(), fetchCategories()])
})

async function fetchTopics() {
  try {
    const response = await api.get<any>('/admin/topics', {
      params: {
        category_id: selectedCategory.value || undefined,
        search: searchQuery.value || undefined,
      }
    })
    topics.value = response.data
  } catch (error) {
    console.error('Failed to fetch topics:', error)
  }
}

async function fetchCategories() {
  try {
    const response = await api.get<any>('/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchTopics, 300)
}

function openModal(topic?: Topic) {
  editingTopic.value = topic || null
  if (topic) {
    Object.assign(form, {
      category_id: String(topic.category?.id || ''),
      name: topic.name,
      name_en: topic.name_en,
      description: topic.description || '',
      icon: topic.icon || '',
      is_active: topic.is_active,
    })
  } else {
    Object.assign(form, {
      category_id: selectedCategory.value,
      name: '',
      name_en: '',
      description: '',
      icon: '',
      is_active: true,
    })
  }
  modalOpen.value = true
}

async function saveTopic() {
  isSaving.value = true
  try {
    const payload = {
      ...form,
      category_id: Number(form.category_id),
    }

    if (editingTopic.value) {
      await api.put(`/admin/topics/${editingTopic.value.id}`, payload)
    } else {
      await api.post('/admin/topics', payload)
    }
    modalOpen.value = false
    await fetchTopics()
  } catch (error) {
    console.error('Failed to save topic:', error)
  } finally {
    isSaving.value = false
  }
}

async function moveUp(topic: Topic) {
  try {
    await api.post(`/admin/topics/${topic.id}/reorder`, { direction: 'up' })
    await fetchTopics()
  } catch (error) {
    console.error('Failed to reorder:', error)
  }
}

async function moveDown(topic: Topic) {
  try {
    await api.post(`/admin/topics/${topic.id}/reorder`, { direction: 'down' })
    await fetchTopics()
  } catch (error) {
    console.error('Failed to reorder:', error)
  }
}

async function confirmDelete(topic: Topic) {
  if (!confirm(`ต้องการลบหัวข้อ "${topic.name}" หรือไม่?`)) return
  
  try {
    await api.delete(`/admin/topics/${topic.id}`)
    await fetchTopics()
  } catch (error: any) {
    alert(error.response?.data?.error?.message || 'ไม่สามารถลบได้')
  }
}
</script>
