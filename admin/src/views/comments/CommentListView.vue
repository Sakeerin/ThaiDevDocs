<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ความคิดเห็น</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการความคิดเห็นทั้งหมด</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="ค้นหาความคิดเห็น..."
          class="input"
          @input="debouncedSearch"
        />
        <select v-model="filters.status" @change="fetchComments" class="input">
          <option value="">ทุกสถานะ</option>
          <option value="pending">รอตรวจสอบ</option>
          <option value="approved">อนุมัติแล้ว</option>
          <option value="spam">สแปม</option>
          <option value="rejected">ปฏิเสธ</option>
        </select>
        <select v-model="filters.has_reports" @change="fetchComments" class="input">
          <option value="">ทั้งหมด</option>
          <option value="true">มีการรายงาน</option>
        </select>
        <div class="flex gap-2">
          <button @click="bulkApprove" :disabled="selectedComments.length === 0" class="btn-success btn-sm flex-1">
            อนุมัติ ({{ selectedComments.length }})
          </button>
          <button @click="bulkReject" :disabled="selectedComments.length === 0" class="btn-danger btn-sm flex-1">
            ปฏิเสธ ({{ selectedComments.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="card p-4 border-l-4 border-amber-500">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pending }}</p>
        <p class="text-sm text-gray-500">รอตรวจสอบ</p>
      </div>
      <div class="card p-4 border-l-4 border-emerald-500">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.approved }}</p>
        <p class="text-sm text-gray-500">อนุมัติแล้ว</p>
      </div>
      <div class="card p-4 border-l-4 border-red-500">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.reported }}</p>
        <p class="text-sm text-gray-500">ถูกรายงาน</p>
      </div>
      <div class="card p-4 border-l-4 border-gray-500">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        <p class="text-sm text-gray-500">ทั้งหมด</p>
      </div>
    </div>

    <!-- Comments List -->
    <div class="space-y-4">
      <div
        v-for="comment in comments"
        :key="comment.id"
        :class="['card p-4', comment.status === 'pending' ? 'border-l-4 border-amber-500' : '']"
      >
        <div class="flex items-start gap-4">
          <input
            type="checkbox"
            v-model="selectedComments"
            :value="comment.id"
            class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white font-medium shrink-0">
            {{ comment.user?.name.charAt(0).toUpperCase() || 'G' }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-medium text-gray-900 dark:text-white">
                {{ comment.user?.name || 'ผู้เยี่ยมชม' }}
              </span>
              <span :class="getStatusBadgeClass(comment.status)">
                {{ getStatusLabel(comment.status) }}
              </span>
              <span v-if="comment.reports_count > 0" class="badge-danger">
                {{ comment.reports_count }} รายงาน
              </span>
              <span class="text-sm text-gray-500">
                {{ formatDate(comment.created_at) }}
              </span>
            </div>
            <p class="text-gray-700 dark:text-gray-300 mt-2">
              {{ comment.content }}
            </p>
            <div class="mt-2 text-sm">
              <router-link
                :to="`/articles/${comment.article_id}/edit`"
                class="text-primary-600 hover:underline"
              >
                {{ comment.article?.title }}
              </router-link>
            </div>
            
            <!-- Reply -->
            <div v-if="comment.parent" class="mt-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
              <p class="text-sm text-gray-500">ตอบกลับถึง:</p>
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ comment.parent.content }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 mt-3">
              <button
                v-if="comment.status !== 'approved'"
                @click="approveComment(comment)"
                class="text-emerald-600 hover:text-emerald-700 text-sm font-medium"
              >
                อนุมัติ
              </button>
              <button
                v-if="comment.status !== 'rejected'"
                @click="rejectComment(comment)"
                class="text-red-600 hover:text-red-700 text-sm font-medium"
              >
                ปฏิเสธ
              </button>
              <button
                v-if="comment.status !== 'spam'"
                @click="markAsSpam(comment)"
                class="text-gray-500 hover:text-gray-700 text-sm"
              >
                ทำเครื่องหมายเป็นสแปม
              </button>
              <button
                @click="deleteComment(comment)"
                class="text-red-600 hover:text-red-700 text-sm ml-auto"
              >
                ลบ
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="comments.length === 0" class="card p-12 text-center text-gray-500">
        <ChatBubbleLeftRightIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
        <p>ไม่พบความคิดเห็น</p>
      </div>
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
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface Comment {
  id: number
  content: string
  status: string
  created_at: string
  reports_count: number
  article_id: number
  user?: { name: string }
  article?: { title: string }
  parent?: { content: string }
}

const comments = ref<Comment[]>([])
const selectedComments = ref<number[]>([])
let searchTimeout: NodeJS.Timeout

const filters = reactive({
  search: '',
  status: '',
  has_reports: '',
})

const pagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0,
  last_page: 1,
})

const stats = reactive({
  total: 0,
  pending: 0,
  approved: 0,
  reported: 0,
})

onMounted(async () => {
  await Promise.all([fetchComments(), fetchStats()])
})

async function fetchComments() {
  try {
    const response = await api.get<any>('/admin/comments', {
      params: {
        page: pagination.current_page,
        per_page: pagination.per_page,
        ...filters,
      }
    })
    comments.value = response.data
    Object.assign(pagination, response.meta)
    selectedComments.value = []
  } catch (error) {
    console.error('Failed to fetch comments:', error)
  }
}

async function fetchStats() {
  try {
    const response = await api.get<any>('/admin/comments/stats')
    Object.assign(stats, response.data)
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.current_page = 1
    fetchComments()
  }, 300)
}

async function approveComment(comment: Comment) {
  try {
    await api.post(`/admin/comments/${comment.id}/approve`)
    await fetchComments()
    await fetchStats()
  } catch (error) {
    console.error('Failed to approve comment:', error)
  }
}

async function rejectComment(comment: Comment) {
  try {
    await api.post(`/admin/comments/${comment.id}/reject`)
    await fetchComments()
    await fetchStats()
  } catch (error) {
    console.error('Failed to reject comment:', error)
  }
}

async function markAsSpam(comment: Comment) {
  try {
    await api.post(`/admin/comments/${comment.id}/spam`)
    await fetchComments()
    await fetchStats()
  } catch (error) {
    console.error('Failed to mark as spam:', error)
  }
}

async function deleteComment(comment: Comment) {
  if (!confirm('ต้องการลบความคิดเห็นนี้หรือไม่?')) return
  
  try {
    await api.delete(`/admin/comments/${comment.id}`)
    await fetchComments()
    await fetchStats()
  } catch (error) {
    console.error('Failed to delete comment:', error)
  }
}

async function bulkApprove() {
  if (selectedComments.value.length === 0) return
  
  try {
    await api.post('/admin/comments/bulk-approve', { ids: selectedComments.value })
    await fetchComments()
    await fetchStats()
  } catch (error) {
    console.error('Failed to bulk approve:', error)
  }
}

async function bulkReject() {
  if (selectedComments.value.length === 0) return
  
  try {
    await api.post('/admin/comments/bulk-reject', { ids: selectedComments.value })
    await fetchComments()
    await fetchStats()
  } catch (error) {
    console.error('Failed to bulk reject:', error)
  }
}

function prevPage() {
  if (pagination.current_page > 1) {
    pagination.current_page--
    fetchComments()
  }
}

function nextPage() {
  if (pagination.current_page < pagination.last_page) {
    pagination.current_page++
    fetchComments()
  }
}

function getStatusLabel(status: string) {
  const labels: Record<string, string> = {
    pending: 'รอตรวจสอบ',
    approved: 'อนุมัติ',
    rejected: 'ปฏิเสธ',
    spam: 'สแปม',
  }
  return labels[status] || status
}

function getStatusBadgeClass(status: string) {
  const classes: Record<string, string> = {
    pending: 'badge-warning',
    approved: 'badge-success',
    rejected: 'badge-danger',
    spam: 'badge',
  }
  return classes[status] || 'badge'
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
