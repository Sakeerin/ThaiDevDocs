<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="text-center mb-12">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">เส้นทางการเรียนรู้</h1>
      <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
        เรียนรู้อย่างเป็นระบบตามเส้นทางที่เราจัดเตรียมไว้ให้ พร้อมติดตามความก้าวหน้าของคุณ
      </p>
    </div>

    <!-- My Progress -->
    <div v-if="isAuthenticated && myPaths.length > 0" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">เส้นทางของฉัน</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <NuxtLink
          v-for="entry in myPaths"
          :key="entry.path?.id"
          :to="`/learn/${entry.path?.slug}`"
          class="group bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6 hover:shadow-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all"
        >
          <div class="flex items-start justify-between mb-4">
            <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white', getDifficultyBg(entry.path?.difficulty || '')]">
              📚
            </div>
            <span v-if="entry.is_completed" class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
              จบแล้ว
            </span>
          </div>

          <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">
            {{ entry.path?.title }}
          </h3>

          <ProgressTracker
            class="mt-4"
            :percentage="entry.progress_percentage"
            :completed="Math.round((entry.progress_percentage / 100) * (entry.path?.total_items || 0))"
            :total="entry.path?.total_items || 0"
          />
        </NuxtLink>
      </div>
    </div>

    <!-- Filter -->
    <div class="flex flex-wrap items-center gap-4 mb-8">
      <button
        v-for="filter in difficultyFilters"
        :key="filter.value"
        type="button"
        :class="[
          'px-4 py-2 rounded-xl text-sm font-medium transition-colors',
          selectedDifficulty === filter.value
            ? filter.activeClass
            : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700',
        ]"
        @click="selectedDifficulty = filter.value"
      >
        {{ filter.label }}
      </button>
    </div>

    <!-- Learning Paths -->
    <div v-if="pending" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="h-64 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <NuxtLink
        v-for="path in filteredPaths"
        :key="path.id"
        :to="`/learn/${path.slug}`"
        class="group bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 overflow-hidden hover:shadow-xl hover:border-primary-300 dark:hover:border-primary-700 transition-all"
      >
        <div :class="['h-32 flex items-center justify-center text-4xl text-white', getDifficultyBg(path.difficulty)]">
          📚
        </div>

        <div class="p-6">
          <div class="flex items-center gap-2 mb-3">
            <span :class="getDifficultyClass(path.difficulty)">
              {{ getDifficultyLabel(path.difficulty) }}
            </span>
            <span class="flex items-center gap-1 text-sm text-gray-500">
              <ClockIcon class="w-4 h-4" />
              {{ path.estimated_hours || '?' }} ชั่วโมง
            </span>
          </div>

          <h3 class="text-xl font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">
            {{ path.title }}
          </h3>

          <p class="mt-2 text-gray-500 dark:text-gray-400 line-clamp-2">
            {{ path.description }}
          </p>

          <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
            <span class="flex items-center gap-1">
              <DocumentTextIcon class="w-4 h-4" />
              {{ path.items_count || 0 }} บทเรียน
            </span>
            <span class="flex items-center gap-1">
              <UsersIcon class="w-4 h-4" />
              {{ formatNumber(path.enrollment_count || 0) }} คนเรียน
            </span>
          </div>
        </div>
      </NuxtLink>
    </div>

    <div v-if="!pending && filteredPaths.length === 0" class="text-center py-16">
      <AcademicCapIcon class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
      <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">ไม่พบเส้นทางการเรียนรู้</h3>
      <p class="text-gray-500 dark:text-gray-400">ลองเลือกตัวกรองอื่น</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  ClockIcon,
  DocumentTextIcon,
  UsersIcon,
  AcademicCapIcon,
} from '@heroicons/vue/24/outline'
import type { LearningPathSummary, MyLearningEntry } from '~/composables/useLearningPath'

const { isAuthenticated } = useAuth()
const { fetchPaths, fetchMyLearning } = useLearningPath()
const config = useRuntimeConfig()

useHead({
  title: 'เส้นทางการเรียนรู้',
})

const paths = ref<LearningPathSummary[]>([])
const myPaths = ref<MyLearningEntry[]>([])
const selectedDifficulty = ref('')
const pending = ref(true)

const difficultyFilters = [
  { value: '', label: 'ทั้งหมด', activeClass: 'bg-primary-600 text-white' },
  { value: 'beginner', label: 'เริ่มต้น', activeClass: 'bg-emerald-600 text-white' },
  { value: 'intermediate', label: 'กลาง', activeClass: 'bg-amber-600 text-white' },
  { value: 'advanced', label: 'สูง', activeClass: 'bg-red-600 text-white' },
]

const filteredPaths = computed(() => {
  if (!selectedDifficulty.value) return paths.value
  return paths.value.filter(p => p.difficulty === selectedDifficulty.value)
})

onMounted(async () => {
  pending.value = true
  try {
    const response = await $fetch<{ data: LearningPathSummary[] }>(`${config.public.apiBase}/learning-paths`)
    paths.value = response.data || []
  } catch (e) {
    console.error('Failed to fetch learning paths:', e)
  } finally {
    pending.value = false
  }

  if (isAuthenticated.value) {
    try {
      myPaths.value = await fetchMyLearning()
    } catch (e) {
      console.error('Failed to fetch my learning paths:', e)
    }
  }
})

function getDifficultyLabel(difficulty: string) {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[difficulty] || difficulty
}

function getDifficultyClass(difficulty: string) {
  const classes: Record<string, string> = {
    beginner: 'px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700',
    intermediate: 'px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700',
    advanced: 'px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700',
  }
  return classes[difficulty] || ''
}

function getDifficultyBg(difficulty: string) {
  const bgs: Record<string, string> = {
    beginner: 'bg-gradient-to-br from-emerald-400 to-emerald-600',
    intermediate: 'bg-gradient-to-br from-amber-400 to-amber-600',
    advanced: 'bg-gradient-to-br from-red-400 to-red-600',
  }
  return bgs[difficulty] || 'bg-gradient-to-br from-primary-400 to-primary-600'
}

function formatNumber(num: number) {
  if (!num) return '0'
  if (num >= 1000) return `${(num / 1000).toFixed(1)}K`
  return num.toString()
}
</script>
