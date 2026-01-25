<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" class="relative z-50" @close="closeModal">
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
            <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-xl transition-all">
              <!-- Header -->
              <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <DialogTitle class="text-lg font-medium text-gray-900 dark:text-white">
                  จัดการสื่อ
                </DialogTitle>
                <button
                  @click="closeModal"
                  class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>

              <!-- Tabs -->
              <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px px-6 gap-4">
                  <button
                    @click="activeTab = 'upload'"
                    :class="[
                      'py-3 px-1 border-b-2 text-sm font-medium transition-colors',
                      activeTab === 'upload'
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                    ]"
                  >
                    <ArrowUpTrayIcon class="w-5 h-5 inline-block mr-2" />
                    อัปโหลด
                  </button>
                  <button
                    @click="activeTab = 'library'"
                    :class="[
                      'py-3 px-1 border-b-2 text-sm font-medium transition-colors',
                      activeTab === 'library'
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                    ]"
                  >
                    <PhotoIcon class="w-5 h-5 inline-block mr-2" />
                    คลังสื่อ
                  </button>
                  <button
                    @click="activeTab = 'url'"
                    :class="[
                      'py-3 px-1 border-b-2 text-sm font-medium transition-colors',
                      activeTab === 'url'
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                    ]"
                  >
                    <LinkIcon class="w-5 h-5 inline-block mr-2" />
                    URL ภายนอก
                  </button>
                </nav>
              </div>

              <!-- Content -->
              <div class="p-6 min-h-[400px]">
                <!-- Upload Tab -->
                <div v-if="activeTab === 'upload'">
                  <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    :class="[
                      'border-2 border-dashed rounded-xl p-12 text-center transition-colors',
                      isDragging
                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                        : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
                    ]"
                  >
                    <CloudArrowUpIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                      ลากไฟล์มาวางที่นี่
                    </p>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                      หรือ
                    </p>
                    <label class="btn-primary cursor-pointer">
                      <input
                        type="file"
                        accept="image/*"
                        multiple
                        class="hidden"
                        @change="handleFileSelect"
                      />
                      เลือกไฟล์
                    </label>
                    <p class="text-sm text-gray-400 mt-4">
                      รองรับ: JPG, PNG, GIF, WebP (สูงสุด 5MB)
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
                </div>

                <!-- Library Tab -->
                <div v-if="activeTab === 'library'">
                  <!-- Search & Filter -->
                  <div class="flex gap-4 mb-6">
                    <div class="flex-1">
                      <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="ค้นหาไฟล์..."
                        class="input"
                      />
                    </div>
                    <select v-model="filterType" class="input w-auto">
                      <option value="">ทุกประเภท</option>
                      <option value="image">รูปภาพ</option>
                      <option value="document">เอกสาร</option>
                      <option value="video">วิดีโอ</option>
                    </select>
                  </div>

                  <!-- Media Grid -->
                  <div v-if="!isLoading" class="grid grid-cols-4 gap-4">
                    <button
                      v-for="media in filteredMedia"
                      :key="media.id"
                      @click="selectMedia(media)"
                      :class="[
                        'relative aspect-square rounded-lg overflow-hidden border-2 transition-all group',
                        selectedMedia?.id === media.id
                          ? 'border-primary-500 ring-2 ring-primary-500/20'
                          : 'border-transparent hover:border-gray-300 dark:hover:border-gray-600'
                      ]"
                    >
                      <img
                        :src="media.thumbnail_url || media.url"
                        :alt="media.alt_text || media.filename"
                        class="w-full h-full object-cover"
                      />
                      <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <EyeIcon class="w-8 h-8 text-white" />
                      </div>
                      <div v-if="selectedMedia?.id === media.id" class="absolute top-2 right-2">
                        <CheckCircleIcon class="w-6 h-6 text-primary-500" />
                      </div>
                    </button>
                  </div>

                  <div v-else class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
                  </div>

                  <div v-if="!isLoading && filteredMedia.length === 0" class="text-center py-12 text-gray-500">
                    ไม่พบสื่อ
                  </div>

                  <!-- Pagination -->
                  <div v-if="mediaPagination.lastPage > 1" class="flex justify-center gap-2 mt-6">
                    <button
                      v-for="page in mediaPagination.lastPage"
                      :key="page"
                      @click="loadMedia(page)"
                      :class="[
                        'px-3 py-1 rounded text-sm',
                        mediaPagination.currentPage === page
                          ? 'bg-primary-500 text-white'
                          : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                      ]"
                    >
                      {{ page }}
                    </button>
                  </div>
                </div>

                <!-- URL Tab -->
                <div v-if="activeTab === 'url'" class="max-w-lg mx-auto">
                  <div class="space-y-4">
                    <div>
                      <label class="label">URL รูปภาพ</label>
                      <input
                        v-model="externalUrl"
                        type="url"
                        class="input"
                        placeholder="https://example.com/image.jpg"
                      />
                    </div>
                    <div>
                      <label class="label">ข้อความอธิบาย (Alt text)</label>
                      <input
                        v-model="externalAlt"
                        type="text"
                        class="input"
                        placeholder="รายละเอียดของรูปภาพ"
                      />
                    </div>

                    <!-- Preview -->
                    <div v-if="externalUrl" class="mt-6">
                      <p class="label mb-2">ตัวอย่าง</p>
                      <div class="aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                        <img
                          :src="externalUrl"
                          :alt="externalAlt"
                          class="w-full h-full object-contain"
                          @error="externalUrlError = true"
                          @load="externalUrlError = false"
                        />
                      </div>
                      <p v-if="externalUrlError" class="text-sm text-red-500 mt-2">
                        ไม่สามารถโหลดรูปภาพได้ กรุณาตรวจสอบ URL
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="text-sm text-gray-500">
                  <template v-if="selectedMedia">
                    เลือก: {{ selectedMedia.filename }}
                  </template>
                  <template v-else-if="activeTab === 'url' && externalUrl && !externalUrlError">
                    URL: {{ externalUrl }}
                  </template>
                </div>
                <div class="flex gap-3">
                  <button @click="closeModal" class="btn-secondary">
                    ยกเลิก
                  </button>
                  <button
                    @click="confirmSelection"
                    :disabled="!canConfirm"
                    class="btn-primary"
                  >
                    แทรกรูปภาพ
                  </button>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import {
  XMarkIcon,
  ArrowUpTrayIcon,
  PhotoIcon,
  LinkIcon,
  CloudArrowUpIcon,
  DocumentIcon,
  CheckCircleIcon,
  XCircleIcon,
  EyeIcon,
} from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface Media {
  id: number
  filename: string
  url: string
  thumbnail_url?: string
  alt_text?: string
  mime_type: string
  size: number
}

interface UploadingFile {
  id: string
  name: string
  preview?: string
  progress: number
  error?: boolean
}

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  'close': []
  'select': [{ url: string; alt: string }]
}>()

const activeTab = ref<'upload' | 'library' | 'url'>('library')
const isDragging = ref(false)
const isLoading = ref(false)

// Upload state
const uploadingFiles = ref<UploadingFile[]>([])

// Library state
const mediaList = ref<Media[]>([])
const selectedMedia = ref<Media | null>(null)
const searchQuery = ref('')
const filterType = ref('')
const mediaPagination = ref({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

// URL state
const externalUrl = ref('')
const externalAlt = ref('')
const externalUrlError = ref(false)

const filteredMedia = computed(() => {
  let filtered = mediaList.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(m =>
      m.filename.toLowerCase().includes(query) ||
      m.alt_text?.toLowerCase().includes(query)
    )
  }

  if (filterType.value) {
    filtered = filtered.filter(m => {
      if (filterType.value === 'image') return m.mime_type.startsWith('image/')
      if (filterType.value === 'document') return m.mime_type.includes('pdf') || m.mime_type.includes('document')
      if (filterType.value === 'video') return m.mime_type.startsWith('video/')
      return true
    })
  }

  return filtered
})

const canConfirm = computed(() => {
  if (activeTab.value === 'library') return !!selectedMedia.value
  if (activeTab.value === 'url') return !!externalUrl.value && !externalUrlError.value
  return false
})

watch(() => props.isOpen, (open) => {
  if (open) {
    loadMedia()
  } else {
    // Reset state
    selectedMedia.value = null
    externalUrl.value = ''
    externalAlt.value = ''
    uploadingFiles.value = []
  }
})

const loadMedia = async (page = 1) => {
  isLoading.value = true
  try {
    const response = await api.get<any>('/admin/media', {
      params: { page, per_page: 20 }
    })
    mediaList.value = response.data
    mediaPagination.value = {
      currentPage: response.meta?.current_page || 1,
      lastPage: response.meta?.last_page || 1,
      total: response.meta?.total || 0,
    }
  } catch (error) {
    console.error('Failed to load media:', error)
  } finally {
    isLoading.value = false
  }
}

const handleFileSelect = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files) {
    uploadFiles(Array.from(input.files))
  }
}

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  if (e.dataTransfer?.files) {
    uploadFiles(Array.from(e.dataTransfer.files))
  }
}

const uploadFiles = async (files: File[]) => {
  for (const file of files) {
    if (!file.type.startsWith('image/')) continue
    if (file.size > 5 * 1024 * 1024) continue // 5MB limit

    const uploadId = crypto.randomUUID()
    const uploadingFile: UploadingFile = {
      id: uploadId,
      name: file.name,
      preview: URL.createObjectURL(file),
      progress: 0,
    }

    uploadingFiles.value.push(uploadingFile)

    try {
      const response = await api.upload<{ data: Media }>('/admin/media', file, (progress) => {
        const idx = uploadingFiles.value.findIndex(f => f.id === uploadId)
        if (idx !== -1) {
          uploadingFiles.value[idx].progress = progress
        }
      })

      // Add to media list
      mediaList.value.unshift(response.data)

      // Auto-select the uploaded media
      selectedMedia.value = response.data
      activeTab.value = 'library'
    } catch (error) {
      console.error('Upload failed:', error)
      const idx = uploadingFiles.value.findIndex(f => f.id === uploadId)
      if (idx !== -1) {
        uploadingFiles.value[idx].error = true
      }
    }
  }
}

const selectMedia = (media: Media) => {
  selectedMedia.value = media
}

const confirmSelection = () => {
  if (activeTab.value === 'library' && selectedMedia.value) {
    emit('select', {
      url: selectedMedia.value.url,
      alt: selectedMedia.value.alt_text || selectedMedia.value.filename,
    })
  } else if (activeTab.value === 'url' && externalUrl.value) {
    emit('select', {
      url: externalUrl.value,
      alt: externalAlt.value || 'image',
    })
  }
  closeModal()
}

const closeModal = () => {
  emit('close')
}
</script>
