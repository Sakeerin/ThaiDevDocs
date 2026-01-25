<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">การมีส่วนร่วมของฉัน</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">ติดตามการมีส่วนร่วมและแต้มสะสมของคุณ</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
            <Icon name="heroicons:star" class="w-6 h-6 text-amber-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_points }}</p>
            <p class="text-sm text-gray-500">แต้มสะสม</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
            <Icon name="heroicons:check-circle" class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.approved }}</p>
            <p class="text-sm text-gray-500">ได้รับอนุมัติ</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <Icon name="heroicons:clock" class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.pending }}</p>
            <p class="text-sm text-gray-500">รอตรวจสอบ</p>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
            <Icon name="heroicons:trophy" class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">#{{ stats.rank }}</p>
            <p class="text-sm text-gray-500">อันดับ</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Level Progress -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-200 dark:border-slate-700 mb-8">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
          <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold', levelColor]">
            {{ currentLevel }}
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ levelTitle }}</h3>
            <p class="text-gray-500">{{ stats.total_points }} / {{ nextLevelPoints }} แต้ม</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-500">ระดับถัดไป</p>
          <p class="text-lg font-medium text-gray-900 dark:text-white">{{ nextLevelTitle }}</p>
        </div>
      </div>
      <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
        <div
          class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-500"
          :style="{ width: `${levelProgress}%` }"
        />
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 dark:border-slate-700 mb-6">
      <nav class="flex -mb-px gap-6">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'py-3 px-1 border-b-2 text-sm font-medium transition-colors',
            activeTab === tab.id
              ? 'border-primary-500 text-primary-600 dark:text-primary-400'
              : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
          ]"
        >
          {{ tab.label }}
          <span class="ml-2 px-2 py-0.5 bg-gray-100 dark:bg-slate-700 rounded-full text-xs">
            {{ tab.count }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Contributions List -->
    <div v-if="!isLoading" class="space-y-4">
      <div
        v-for="contribution in contributions"
        :key="contribution.id"
        class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4"
      >
        <div class="flex items-start gap-4">
          <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', getTypeColor(contribution.type)]">
            <Icon :name="getTypeIcon(contribution.type)" class="w-5 h-5" />
          </div>
          
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span :class="['px-2 py-0.5 text-xs rounded-full', getStatusClass(contribution.status)]">
                {{ getStatusLabel(contribution.status) }}
              </span>
              <span class="text-sm text-gray-500">{{ formatDate(contribution.created_at) }}</span>
            </div>
            
            <NuxtLink :to="`/docs/${contribution.article?.slug}`" class="text-lg font-medium text-gray-900 dark:text-white hover:text-primary-600">
              {{ contribution.article?.title }}
            </NuxtLink>
            
            <p class="mt-1 text-gray-500 line-clamp-2">{{ contribution.description }}</p>
            
            <div class="mt-3 flex items-center gap-4 text-sm">
              <span class="text-gray-500">{{ getTypeLabel(contribution.type) }}</span>
              <span v-if="contribution.points_earned" class="flex items-center gap-1 text-amber-600">
                <Icon name="heroicons:star" class="w-4 h-4" />
                +{{ contribution.points_earned }} แต้ม
              </span>
              <span v-if="contribution.reviewer" class="text-gray-400">
                ตรวจสอบโดย {{ contribution.reviewer.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && contributions.length === 0" class="text-center py-16">
      <Icon name="heroicons:pencil-square" class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
      <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">ยังไม่มีการมีส่วนร่วม</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">เริ่มต้นโดยการแนะนำแก้ไขบทความ</p>
      <NuxtLink to="/docs" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors">
        สำรวจบทความ
        <Icon name="heroicons:arrow-right" class="w-5 h-5" />
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 animate-pulse">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-gray-200 dark:bg-slate-700 rounded-lg" />
          <div class="flex-1">
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/4 mb-2" />
            <div class="h-6 bg-gray-200 dark:bg-slate-700 rounded w-3/4 mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-full" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { $api } = useNuxtApp()

const contributions = ref<any[]>([])
const isLoading = ref(true)
const activeTab = ref('all')

const stats = reactive({
  total_points: 0,
  approved: 0,
  pending: 0,
  rejected: 0,
  rank: 0,
})

const tabs = computed(() => [
  { id: 'all', label: 'ทั้งหมด', count: contributions.value.length },
  { id: 'pending', label: 'รอตรวจสอบ', count: stats.pending },
  { id: 'approved', label: 'อนุมัติ', count: stats.approved },
  { id: 'rejected', label: 'ไม่อนุมัติ', count: stats.rejected },
])

const levels = [
  { level: 1, points: 0, title: 'มือใหม่' },
  { level: 2, points: 50, title: 'ผู้มีส่วนร่วม' },
  { level: 3, points: 150, title: 'นักเขียน' },
  { level: 4, points: 300, title: 'ผู้เชี่ยวชาญ' },
  { level: 5, points: 500, title: 'ปรมาจารย์' },
]

const currentLevelData = computed(() => {
  return levels.filter(l => l.points <= stats.total_points).pop() || levels[0]
})

const nextLevelData = computed(() => {
  return levels.find(l => l.points > stats.total_points) || levels[levels.length - 1]
})

const currentLevel = computed(() => currentLevelData.value.level)
const levelTitle = computed(() => currentLevelData.value.title)
const nextLevelTitle = computed(() => nextLevelData.value.title)
const nextLevelPoints = computed(() => nextLevelData.value.points)

const levelProgress = computed(() => {
  const current = currentLevelData.value.points
  const next = nextLevelData.value.points
  if (next === current) return 100
  return Math.round(((stats.total_points - current) / (next - current)) * 100)
})

const levelColor = computed(() => {
  const colors = [
    'bg-gray-100 text-gray-600',
    'bg-emerald-100 text-emerald-600',
    'bg-blue-100 text-blue-600',
    'bg-purple-100 text-purple-600',
    'bg-amber-100 text-amber-600',
  ]
  return colors[currentLevel.value - 1] || colors[0]
})

onMounted(async () => {
  await Promise.all([fetchContributions(), fetchStats()])
})

watch(activeTab, fetchContributions)

async function fetchContributions() {
  isLoading.value = true
  try {
    const params = activeTab.value !== 'all' ? { status: activeTab.value } : {}
    const { data } = await $api('/user/contributions', { params })
    contributions.value = data
  } catch (e) {
    console.error('Failed to fetch contributions:', e)
  } finally {
    isLoading.value = false
  }
}

async function fetchStats() {
  try {
    const { data } = await $api('/user/contributions/stats')
    Object.assign(stats, data)
  } catch (e) {
    console.error('Failed to fetch stats:', e)
  }
}

function getTypeIcon(type: string) {
  const icons: Record<string, string> = {
    edit: 'heroicons:pencil',
    typo: 'heroicons:document-text',
    content: 'heroicons:document-plus',
    translation: 'heroicons:language',
  }
  return icons[type] || 'heroicons:pencil-square'
}

function getTypeColor(type: string) {
  const colors: Record<string, string> = {
    edit: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600',
    typo: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600',
    content: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600',
    translation: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600',
  }
  return colors[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-600'
}

function getTypeLabel(type: string) {
  const labels: Record<string, string> = {
    edit: 'แก้ไขเนื้อหา',
    typo: 'แก้ไขคำผิด',
    content: 'เพิ่มเนื้อหา',
    translation: 'แปลภาษา',
  }
  return labels[type] || type
}

function getStatusLabel(status: string) {
  const labels: Record<string, string> = {
    pending: 'รอตรวจสอบ',
    approved: 'อนุมัติ',
    rejected: 'ไม่อนุมัติ',
    merged: 'รวมแล้ว',
  }
  return labels[status] || status
}

function getStatusClass(status: string) {
  const classes: Record<string, string> = {
    pending: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700',
    approved: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700',
    rejected: 'bg-red-100 dark:bg-red-900/30 text-red-700',
    merged: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700',
  }
  return classes[status] || ''
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>
