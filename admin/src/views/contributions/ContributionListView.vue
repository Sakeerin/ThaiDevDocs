<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">การมีส่วนร่วม</h1>
      <p class="text-gray-600 dark:text-gray-400">ตรวจสอบ contributions จากผู้ใช้</p>
    </div>

    <div class="card p-4">
      <select v-model="filters.status" class="input w-48" @change="fetchContributions">
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
            <th>ผู้ใช้</th>
            <th>ประเภท</th>
            <th>รายละเอียด</th>
            <th>คะแนน</th>
            <th>สถานะ</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in contributions" :key="item.id">
            <td>{{ item.user?.name || '—' }}</td>
            <td>{{ item.type }}</td>
            <td class="max-w-xs truncate">{{ item.description || '—' }}</td>
            <td>{{ item.points || 0 }}</td>
            <td><span :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span></td>
            <td class="text-right">
              <template v-if="item.status === 'pending'">
                <button class="btn-primary btn-sm mr-2" @click="review(item.id, 'approved')">อนุมัติ</button>
                <button class="btn-secondary btn-sm" @click="review(item.id, 'rejected')">ปฏิเสธ</button>
              </template>
            </td>
          </tr>
          <tr v-if="!contributions.length">
            <td colspan="6" class="text-center py-8 text-gray-500">ไม่พบรายการ</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const contributions = ref<any[]>([])
const filters = reactive({ status: 'pending' })

onMounted(fetchContributions)

async function fetchContributions() {
  const endpoint = filters.status === 'pending'
    ? '/admin/contributions/pending'
    : '/admin/contributions'
  const response = await api.get<any>(endpoint, {
    params: filters.status && filters.status !== 'pending' ? { status: filters.status } : {},
  })
  contributions.value = response.data
}

async function review(id: number, status: string) {
  await api.patch(`/admin/contributions/${id}/review`, { status })
  await fetchContributions()
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
</script>
