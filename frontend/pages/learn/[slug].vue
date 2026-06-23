<template>
  <div v-if="pending" class="container mx-auto px-4 py-20 flex justify-center">
    <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin" />
  </div>

  <div v-else-if="path" class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 mb-8">
      <div class="flex items-start gap-6">
        <div :class="['w-20 h-20 rounded-2xl flex items-center justify-center text-4xl text-white shrink-0', getDifficultyBg(path.difficulty)]">
          📚
        </div>

        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <span :class="getDifficultyClass(path.difficulty)">
              {{ getDifficultyLabel(path.difficulty) }}
            </span>
            <span class="text-sm text-gray-500">{{ path.estimated_hours || '?' }} ชั่วโมง</span>
          </div>

          <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ path.title }}</h1>
          <p class="text-gray-500 dark:text-gray-400">{{ path.description }}</p>

          <div class="mt-4 flex items-center gap-6 text-sm text-gray-500">
            <span class="flex items-center gap-1">
              <DocumentTextIcon class="w-4 h-4" />
              {{ path.items?.length || 0 }} บทเรียน
            </span>
            <span class="flex items-center gap-1">
              <UsersIcon class="w-4 h-4" />
              {{ path.enrollment_count || 0 }} คนเรียน
            </span>
          </div>
        </div>
      </div>

      <div v-if="enrollment?.enrolled" class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
        <ProgressTracker
          :percentage="progressPercentage"
          :completed="completedCount"
          :total="path.items?.length || 0"
        />
      </div>

      <div v-else-if="isAuthenticated" class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
        <button
          type="button"
          :disabled="isEnrolling"
          class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
          @click="handleEnroll"
        >
          {{ isEnrolling ? 'กำลังลงทะเบียน...' : 'เริ่มเรียน' }}
        </button>
      </div>

      <div v-else class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700 text-center">
        <p class="text-gray-500 mb-4">เข้าสู่ระบบเพื่อติดตามความก้าวหน้า</p>
        <NuxtLink
          :to="{ path: '/auth/login', query: { redirect: route.fullPath } }"
          class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium"
        >
          เข้าสู่ระบบ
        </NuxtLink>
      </div>
    </div>

    <div class="space-y-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">บทเรียน</h2>

      <div
        v-for="(item, index) in path.items"
        :key="item.id"
        :class="[
          'bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 transition-all',
          isCompleted(item.id) ? 'border-emerald-300 dark:border-emerald-700' : '',
          isUnlocked(index) ? 'hover:shadow-lg cursor-pointer' : 'opacity-50',
        ]"
        @click="goToLesson(item, index)"
      >
        <div class="flex items-center gap-4">
          <div
            :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-medium shrink-0',
              isCompleted(item.id) ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600'
                : isUnlocked(index) ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600'
                  : 'bg-gray-100 dark:bg-slate-700 text-gray-400',
            ]"
          >
            <CheckIcon v-if="isCompleted(item.id)" class="w-5 h-5" />
            <LockClosedIcon v-else-if="!isUnlocked(index)" class="w-5 h-5" />
            <span v-else>{{ index + 1 }}</span>
          </div>

          <div class="flex-1 min-w-0">
            <h3 :class="['font-medium', isUnlocked(index) ? 'text-gray-900 dark:text-white' : 'text-gray-500']">
              {{ item.article?.title }}
            </h3>
            <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
              <span class="flex items-center gap-1">
                <ClockIcon class="w-4 h-4" />
                {{ item.article?.reading_time || 5 }} นาที
              </span>
              <span v-if="item.is_required" class="text-amber-600">จำเป็น</span>
            </div>
          </div>

          <ChevronRightIcon v-if="isUnlocked(index)" class="w-5 h-5 text-gray-400" />
        </div>
      </div>
    </div>

    <div v-if="progressPercentage === 100" class="mt-8 bg-gradient-to-r from-amber-400 to-amber-600 rounded-2xl p-8 text-center text-white">
      <TrophyIcon class="w-16 h-16 mx-auto mb-4" />
      <h3 class="text-2xl font-bold mb-2">ยินดีด้วย!</h3>
      <p class="mb-4">คุณเรียนจบเส้นทาง "{{ path.title }}" แล้ว</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  DocumentTextIcon,
  UsersIcon,
  ClockIcon,
  ChevronRightIcon,
  CheckIcon,
  LockClosedIcon,
  TrophyIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { isAuthenticated } = useAuth()
const { fetchPath, fetchProgress, enroll } = useLearningPath()

const path = ref<any>(null)
const enrollment = ref<{ enrolled: boolean; progress?: { progress_percentage: number; completed_item_ids: number[] } } | null>(null)
const pending = ref(true)
const isEnrolling = ref(false)

const pathSlug = computed(() => route.params.slug as string)

const completedItemIds = computed(() => enrollment.value?.progress?.completed_item_ids || [])

const progressPercentage = computed(() => {
  if (enrollment.value?.progress?.progress_percentage != null) {
    return enrollment.value.progress.progress_percentage
  }
  if (!path.value?.items?.length) return 0
  return Math.round((completedItemIds.value.length / path.value.items.length) * 100)
})

const completedCount = computed(() => completedItemIds.value.length)

onMounted(loadPath)

watch(pathSlug, loadPath)

async function loadPath() {
  pending.value = true
  try {
    const data = await fetchPath(pathSlug.value)
    path.value = data.path

    if (isAuthenticated.value) {
      enrollment.value = await fetchProgress(pathSlug.value)
    }
  } catch (e) {
    console.error('Failed to fetch learning path:', e)
    router.push('/learn')
  } finally {
    pending.value = false
  }
}

async function handleEnroll() {
  if (!isAuthenticated.value) {
    await navigateTo({ path: '/auth/login', query: { redirect: route.fullPath } })
    return
  }

  isEnrolling.value = true
  try {
    await enroll(pathSlug.value)
    enrollment.value = await fetchProgress(pathSlug.value)
  } catch (e: any) {
    alert(e?.error?.message || e?.message || 'ไม่สามารถลงทะเบียนได้')
  } finally {
    isEnrolling.value = false
  }
}

function isCompleted(itemId: number) {
  return completedItemIds.value.includes(itemId)
}

function isUnlocked(index: number) {
  if (!enrollment.value?.enrolled) return true
  if (index === 0) return true
  const prevItem = path.value.items[index - 1]
  return isCompleted(prevItem.id)
}

function goToLesson(item: any, index: number) {
  if (!isUnlocked(index) || !item.article?.slug) return

  router.push({
    path: `/docs/${item.article.slug}`,
    query: {
      learning_path: pathSlug.value,
      item_id: String(item.id),
    },
  })
}

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
    beginner: 'px-3 py-1 text-sm font-medium rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700',
    intermediate: 'px-3 py-1 text-sm font-medium rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700',
    advanced: 'px-3 py-1 text-sm font-medium rounded-full bg-red-100 dark:bg-red-900/30 text-red-700',
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
</script>
