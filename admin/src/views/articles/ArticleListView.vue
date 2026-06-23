<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">บทความ</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการบทความทั้งหมด</p>
      </div>
      <router-link to="/articles/create" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        สร้างบทความใหม่
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="ค้นหาบทความ..."
          class="input"
        />
        <select v-model="filters.status" class="input">
          <option value="">สถานะทั้งหมด</option>
          <option value="draft">ฉบับร่าง</option>
          <option value="pending_review">รอตรวจสอบ</option>
          <option value="published">เผยแพร่แล้ว</option>
          <option value="archived">เก็บถาวร</option>
        </select>
        <select v-model="filters.category_id" class="input">
          <option value="">หมวดหมู่ทั้งหมด</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
        <button @click="fetchArticles" class="btn-secondary">
          <FunnelIcon class="w-5 h-5 mr-2" />
          กรอง
        </button>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedIds.length" class="card p-4 flex flex-wrap items-center gap-3">
      <span class="text-sm text-gray-600 dark:text-gray-400">เลือก {{ selectedIds.length }} รายการ</span>
      <button class="btn-primary btn-sm" @click="bulkAction('publish')">เผยแพร่</button>
      <button class="btn-secondary btn-sm" @click="bulkAction('draft')">ฉบับร่าง</button>
      <button class="btn-secondary btn-sm" @click="bulkAction('pending_review')">รอตรวจ</button>
      <button class="btn-secondary btn-sm" @click="bulkAction('archive')">เก็บถาวร</button>
      <button class="btn-danger btn-sm" @click="bulkAction('delete')">ลบ</button>
      <button
        v-if="selectedIds.length === 1"
        class="btn-outline btn-sm"
        @click="openScheduleModal(selectedIds[0])"
      >
        กำหนดเวลาเผยแพร่
      </button>
    </div>

    <!-- Articles Table -->
    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-10">
              <input
                type="checkbox"
                :checked="allSelected"
                class="rounded"
                @change="toggleSelectAll"
              >
            </th>
            <th>ชื่อบทความ</th>
            <th>หมวดหมู่</th>
            <th>ผู้เขียน</th>
            <th>สถานะ</th>
            <th>วันที่สร้าง</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="article in articles" :key="article.id">
            <td>
              <input
                type="checkbox"
                :checked="selectedIds.includes(article.id)"
                class="rounded"
                @change="toggleSelect(article.id)"
              >
            </td>
            <td>
              <div class="max-w-xs">
                <p class="font-medium truncate">{{ article.title }}</p>
                <p class="text-xs text-gray-500 truncate">{{ article.slug }}</p>
              </div>
            </td>
            <td>{{ article.topic?.category?.name }}</td>
            <td>{{ article.author?.name }}</td>
            <td>
              <span :class="getStatusClass(article.status)">
                {{ getStatusLabel(article.status) }}
              </span>
            </td>
            <td>{{ formatDate(article.created_at) }}</td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <router-link
                  :to="`/articles/${article.id}/edit`"
                  class="p-2 text-gray-500 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <PencilIcon class="w-5 h-5" />
                </router-link>
                <button
                  @click="duplicateArticle(article.id)"
                  class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <DocumentDuplicateIcon class="w-5 h-5" />
                </button>
                <button
                  @click="openScheduleModal(article.id)"
                  class="p-2 text-gray-500 hover:text-amber-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                  title="กำหนดเวลาเผยแพร่"
                >
                  <ClockIcon class="w-5 h-5" />
                </button>
                <button
                  @click="confirmDelete(article)"
                  class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                >
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="articles.length === 0">
            <td colspan="7" class="text-center py-8 text-gray-500">
              ไม่พบบทความ
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
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
    </div>

    <!-- Schedule Modal -->
    <TransitionRoot appear :show="scheduleModalOpen" as="template">
      <Dialog as="div" class="relative z-50" @close="scheduleModalOpen = false">
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
                  กำหนดเวลาเผยแพร่
                </DialogTitle>
                <div class="mt-4">
                  <label class="label">วันที่และเวลา</label>
                  <input v-model="scheduleAt" type="datetime-local" class="input">
                  <p class="text-xs text-gray-500 mt-2">บทความจะถูกตั้งเป็น "รอตรวจสอบ" จนถึงเวลาที่กำหนด</p>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                  <button @click="scheduleModalOpen = false" class="btn-secondary">ยกเลิก</button>
                  <button @click="saveSchedule" class="btn-primary">บันทึก</button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Delete Modal -->
    <TransitionRoot appear :show="deleteModalOpen" as="template">
      <Dialog as="div" class="relative z-50" @close="deleteModalOpen = false">
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
                  ยืนยันการลบ
                </DialogTitle>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    คุณต้องการลบบทความ "{{ selectedArticle?.title }}" หรือไม่? การดำเนินการนี้ไม่สามารถยกเลิกได้
                  </p>
                </div>

                <div class="mt-4 flex justify-end gap-3">
                  <button @click="deleteModalOpen = false" class="btn-secondary">
                    ยกเลิก
                  </button>
                  <button @click="deleteArticle" class="btn-danger">
                    ลบ
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { PlusIcon, PencilIcon, TrashIcon, DocumentDuplicateIcon, FunnelIcon, ClockIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface Article {
  id: number
  title: string
  slug: string
  status: string
  created_at: string
  topic?: { category?: { name: string } }
  author?: { name: string }
}

const articles = ref<Article[]>([])
const categories = ref<any[]>([])
const pagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0,
  last_page: 1,
})

const filters = reactive({
  search: '',
  status: '',
  category_id: '',
})

const deleteModalOpen = ref(false)
const selectedArticle = ref<Article | null>(null)
const selectedIds = ref<number[]>([])
const scheduleModalOpen = ref(false)
const scheduleArticleId = ref<number | null>(null)
const scheduleAt = ref('')

const allSelected = computed(() =>
  articles.value.length > 0 && selectedIds.value.length === articles.value.length
)

onMounted(async () => {
  await Promise.all([fetchArticles(), fetchCategories()])
})

const fetchArticles = async () => {
  try {
    const response = await api.get<any>('/admin/articles', {
      params: {
        page: pagination.current_page,
        per_page: pagination.per_page,
        ...filters,
      },
    })
    articles.value = response.data
    Object.assign(pagination, response.meta)
    selectedIds.value = selectedIds.value.filter(id => articles.value.some(a => a.id === id))
  } catch (error) {
    console.error('Failed to fetch articles:', error)
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get<any>('/admin/categories', { params: { root_only: true } })
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const confirmDelete = (article: Article) => {
  selectedArticle.value = article
  deleteModalOpen.value = true
}

const deleteArticle = async () => {
  if (!selectedArticle.value) return

  try {
    await api.delete(`/admin/articles/${selectedArticle.value.id}`)
    articles.value = articles.value.filter(a => a.id !== selectedArticle.value!.id)
    deleteModalOpen.value = false
  } catch (error) {
    console.error('Failed to delete article:', error)
  }
}

const duplicateArticle = async (id: number) => {
  try {
    await api.post(`/admin/articles/${id}/duplicate`)
    await fetchArticles()
  } catch (error) {
    console.error('Failed to duplicate article:', error)
  }
}

const toggleSelect = (id: number) => {
  if (selectedIds.value.includes(id)) {
    selectedIds.value = selectedIds.value.filter(item => item !== id)
  } else {
    selectedIds.value = [...selectedIds.value, id]
  }
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = articles.value.map(a => a.id)
  }
}

const bulkAction = async (action: string) => {
  if (!selectedIds.value.length) return
  if (action === 'delete' && !confirm(`ต้องการลบ ${selectedIds.value.length} บทความหรือไม่?`)) return

  try {
    await api.post('/admin/articles/bulk', {
      ids: selectedIds.value,
      action,
    })
    selectedIds.value = []
    await fetchArticles()
  } catch (error) {
    console.error('Failed to bulk action:', error)
  }
}

const openScheduleModal = (id: number) => {
  scheduleArticleId.value = id
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  tomorrow.setMinutes(0, 0, 0)
  scheduleAt.value = tomorrow.toISOString().slice(0, 16)
  scheduleModalOpen.value = true
}

const saveSchedule = async () => {
  if (!scheduleArticleId.value || !scheduleAt.value) return

  try {
    await api.patch(`/admin/articles/${scheduleArticleId.value}/schedule`, {
      published_at: new Date(scheduleAt.value).toISOString(),
    })
    scheduleModalOpen.value = false
    await fetchArticles()
  } catch (error) {
    console.error('Failed to schedule publish:', error)
  }
}

const prevPage = () => {
  if (pagination.current_page > 1) {
    pagination.current_page--
    fetchArticles()
  }
}

const nextPage = () => {
  if (pagination.current_page < pagination.last_page) {
    pagination.current_page++
    fetchArticles()
  }
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    draft: 'ฉบับร่าง',
    pending_review: 'รอตรวจสอบ',
    published: 'เผยแพร่แล้ว',
    archived: 'เก็บถาวร',
  }
  return labels[status] || status
}

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'badge-info',
    pending_review: 'badge-warning',
    published: 'badge-success',
    archived: 'badge-danger',
  }
  return classes[status] || 'badge'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Debounced search
let searchTimeout: NodeJS.Timeout
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.current_page = 1
    fetchArticles()
  }, 500)
})
</script>
