<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">คำแนะนำการแก้ไข</h1>
      <p class="text-gray-600 dark:text-gray-400">ตรวจสอบและอนุมัติการแก้ไขจากผู้ใช้</p>
    </div>

    <div class="card p-4 flex flex-wrap gap-4">
      <select v-model="filters.status" class="input w-48" @change="fetchSuggestions">
        <option value="">ทุกสถานะ</option>
        <option value="pending">รอตรวจ</option>
        <option value="approved">อนุมัติ</option>
        <option value="rejected">ปฏิเสธ</option>
      </select>
    </div>

    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>บทความ</th>
            <th>ผู้เสนอ</th>
            <th>สถานะ</th>
            <th>วันที่</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in suggestions" :key="item.id">
            <td>
              <p class="font-medium">{{ item.article?.title || `#${item.article_id}` }}</p>
              <p class="text-xs text-gray-500 truncate max-w-xs">{{ item.reason || '—' }}</p>
            </td>
            <td>{{ item.user?.name || '—' }}</td>
            <td>
              <span :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span>
            </td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td class="text-right">
              <button class="btn-secondary btn-sm mr-2" @click="openDetail(item)">ดูรายละเอียด</button>
              <template v-if="item.status === 'pending'">
                <button class="btn-primary btn-sm mr-2" @click="review(item.id, 'approved', true)">อนุมัติ+นำไปใช้</button>
                <button class="btn-secondary btn-sm" @click="review(item.id, 'rejected')">ปฏิเสธ</button>
              </template>
            </td>
          </tr>
          <tr v-if="!suggestions.length">
            <td colspan="5" class="text-center py-8 text-gray-500">ไม่พบคำแนะนำ</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="selected" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="selected = null">
      <div class="card max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">รายละเอียดคำแนะนำ</h2>
          <button @click="selected = null"><XMarkIcon class="w-6 h-6" /></button>
        </div>
        <div>
          <p class="text-sm text-gray-500 mb-1">เหตุผล</p>
          <p>{{ selected.reason || '—' }}</p>
        </div>
        <div v-if="selected.suggested_content" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm font-medium mb-2">เนื้อหาเดิม</p>
            <pre class="text-xs p-3 bg-gray-100 dark:bg-gray-900 rounded-lg overflow-auto max-h-64">{{ selected.original_content || '—' }}</pre>
          </div>
          <div>
            <p class="text-sm font-medium mb-2">เนื้อหาที่เสนอ</p>
            <pre class="text-xs p-3 bg-gray-100 dark:bg-gray-900 rounded-lg overflow-auto max-h-64">{{ selected.suggested_content }}</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

const suggestions = ref<any[]>([])
const selected = ref<any>(null)
const filters = reactive({ status: 'pending' })

onMounted(fetchSuggestions)

async function fetchSuggestions() {
  const endpoint = filters.status === 'pending'
    ? '/admin/edit-suggestions/pending'
    : '/admin/edit-suggestions'
  const response = await api.get<any>(endpoint, {
    params: filters.status && filters.status !== 'pending' ? { status: filters.status } : {},
  })
  suggestions.value = response.data
}

function openDetail(item: any) {
  selected.value = item
}

async function review(id: number, status: string, applyToArticle = false) {
  await api.patch(`/admin/edit-suggestions/${id}`, {
    status,
    apply_to_article: applyToArticle,
  })
  selected.value = null
  await fetchSuggestions()
}

function statusLabel(status: string) {
  const map: Record<string, string> = { pending: 'รอตรวจ', approved: 'อนุมัติ', rejected: 'ปฏิเสธ' }
  return map[status] || status
}

function statusClass(status: string) {
  const map: Record<string, string> = {
    pending: 'badge-warning',
    approved: 'badge-success',
    rejected: 'badge-danger',
  }
  return map[status] || 'badge'
}

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('th-TH')
}
</script>
