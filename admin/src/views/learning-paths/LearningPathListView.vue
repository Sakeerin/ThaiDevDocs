<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">เส้นทางการเรียนรู้</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการ learning paths</p>
      </div>
      <router-link to="/learning-paths/create" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        สร้างเส้นทางใหม่
      </router-link>
    </div>

    <div class="card p-4">
      <input v-model="search" type="text" class="input" placeholder="ค้นหา..." @input="debouncedFetch">
    </div>

    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>ชื่อ</th>
            <th>ระดับ</th>
            <th>บทเรียน</th>
            <th>ผู้เรียน</th>
            <th>สถานะ</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="path in paths" :key="path.id">
            <td>
              <p class="font-medium">{{ path.title }}</p>
              <p class="text-xs text-gray-500">{{ path.slug }}</p>
            </td>
            <td>{{ path.difficulty }}</td>
            <td>{{ path.items_count || 0 }}</td>
            <td>{{ path.enrollment_count || 0 }}</td>
            <td>
              <span :class="path.is_published ? 'badge-success' : 'badge-warning'">
                {{ path.is_published ? 'เผยแพร่' : 'ฉบับร่าง' }}
              </span>
            </td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <router-link :to="`/learning-paths/${path.id}/edit`" class="p-2 text-gray-500 hover:text-primary-600 rounded-lg">
                  <PencilIcon class="w-5 h-5" />
                </router-link>
                <button v-if="!path.is_published" class="p-2 text-gray-500 hover:text-green-600 rounded-lg" @click="publish(path.id)">
                  <CheckIcon class="w-5 h-5" />
                </button>
                <button class="p-2 text-gray-500 hover:text-red-600 rounded-lg" @click="remove(path.id)">
                  <TrashIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!paths.length">
            <td colspan="6" class="text-center py-8 text-gray-500">ไม่พบเส้นทางการเรียนรู้</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { PlusIcon, PencilIcon, TrashIcon, CheckIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

const paths = ref<any[]>([])
const search = ref('')
let timeout: ReturnType<typeof setTimeout>

onMounted(fetchPaths)

async function fetchPaths() {
  const response = await api.get<any>('/admin/learning-paths', {
    params: { search: search.value || undefined, per_page: 50 },
  })
  paths.value = response.data
}

function debouncedFetch() {
  clearTimeout(timeout)
  timeout = setTimeout(fetchPaths, 300)
}

async function publish(id: number) {
  await api.patch(`/admin/learning-paths/${id}/publish`)
  await fetchPaths()
}

async function remove(id: number) {
  if (!confirm('ต้องการลบเส้นทางนี้หรือไม่?')) return
  await api.delete(`/admin/learning-paths/${id}`)
  await fetchPaths()
}
</script>
