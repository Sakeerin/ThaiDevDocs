<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">พจนานุกรม</h1>
        <p class="text-gray-600 dark:text-gray-400">จัดการคำศัพท์ glossary</p>
      </div>
      <button class="btn-primary" @click="openForm()">
        <PlusIcon class="w-5 h-5 mr-2" />
        เพิ่มคำศัพท์
      </button>
    </div>

    <div class="card p-4">
      <input v-model="search" type="text" class="input" placeholder="ค้นหา..." @input="debouncedFetch">
    </div>

    <div class="card overflow-hidden">
      <table class="data-table">
        <thead>
          <tr>
            <th>คำศัพท์</th>
            <th>คำแปล</th>
            <th>สถานะ</th>
            <th class="text-right">การดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="term in terms" :key="term.id">
            <td class="font-medium">{{ term.term }}</td>
            <td class="max-w-md truncate">{{ term.definition }}</td>
            <td>
              <span :class="term.is_approved ? 'badge-success' : 'badge-warning'">
                {{ term.is_approved ? 'อนุมัติ' : 'รออนุมัติ' }}
              </span>
            </td>
            <td class="text-right">
              <button class="p-2 text-gray-500 hover:text-primary-600" @click="openForm(term)">
                <PencilIcon class="w-5 h-5" />
              </button>
              <button v-if="!term.is_approved" class="p-2 text-gray-500 hover:text-green-600" @click="approve(term.id)">
                <CheckIcon class="w-5 h-5" />
              </button>
              <button class="p-2 text-gray-500 hover:text-red-600" @click="remove(term.id)">
                <TrashIcon class="w-5 h-5" />
              </button>
            </td>
          </tr>
          <tr v-if="!terms.length">
            <td colspan="4" class="text-center py-8 text-gray-500">ไม่พบคำศัพท์</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="showForm = false">
      <div class="card max-w-lg w-full p-6 space-y-4">
        <h2 class="text-lg font-semibold">{{ editingId ? 'แก้ไขคำศัพท์' : 'เพิ่มคำศัพท์' }}</h2>
        <div>
          <label class="label">คำศัพท์</label>
          <input v-model="form.term" type="text" class="input" required>
        </div>
        <div>
          <label class="label">คำแปล / คำอธิบาย</label>
          <textarea v-model="form.definition" class="input h-24 resize-none" required />
        </div>
        <div>
          <label class="label">คำศัพท์ (EN)</label>
          <input v-model="form.term_en" type="text" class="input">
        </div>
        <label class="flex items-center gap-2">
          <input v-model="form.is_approved" type="checkbox" class="rounded">
          อนุมัติทันที
        </label>
        <div class="flex justify-end gap-2">
          <button class="btn-secondary" @click="showForm = false">ยกเลิก</button>
          <button class="btn-primary" @click="save">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { PlusIcon, PencilIcon, TrashIcon, CheckIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

const terms = ref<any[]>([])
const search = ref('')
const showForm = ref(false)
const editingId = ref<number | null>(null)
const form = reactive({
  term: '',
  term_en: '',
  definition: '',
  is_approved: true,
})
let timeout: ReturnType<typeof setTimeout>

onMounted(fetchTerms)

async function fetchTerms() {
  const response = await api.get<any>('/admin/glossary', {
    params: { search: search.value || undefined, per_page: 100 },
  })
  terms.value = response.data
}

function debouncedFetch() {
  clearTimeout(timeout)
  timeout = setTimeout(fetchTerms, 300)
}

function openForm(term?: any) {
  if (term) {
    editingId.value = term.id
    Object.assign(form, {
      term: term.term,
      term_en: term.term_en || '',
      definition: term.definition,
      is_approved: term.is_approved,
    })
  } else {
    editingId.value = null
    Object.assign(form, { term: '', term_en: '', definition: '', is_approved: true })
  }
  showForm.value = true
}

async function save() {
  if (editingId.value) {
    await api.put(`/admin/glossary/${editingId.value}`, form)
  } else {
    await api.post('/admin/glossary', form)
  }
  showForm.value = false
  await fetchTerms()
}

async function approve(id: number) {
  await api.patch(`/admin/glossary/${id}/approve`)
  await fetchTerms()
}

async function remove(id: number) {
  if (!confirm('ต้องการลบคำศัพท์นี้หรือไม่?')) return
  await api.delete(`/admin/glossary/${id}`)
  await fetchTerms()
}
</script>
