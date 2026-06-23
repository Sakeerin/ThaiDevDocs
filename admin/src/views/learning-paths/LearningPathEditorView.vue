<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" @click="router.push('/learning-paths')">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ isEditing ? 'แก้ไขเส้นทาง' : 'สร้างเส้นทางใหม่' }}
          </h1>
        </div>
      </div>
      <button class="btn-primary" :disabled="isSaving" @click="save">
        {{ isSaving ? 'กำลังบันทึก...' : 'บันทึก' }}
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card p-6 space-y-4">
          <div>
            <label class="label">ชื่อเส้นทาง</label>
            <input v-model="form.title" type="text" class="input" required>
          </div>
          <div>
            <label class="label">คำอธิบาย</label>
            <textarea v-model="form.description" class="input h-24 resize-none" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">ระดับ</label>
              <select v-model="form.difficulty" class="input">
                <option value="beginner">เริ่มต้น</option>
                <option value="intermediate">กลาง</option>
                <option value="advanced">สูง</option>
              </select>
            </div>
            <div>
              <label class="label">ชั่วโมงโดยประมาณ</label>
              <input v-model.number="form.estimated_hours" type="number" min="1" class="input">
            </div>
          </div>
        </div>

        <div class="card p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">บทเรียนในเส้นทาง</h2>
            <button type="button" class="btn-secondary btn-sm" @click="addItem">เพิ่มบทเรียน</button>
          </div>
          <div class="space-y-3">
            <div v-for="(item, index) in items" :key="index" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
              <span class="text-sm text-gray-500 w-6">{{ index + 1 }}</span>
              <select v-model="item.article_id" class="input flex-1">
                <option value="">เลือกบทความ</option>
                <option v-for="article in articles" :key="article.id" :value="article.id">
                  {{ article.title }}
                </option>
              </select>
              <label class="flex items-center gap-2 text-sm whitespace-nowrap">
                <input v-model="item.is_required" type="checkbox" class="rounded">
                จำเป็น
              </label>
              <button type="button" class="p-1 text-red-500" @click="items.splice(index, 1)">
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card p-6 space-y-4">
          <label class="flex items-center gap-3">
            <input v-model="form.is_featured" type="checkbox" class="rounded">
            <span>แนะนำ</span>
          </label>
          <label class="flex items-center gap-3">
            <input v-model="form.is_published" type="checkbox" class="rounded">
            <span>เผยแพร่</span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const isEditing = computed(() => !!route.params.id && route.params.id !== 'create')
const isSaving = ref(false)
const articles = ref<any[]>([])

const form = reactive({
  title: '',
  description: '',
  difficulty: 'beginner',
  estimated_hours: 10,
  is_featured: false,
  is_published: false,
})

const items = ref<Array<{ article_id: number | ''; sort_order: number; is_required: boolean }>>([])

onMounted(async () => {
  const response = await api.get<any>('/admin/articles', { params: { per_page: 200, status: 'published' } })
  articles.value = response.data

  if (isEditing.value) {
    const pathResponse = await api.get<any>(`/admin/learning-paths/${route.params.id}`)
    const data = pathResponse.data
    Object.assign(form, {
      title: data.title,
      description: data.description || '',
      difficulty: data.difficulty,
      estimated_hours: data.estimated_hours || 10,
      is_featured: data.is_featured,
      is_published: data.is_published,
    })
    items.value = (data.items || []).map((item: any, index: number) => ({
      article_id: item.article_id,
      sort_order: index,
      is_required: item.is_required,
    }))
  }
})

function addItem() {
  items.value.push({ article_id: '', sort_order: items.value.length, is_required: true })
}

async function save() {
  isSaving.value = true
  const payload = {
    ...form,
    items: items.value
      .filter(item => item.article_id)
      .map((item, index) => ({
        article_id: Number(item.article_id),
        sort_order: index,
        is_required: item.is_required,
      })),
  }

  try {
    if (isEditing.value) {
      await api.put(`/admin/learning-paths/${route.params.id}`, payload)
    } else {
      await api.post('/admin/learning-paths', payload)
    }
    router.push('/learning-paths')
  } finally {
    isSaving.value = false
  }
}
</script>
