<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">หมวดหมู่</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการหมวดหมู่เอกสาร</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        เพิ่มหมวดหมู่
      </button>
    </div>

    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>ชื่อหมวดหมู่</th>
            <th>Slug</th>
            <th>หัวข้อ</th>
            <th>สถานะ</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in categories" :key="category.id">
            <td>
              <div class="flex items-center gap-3">
                <div
                  v-if="category.color"
                  class="w-8 h-8 rounded-lg flex items-center justify-center"
                  :style="{ backgroundColor: category.color + '20' }"
                >
                  <span :style="{ color: category.color }">{{ category.icon || '📁' }}</span>
                </div>
                <span class="font-medium">{{ category.name }}</span>
              </div>
            </td>
            <td class="text-gray-500">{{ category.slug }}</td>
            <td>{{ category.topics_count }}</td>
            <td>
              <span :class="category.is_active ? 'badge-success' : 'badge-danger'">
                {{ category.is_active ? 'เปิด' : 'ปิด' }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openModal(category)"
                  class="p-2 text-gray-500 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>
                <button
                  @click="confirmDelete(category)"
                  class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
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
                  {{ editingCategory ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่' }}
                </DialogTitle>

                <form @submit.prevent="saveCategory" class="mt-4 space-y-4">
                  <div>
                    <label class="label">ชื่อหมวดหมู่ (ภาษาไทย)</label>
                    <input v-model="form.name" type="text" class="input" required />
                  </div>
                  <div>
                    <label class="label">ชื่อหมวดหมู่ (ภาษาอังกฤษ)</label>
                    <input v-model="form.name_en" type="text" class="input" required />
                  </div>
                  <div>
                    <label class="label">คำอธิบาย</label>
                    <textarea v-model="form.description" class="input resize-none h-20" />
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="label">ไอคอน</label>
                      <input v-model="form.icon" type="text" class="input" placeholder="📁" />
                    </div>
                    <div>
                      <label class="label">สี</label>
                      <input v-model="form.color" type="color" class="input h-10" />
                    </div>
                  </div>
                  <label class="flex items-center gap-3">
                    <input
                      type="checkbox"
                      v-model="form.is_active"
                      class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300">เปิดใช้งาน</span>
                  </label>

                  <div class="flex justify-end gap-3">
                    <button type="button" @click="modalOpen = false" class="btn-secondary">
                      ยกเลิก
                    </button>
                    <button type="submit" class="btn-primary">
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
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

const categories = ref<any[]>([])
const modalOpen = ref(false)
const editingCategory = ref<any>(null)

const form = reactive({
  name: '',
  name_en: '',
  description: '',
  icon: '',
  color: '#6366F1',
  is_active: true,
})

onMounted(fetchCategories)

async function fetchCategories() {
  try {
    const response = await api.get<any>('/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

function openModal(category?: any) {
  editingCategory.value = category
  if (category) {
    Object.assign(form, {
      name: category.name,
      name_en: category.name_en,
      description: category.description || '',
      icon: category.icon || '',
      color: category.color || '#6366F1',
      is_active: category.is_active,
    })
  } else {
    Object.assign(form, {
      name: '',
      name_en: '',
      description: '',
      icon: '',
      color: '#6366F1',
      is_active: true,
    })
  }
  modalOpen.value = true
}

async function saveCategory() {
  try {
    if (editingCategory.value) {
      await api.put(`/admin/categories/${editingCategory.value.id}`, form)
    } else {
      await api.post('/admin/categories', form)
    }
    modalOpen.value = false
    await fetchCategories()
  } catch (error) {
    console.error('Failed to save category:', error)
  }
}

async function confirmDelete(category: any) {
  if (!confirm(`ต้องการลบหมวดหมู่ "${category.name}" หรือไม่?`)) return
  
  try {
    await api.delete(`/admin/categories/${category.id}`)
    await fetchCategories()
  } catch (error: any) {
    alert(error.response?.data?.error?.message || 'ไม่สามารถลบได้')
  }
}
</script>
