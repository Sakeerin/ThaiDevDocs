<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">แท็ก</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการแท็กบทความ</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        เพิ่มแท็ก
      </button>
    </div>

    <!-- Search -->
    <div class="card p-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="ค้นหาแท็ก..."
        class="input w-full max-w-md"
        @input="debouncedSearch"
      />
    </div>

    <!-- Tags Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div
        v-for="tag in tags"
        :key="tag.id"
        class="card p-4 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h3 class="font-medium text-gray-900 dark:text-white">{{ tag.name }}</h3>
            <p v-if="tag.name_en" class="text-sm text-gray-500">{{ tag.name_en }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ tag.articles_count }} บทความ</p>
          </div>
          <div class="flex gap-1">
            <button
              @click="openModal(tag)"
              class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <button
              @click="confirmDelete(tag)"
              class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div
          v-if="tag.color"
          class="mt-3 h-1 rounded-full"
          :style="{ backgroundColor: tag.color }"
        />
      </div>
    </div>

    <div v-if="tags.length === 0" class="card p-12 text-center text-gray-500">
      <TagIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
      <p>ไม่พบแท็ก</p>
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
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-xl transition-all">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ editingTag ? 'แก้ไขแท็ก' : 'เพิ่มแท็ก' }}
                </DialogTitle>

                <form @submit.prevent="saveTag" class="mt-4 space-y-4">
                  <div>
                    <label class="label">ชื่อแท็ก (ภาษาไทย) <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="input" required />
                  </div>
                  <div>
                    <label class="label">ชื่อแท็ก (ภาษาอังกฤษ)</label>
                    <input v-model="form.name_en" type="text" class="input" />
                  </div>
                  <div>
                    <label class="label">คำอธิบาย</label>
                    <textarea v-model="form.description" class="input resize-none h-20" />
                  </div>
                  <div>
                    <label class="label">สี</label>
                    <div class="flex gap-2">
                      <input v-model="form.color" type="color" class="w-12 h-10 rounded border border-gray-300 cursor-pointer" />
                      <input v-model="form.color" type="text" class="input flex-1" placeholder="#6366F1" />
                    </div>
                  </div>

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
import { PlusIcon, PencilIcon, TrashIcon, TagIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface Tag {
  id: number
  name: string
  name_en?: string
  slug: string
  description?: string
  color?: string
  articles_count: number
}

const tags = ref<Tag[]>([])
const modalOpen = ref(false)
const editingTag = ref<Tag | null>(null)
const isSaving = ref(false)
const searchQuery = ref('')
let searchTimeout: NodeJS.Timeout

const form = reactive({
  name: '',
  name_en: '',
  description: '',
  color: '#6366F1',
})

onMounted(fetchTags)

async function fetchTags() {
  try {
    const response = await api.get<any>('/admin/tags', {
      params: { search: searchQuery.value || undefined }
    })
    tags.value = response.data
  } catch (error) {
    console.error('Failed to fetch tags:', error)
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(fetchTags, 300)
}

function openModal(tag?: Tag) {
  editingTag.value = tag || null
  if (tag) {
    Object.assign(form, {
      name: tag.name,
      name_en: tag.name_en || '',
      description: tag.description || '',
      color: tag.color || '#6366F1',
    })
  } else {
    Object.assign(form, {
      name: '',
      name_en: '',
      description: '',
      color: '#6366F1',
    })
  }
  modalOpen.value = true
}

async function saveTag() {
  isSaving.value = true
  try {
    if (editingTag.value) {
      await api.put(`/admin/tags/${editingTag.value.id}`, form)
    } else {
      await api.post('/admin/tags', form)
    }
    modalOpen.value = false
    await fetchTags()
  } catch (error) {
    console.error('Failed to save tag:', error)
  } finally {
    isSaving.value = false
  }
}

async function confirmDelete(tag: Tag) {
  if (!confirm(`ต้องการลบแท็ก "${tag.name}" หรือไม่?`)) return
  
  try {
    await api.delete(`/admin/tags/${tag.id}`)
    await fetchTags()
  } catch (error: any) {
    alert(error.response?.data?.error?.message || 'ไม่สามารถลบได้')
  }
}
</script>
