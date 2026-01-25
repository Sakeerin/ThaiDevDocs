<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ผู้ใช้</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการผู้ใช้ระบบ</p>
      </div>
      <button @click="openModal()" class="btn-primary">
        <PlusIcon class="w-5 h-5 mr-2" />
        เพิ่มผู้ใช้
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input
          v-model="filters.search"
          type="text"
          placeholder="ค้นหาชื่อ หรืออีเมล..."
          class="input"
          @input="debouncedSearch"
        />
        <select v-model="filters.role" @change="fetchUsers" class="input">
          <option value="">ทุกบทบาท</option>
          <option value="admin">ผู้ดูแลระบบ</option>
          <option value="editor">บรรณาธิการ</option>
          <option value="contributor">ผู้ร่วมเขียน</option>
          <option value="user">สมาชิก</option>
        </select>
        <select v-model="filters.status" @change="fetchUsers" class="input">
          <option value="">ทุกสถานะ</option>
          <option value="active">ใช้งานอยู่</option>
          <option value="inactive">ปิดใช้งาน</option>
          <option value="banned">ถูกระงับ</option>
        </select>
        <button @click="resetFilters" class="btn-outline">
          รีเซ็ตตัวกรอง
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="card p-4">
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
        <p class="text-sm text-gray-500">ผู้ใช้ทั้งหมด</p>
      </div>
      <div class="card p-4">
        <p class="text-2xl font-bold text-emerald-600">{{ stats.active }}</p>
        <p class="text-sm text-gray-500">ใช้งานอยู่</p>
      </div>
      <div class="card p-4">
        <p class="text-2xl font-bold text-blue-600">{{ stats.new_this_month }}</p>
        <p class="text-sm text-gray-500">ลงทะเบียนเดือนนี้</p>
      </div>
      <div class="card p-4">
        <p class="text-2xl font-bold text-purple-600">{{ stats.contributors }}</p>
        <p class="text-sm text-gray-500">ผู้ร่วมเขียน</p>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>ผู้ใช้</th>
            <th>บทบาท</th>
            <th>บทความ</th>
            <th>แต้มสะสม</th>
            <th>สถานะ</th>
            <th>วันที่สมัคร</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-medium">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>
                </div>
              </div>
            </td>
            <td>
              <span :class="getRoleBadgeClass(user.role)">
                {{ getRoleLabel(user.role) }}
              </span>
            </td>
            <td>{{ user.articles_count }}</td>
            <td>
              <span class="font-medium text-amber-600">{{ user.contribution_points }}</span>
            </td>
            <td>
              <span :class="getStatusBadgeClass(user.status)">
                {{ getStatusLabel(user.status) }}
              </span>
            </td>
            <td class="text-gray-500">{{ formatDate(user.created_at) }}</td>
            <td class="text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="openModal(user)"
                  class="p-2 text-gray-500 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                  title="แก้ไข"
                >
                  <PencilIcon class="w-5 h-5" />
                </button>
                <button
                  v-if="user.status !== 'banned'"
                  @click="banUser(user)"
                  class="p-2 text-gray-500 hover:text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                  title="ระงับ"
                >
                  <NoSymbolIcon class="w-5 h-5" />
                </button>
                <button
                  v-else
                  @click="unbanUser(user)"
                  class="p-2 text-gray-500 hover:text-emerald-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                  title="ยกเลิกการระงับ"
                >
                  <CheckCircleIcon class="w-5 h-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="users.length === 0">
            <td colspan="7" class="text-center py-8 text-gray-500">
              ไม่พบผู้ใช้
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
                  {{ editingUser ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้' }}
                </DialogTitle>

                <form @submit.prevent="saveUser" class="mt-4 space-y-4">
                  <div>
                    <label class="label">ชื่อ <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="input" required />
                  </div>
                  <div>
                    <label class="label">อีเมล <span class="text-red-500">*</span></label>
                    <input v-model="form.email" type="email" class="input" required :disabled="!!editingUser" />
                  </div>
                  <div v-if="!editingUser">
                    <label class="label">รหัสผ่าน <span class="text-red-500">*</span></label>
                    <input v-model="form.password" type="password" class="input" :required="!editingUser" minlength="8" />
                  </div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="label">บทบาท</label>
                      <select v-model="form.role" class="input">
                        <option value="user">สมาชิก</option>
                        <option value="contributor">ผู้ร่วมเขียน</option>
                        <option value="editor">บรรณาธิการ</option>
                        <option value="admin">ผู้ดูแลระบบ</option>
                      </select>
                    </div>
                    <div>
                      <label class="label">สถานะ</label>
                      <select v-model="form.status" class="input">
                        <option value="active">ใช้งานอยู่</option>
                        <option value="inactive">ปิดใช้งาน</option>
                        <option value="banned">ถูกระงับ</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="label">ประวัติ</label>
                    <textarea v-model="form.bio" class="input resize-none h-20" />
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
import { PlusIcon, PencilIcon, NoSymbolIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

interface User {
  id: number
  name: string
  email: string
  role: string
  status: string
  bio?: string
  articles_count: number
  contribution_points: number
  created_at: string
}

const users = ref<User[]>([])
const modalOpen = ref(false)
const editingUser = ref<User | null>(null)
const isSaving = ref(false)
let searchTimeout: NodeJS.Timeout

const filters = reactive({
  search: '',
  role: '',
  status: '',
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
  active: 0,
  new_this_month: 0,
  contributors: 0,
})

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'user',
  status: 'active',
  bio: '',
})

onMounted(async () => {
  await Promise.all([fetchUsers(), fetchStats()])
})

async function fetchUsers() {
  try {
    const response = await api.get<any>('/admin/users', {
      params: {
        page: pagination.current_page,
        per_page: pagination.per_page,
        ...filters,
      }
    })
    users.value = response.data
    Object.assign(pagination, response.meta)
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

async function fetchStats() {
  try {
    const response = await api.get<any>('/admin/users/stats')
    Object.assign(stats, response.data)
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.current_page = 1
    fetchUsers()
  }, 300)
}

function resetFilters() {
  filters.search = ''
  filters.role = ''
  filters.status = ''
  pagination.current_page = 1
  fetchUsers()
}

function openModal(user?: User) {
  editingUser.value = user || null
  if (user) {
    Object.assign(form, {
      name: user.name,
      email: user.email,
      password: '',
      role: user.role,
      status: user.status,
      bio: user.bio || '',
    })
  } else {
    Object.assign(form, {
      name: '',
      email: '',
      password: '',
      role: 'user',
      status: 'active',
      bio: '',
    })
  }
  modalOpen.value = true
}

async function saveUser() {
  isSaving.value = true
  try {
    const payload = { ...form }
    if (!payload.password) {
      delete (payload as any).password
    }

    if (editingUser.value) {
      await api.put(`/admin/users/${editingUser.value.id}`, payload)
    } else {
      await api.post('/admin/users', payload)
    }
    modalOpen.value = false
    await fetchUsers()
    await fetchStats()
  } catch (error: any) {
    console.error('Failed to save user:', error)
    alert(error.response?.data?.error?.message || 'เกิดข้อผิดพลาด')
  } finally {
    isSaving.value = false
  }
}

async function banUser(user: User) {
  if (!confirm(`ต้องการระงับผู้ใช้ "${user.name}" หรือไม่?`)) return
  
  try {
    await api.post(`/admin/users/${user.id}/ban`)
    await fetchUsers()
    await fetchStats()
  } catch (error) {
    console.error('Failed to ban user:', error)
  }
}

async function unbanUser(user: User) {
  try {
    await api.post(`/admin/users/${user.id}/unban`)
    await fetchUsers()
    await fetchStats()
  } catch (error) {
    console.error('Failed to unban user:', error)
  }
}

function prevPage() {
  if (pagination.current_page > 1) {
    pagination.current_page--
    fetchUsers()
  }
}

function nextPage() {
  if (pagination.current_page < pagination.last_page) {
    pagination.current_page++
    fetchUsers()
  }
}

function getRoleLabel(role: string) {
  const labels: Record<string, string> = {
    admin: 'ผู้ดูแล',
    editor: 'บรรณาธิการ',
    contributor: 'ผู้ร่วมเขียน',
    user: 'สมาชิก',
  }
  return labels[role] || role
}

function getRoleBadgeClass(role: string) {
  const classes: Record<string, string> = {
    admin: 'badge-danger',
    editor: 'badge-warning',
    contributor: 'badge-info',
    user: 'badge',
  }
  return classes[role] || 'badge'
}

function getStatusLabel(status: string) {
  const labels: Record<string, string> = {
    active: 'ใช้งาน',
    inactive: 'ปิด',
    banned: 'ระงับ',
  }
  return labels[status] || status
}

function getStatusBadgeClass(status: string) {
  const classes: Record<string, string> = {
    active: 'badge-success',
    inactive: 'badge',
    banned: 'badge-danger',
  }
  return classes[status] || 'badge'
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
