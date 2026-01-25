<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">คลังสื่อ</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการรูปภาพและไฟล์ทั้งหมด</p>
      </div>
      <button @click="openUploadModal" class="btn-primary">
        <ArrowUpTrayIcon class="w-5 h-5 mr-2" />
        อัปโหลด
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="card p-4">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        <p class="text-sm text-gray-500">ไฟล์ทั้งหมด</p>
      </div>
      <div class="card p-4">
        <p class="text-2xl font-bold text-blue-600">{{ stats.images }}</p>
        <p class="text-sm text-gray-500">รูปภาพ</p>
      </div>
      <div class="card p-4">
        <p class="text-2xl font-bold text-purple-600">{{ stats.documents }}</p>
        <p class="text-sm text-gray-500">เอกสาร</p>
      </div>
      <div class="card p-4">
        <p class="text-2xl font-bold text-emerald-600">{{ formatFileSize(stats.total_size) }}</p>
        <p class="text-sm text-gray-500">พื้นที่ใช้งาน</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="flex flex-wrap gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="ค้นหาไฟล์..."
          class="input w-64"
          @input="debouncedSearch"
        />
        <select v-model="filters.type" @change="fetchMedia" class="input w-auto">
          <option value="">ทุกประเภท</option>
          <option value="image">รูปภาพ</option>
          <option value="document">เอกสาร</option>
          <option value="video">วิดีโอ</option>
          <option value="audio">เสียง</option>
        </select>
        <div class="flex gap-2 ml-auto">
          <button
            @click="viewMode = 'grid'"
            :class="['p-2 rounded-lg', viewMode === 'grid' ? 'bg-primary-100 text-primary-600' : 'text-gray-500 hover:bg-gray-100']"
          >
            <Squares2X2Icon class="w-5 h-5" />
          </button>
          <button
            @click="viewMode = 'list'"
            :class="['p-2 rounded-lg', viewMode === 'list' ? 'bg-primary-100 text-primary-600' : 'text-gray-500 hover:bg-gray-100']"
          >
            <ListBulletIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Grid View -->
    <div v-if="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div
        v-for="media in mediaList"
        :key="media.id"
        @click="selectMedia(media)"
        :class="[
          'relative aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all group',
          selectedMedia?.id === media.id
            ? 'border-primary-500 ring-2 ring-primary-500/20'
            : 'border-transparent hover:border-gray-300'
        ]"
      >
        <img
          v-if="media.mime_type.startsWith('image/')"
          :src="media.thumbnail_url || media.url"
          :alt="media.alt_text || media.filename"
          class="w-full h-full object-cover"
        />
        <div
          v-else
          class="w-full h-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
        >
          <DocumentIcon class="w-12 h-12 text-gray-400" />
        </div>
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
          <div class="flex gap-2">
            <button @click.stop="copyUrl(media)" class="p-2 bg-white/20 rounded-lg hover:bg-white/30">
              <ClipboardDocumentIcon class="w-5 h-5 text-white" />
            </button>
            <button @click.stop="confirmDelete(media)" class="p-2 bg-white/20 rounded-lg hover:bg-red-500">
              <TrashIcon class="w-5 h-5 text-white" />
            </button>
          </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 p-2">
          <p class="text-xs text-white truncate">{{ media.filename }}</p>
        </div>
      </div>
    </div>

    <!-- List View -->
    <div v-else class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>ไฟล์</th>
            <th>ประเภท</th>
            <th>ขนาด</th>
            <th>อัปโหลดโดย</th>
            <th>วันที่</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="media in mediaList" :key="media.id" @click="selectMedia(media)" class="cursor-pointer">
            <td>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 overflow-hidden shrink-0">
                  <img
                    v-if="media.mime_type.startsWith('image/')"
                    :src="media.thumbnail_url || media.url"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center">
                    <DocumentIcon class="w-5 h-5 text-gray-400" />
                  </div>
                </div>
                <div class="min-w-0">
                  <p class="font-medium truncate">{{ media.filename }}</p>
                  <p class="text-xs text-gray-500">{{ media.alt_text || '-' }}</p>
                </div>
              </div>
            </td>
            <td class="text-gray-500">{{ media.mime_type.split('/')[1]?.toUpperCase() }}</td>
            <td class="text-gray-500">{{ formatFileSize(media.size) }}</td>
            <td class="text-gray-500">{{ media.uploader?.name || '-' }}</td>
            <td class="text-gray-500">{{ formatDate(media.created_at) }}</td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click.stop="copyUrl(media)"
                  class="p-2 text-gray-500 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                  title="คัดลอก URL"
                >
                  <ClipboardDocumentIcon class="w-5 h-5" />
                </button>
                <button
                  @click.stop="confirmDelete(media)"
                  class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                  title="ลบ"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="mediaList.length === 0 && !isLoading" class="card p-12 text-center text-gray-500">
      <PhotoIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
      <p>ไม่พบไฟล์</p>
      <button @click="openUploadModal" class="btn-primary mt-4">
        อัปโหลดไฟล์แรก
      </button>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > 0" class="flex items-center justify-between">
      <p class="text-sm text-gray-500">
        แสดง {{ pagination.from }} - {{ pagination.to }} จาก {{ pagination.total }} รายการ
      </p>
      <div class="flex gap-2">
        <button
          @click="prevPage"
          :disabled="pagination.current_page === 1"
          class="btn-outline btn-sm"
        >
          ก่อนหน้า
        </button>
        <button
          @click="nextPage"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn-outline btn-sm"
        >
          ถัดไป
        </button>
      </div>
    </div>

    <!-- Detail Sidebar -->
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="selectedMedia"
        class="fixed right-0 top-0 bottom-0 w-96 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-xl z-40 overflow-y-auto"
      >
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="font-medium text-gray-900 dark:text-white">รายละเอียดไฟล์</h3>
            <button @click="selectedMedia = null" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <!-- Preview -->
          <div class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden mb-6">
            <img
              v-if="selectedMedia.mime_type.startsWith('image/')"
              :src="selectedMedia.url"
              class="w-full h-full object-contain"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <DocumentIcon class="w-16 h-16 text-gray-400" />
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-4">
            <div>
              <label class="label">ชื่อไฟล์</label>
              <p class="text-gray-900 dark:text-white">{{ selectedMedia.filename }}</p>
            </div>
            <div>
              <label class="label">URL</label>
              <div class="flex gap-2">
                <input :value="selectedMedia.url" class="input text-sm flex-1" readonly />
                <button @click="copyUrl(selectedMedia)" class="btn-outline btn-sm">
                  คัดลอก
                </button>
              </div>
            </div>
            <div>
              <label class="label">Alt Text</label>
              <input
                v-model="editForm.alt_text"
                type="text"
                class="input"
                placeholder="คำอธิบายรูปภาพ"
              />
            </div>
            <div>
              <label class="label">Title</label>
              <input
                v-model="editForm.title"
                type="text"
                class="input"
                placeholder="ชื่อไฟล์"
              />
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500">ประเภท</p>
                <p class="text-gray-900 dark:text-white">{{ selectedMedia.mime_type }}</p>
              </div>
              <div>
                <p class="text-gray-500">ขนาด</p>
                <p class="text-gray-900 dark:text-white">{{ formatFileSize(selectedMedia.size) }}</p>
              </div>
              <div>
                <p class="text-gray-500">มิติ</p>
                <p class="text-gray-900 dark:text-white">{{ selectedMedia.width }}x{{ selectedMedia.height }}</p>
              </div>
              <div>
                <p class="text-gray-500">อัปโหลด</p>
                <p class="text-gray-900 dark:text-white">{{ formatDate(selectedMedia.created_at) }}</p>
              </div>
            </div>

            <div class="flex gap-2 pt-4">
              <button @click="updateMedia" class="btn-primary flex-1">บันทึก</button>
              <button @click="confirmDelete(selectedMedia)" class="btn-danger">ลบ</button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Upload Modal -->
    <TransitionRoot appear :show="uploadModalOpen" as="template">
      <Dialog as="div" class="relative z-50" @close="uploadModalOpen = false">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
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
              <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-xl transition-all">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-white">
                  อัปโหลดไฟล์
                </DialogTitle>

                <div
                  @dragover.prevent="isDragging = true"
                  @dragleave.prevent="isDragging = false"
                  @drop.prevent="handleDrop"
                  :class="[
                    'mt-4 border-2 border-dashed rounded-xl p-12 text-center transition-colors',
                    isDragging
                      ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                      : 'border-gray-300 dark:border-gray-600'
                  ]"
                >
                  <CloudArrowUpIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                  <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    ลากไฟล์มาวางที่นี่
                  </p>
                  <p class="text-gray-500 mb-4">หรือ</p>
                  <label class="btn-primary cursor-pointer">
                    <input
                      type="file"
                      accept="image/*,application/pdf,.doc,.docx"
                      multiple
                      class="hidden"
                      @change="handleFileSelect"
                    />
                    เลือกไฟล์
                  </label>
                  <p class="text-sm text-gray-400 mt-4">
                    รองรับ: JPG, PNG, GIF, WebP, PDF (สูงสุด 10MB)
                  </p>
                </div>

                <!-- Upload Progress -->
                <div v-if="uploadingFiles.length > 0" class="mt-6 space-y-3">
                  <div
                    v-for="file in uploadingFiles"
                    :key="file.id"
                    class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                  >
                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center overflow-hidden">
                      <img v-if="file.preview" :src="file.preview" class="w-full h-full object-cover" />
                      <DocumentIcon v-else class="w-6 h-6 text-gray-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ file.name }}
                      </p>
                      <div class="mt-1 h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div
                          class="h-full bg-primary-500 transition-all duration-300"
                          :style="{ width: `${file.progress}%` }"
                        />
                      </div>
                    </div>
                    <span v-if="file.progress === 100" class="text-emerald-500">
                      <CheckCircleIcon class="w-6 h-6" />
                    </span>
                    <span v-else-if="file.error" class="text-red-500">
                      <XCircleIcon class="w-6 h-6" />
                    </span>
                    <span v-else class="text-sm text-gray-500">{{ file.progress }}%</span>
                  </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                  <button @click="uploadModalOpen = false" class="btn-secondary">
                    ปิด
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import {
  ArrowUpTrayIcon,
  Squares2X2Icon,
  ListBulletIcon,
  PhotoIcon,
  DocumentIcon,
  TrashIcon,
  ClipboardDocumentIcon,
  XMarkIcon,
  CloudArrowUpIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface Media {
  id: number
  filename: string
  url: string
  thumbnail_url?: string
  alt_text?: string
  title?: string
  mime_type: string
  size: number
  width?: number
  height?: number
  created_at: string
  uploader?: { name: string }
}

interface UploadingFile {
  id: string
  name: string
  preview?: string
  progress: number
  error?: boolean
}

const mediaList = ref<Media[]>([])
const selectedMedia = ref<Media | null>(null)
const viewMode = ref<'grid' | 'list'>('grid')
const uploadModalOpen = ref(false)
const isDragging = ref(false)
const isLoading = ref(false)
const uploadingFiles = ref<UploadingFile[]>([])
let searchTimeout: NodeJS.Timeout

const filters = reactive({
  search: '',
  type: '',
})

const pagination = reactive({
  current_page: 1,
  per_page: 24,
  total: 0,
  from: 0,
  to: 0,
  last_page: 1,
})

const stats = reactive({
  total: 0,
  images: 0,
  documents: 0,
  total_size: 0,
})

const editForm = reactive({
  alt_text: '',
  title: '',
})

onMounted(async () => {
  await Promise.all([fetchMedia(), fetchStats()])
})

watch(selectedMedia, (media) => {
  if (media) {
    editForm.alt_text = media.alt_text || ''
    editForm.title = media.title || ''
  }
})

async function fetchMedia() {
  isLoading.value = true
  try {
    const response = await api.get<any>('/admin/media', {
      params: {
        page: pagination.current_page,
        per_page: pagination.per_page,
        ...filters,
      }
    })
    mediaList.value = response.data
    Object.assign(pagination, response.meta)
  } catch (error) {
    console.error('Failed to fetch media:', error)
  } finally {
    isLoading.value = false
  }
}

async function fetchStats() {
  try {
    const response = await api.get<any>('/admin/media/stats')
    Object.assign(stats, response.data)
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.current_page = 1
    fetchMedia()
  }, 300)
}

function selectMedia(media: Media) {
  selectedMedia.value = media
}

function openUploadModal() {
  uploadModalOpen.value = true
  uploadingFiles.value = []
}

function handleFileSelect(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files) {
    uploadFiles(Array.from(input.files))
  }
}

function handleDrop(e: DragEvent) {
  isDragging.value = false
  if (e.dataTransfer?.files) {
    uploadFiles(Array.from(e.dataTransfer.files))
  }
}

async function uploadFiles(files: File[]) {
  for (const file of files) {
    if (file.size > 10 * 1024 * 1024) continue // 10MB limit

    const uploadId = crypto.randomUUID()
    const uploadingFile: UploadingFile = {
      id: uploadId,
      name: file.name,
      preview: file.type.startsWith('image/') ? URL.createObjectURL(file) : undefined,
      progress: 0,
    }

    uploadingFiles.value.push(uploadingFile)

    try {
      await api.upload<{ data: Media }>('/admin/media', file, (progress) => {
        const idx = uploadingFiles.value.findIndex(f => f.id === uploadId)
        if (idx !== -1) {
          uploadingFiles.value[idx].progress = progress
        }
      })

      // Refresh list
      await fetchMedia()
      await fetchStats()
    } catch (error) {
      console.error('Upload failed:', error)
      const idx = uploadingFiles.value.findIndex(f => f.id === uploadId)
      if (idx !== -1) {
        uploadingFiles.value[idx].error = true
      }
    }
  }
}

async function updateMedia() {
  if (!selectedMedia.value) return

  try {
    await api.put(`/admin/media/${selectedMedia.value.id}`, editForm)
    selectedMedia.value.alt_text = editForm.alt_text
    selectedMedia.value.title = editForm.title
    await fetchMedia()
  } catch (error) {
    console.error('Failed to update media:', error)
  }
}

async function confirmDelete(media: Media) {
  if (!confirm(`ต้องการลบไฟล์ "${media.filename}" หรือไม่?`)) return
  
  try {
    await api.delete(`/admin/media/${media.id}`)
    if (selectedMedia.value?.id === media.id) {
      selectedMedia.value = null
    }
    await fetchMedia()
    await fetchStats()
  } catch (error) {
    console.error('Failed to delete media:', error)
  }
}

function copyUrl(media: Media) {
  navigator.clipboard.writeText(media.url)
  // Could add a toast notification here
}

function prevPage() {
  if (pagination.current_page > 1) {
    pagination.current_page--
    fetchMedia()
  }
}

function nextPage() {
  if (pagination.current_page < pagination.last_page) {
    pagination.current_page++
    fetchMedia()
  }
}

function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
