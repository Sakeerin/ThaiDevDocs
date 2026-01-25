<template>
  <div v-if="path" class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 mb-8">
      <div class="flex items-start gap-6">
        <div :class="['w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shrink-0', getDifficultyBg(path.difficulty)]">
          {{ path.icon || '📚' }}
        </div>
        
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <span :class="getDifficultyClass(path.difficulty)">
              {{ getDifficultyLabel(path.difficulty) }}
            </span>
            <span class="text-sm text-gray-500">{{ path.estimated_hours }} ชั่วโมง</span>
          </div>
          
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ path.name }}</h1>
          <p class="text-gray-500 dark:text-gray-400">{{ path.description }}</p>
          
          <div class="mt-4 flex items-center gap-6 text-sm text-gray-500">
            <span class="flex items-center gap-1">
              <Icon name="heroicons:document-text" class="w-4 h-4" />
              {{ path.items?.length || 0 }} บทเรียน
            </span>
            <span class="flex items-center gap-1">
              <Icon name="heroicons:users" class="w-4 h-4" />
              {{ path.enrollments_count }} คนเรียน
            </span>
          </div>
        </div>
      </div>

      <!-- Progress (if enrolled) -->
      <div v-if="enrollment" class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-2">
          <span class="font-medium text-gray-700 dark:text-gray-300">ความก้าวหน้าของคุณ</span>
          <span class="text-primary-600 font-medium">{{ progress }}%</span>
        </div>
        <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all"
            :style="{ width: `${progress}%` }"
          />
        </div>
      </div>

      <!-- Enroll Button -->
      <div v-else-if="isAuthenticated" class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700">
        <button
          @click="enroll"
          :disabled="isEnrolling"
          class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors disabled:opacity-50"
        >
          {{ isEnrolling ? 'กำลังลงทะเบียน...' : 'เริ่มเรียน' }}
        </button>
      </div>

      <div v-else class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-700 text-center">
        <p class="text-gray-500 mb-4">เข้าสู่ระบบเพื่อติดตามความก้าวหน้า</p>
        <NuxtLink to="/auth/login" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium">
          เข้าสู่ระบบ
        </NuxtLink>
      </div>
    </div>

    <!-- Lessons -->
    <div class="space-y-4">
      <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">บทเรียน</h2>
      
      <div
        v-for="(item, index) in path.items"
        :key="item.id"
        :class="[
          'bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 transition-all',
          isCompleted(item.id) ? 'border-emerald-300 dark:border-emerald-700' : '',
          !isUnlocked(index) ? 'opacity-50' : 'hover:shadow-lg cursor-pointer'
        ]"
        @click="goToLesson(item, index)"
      >
        <div class="flex items-center gap-4">
          <!-- Status Icon -->
          <div
            :class="[
              'w-10 h-10 rounded-full flex items-center justify-center font-medium shrink-0',
              isCompleted(item.id) ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' :
              isUnlocked(index) ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600' :
              'bg-gray-100 dark:bg-slate-700 text-gray-400'
            ]"
          >
            <Icon v-if="isCompleted(item.id)" name="heroicons:check" class="w-5 h-5" />
            <Icon v-else-if="!isUnlocked(index)" name="heroicons:lock-closed" class="w-5 h-5" />
            <span v-else>{{ index + 1 }}</span>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <h3 :class="['font-medium', isUnlocked(index) ? 'text-gray-900 dark:text-white' : 'text-gray-500']">
              {{ item.article?.title }}
            </h3>
            <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
              <span class="flex items-center gap-1">
                <Icon name="heroicons:clock" class="w-4 h-4" />
                {{ item.article?.reading_time || 5 }} นาที
              </span>
              <span v-if="item.is_required" class="text-amber-600">
                จำเป็น
              </span>
            </div>
          </div>

          <!-- Arrow -->
          <Icon
            v-if="isUnlocked(index)"
            name="heroicons:chevron-right"
            class="w-5 h-5 text-gray-400"
          />
        </div>
      </div>
    </div>

    <!-- Certificate -->
    <div v-if="progress === 100" class="mt-8 bg-gradient-to-r from-amber-400 to-amber-600 rounded-2xl p-8 text-center text-white">
      <Icon name="heroicons:trophy" class="w-16 h-16 mx-auto mb-4" />
      <h3 class="text-2xl font-bold mb-2">ยินดีด้วย! 🎉</h3>
      <p class="mb-4">คุณเรียนจบเส้นทาง "{{ path.name }}" แล้ว</p>
      <button class="px-6 py-3 bg-white text-amber-600 rounded-xl font-medium hover:bg-amber-50 transition-colors">
        ดาวน์โหลดใบประกาศ
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const { isAuthenticated } = useAuth()

const path = ref<any>(null)
const enrollment = ref<any>(null)
const isEnrolling = ref(false)

const progress = computed(() => {
  if (!enrollment.value || !path.value?.items?.length) return 0
  return Math.round((enrollment.value.completed_items.length / path.value.items.length) * 100)
})

onMounted(async () => {
  // Fetch learning path
  try {
    const response = await $fetch<any>(`${config.public.apiBase}/learning-paths/${route.params.slug}`)
    path.value = response.data
  } catch (e) {
    console.error('Failed to fetch learning path:', e)
    router.push('/learn')
    return
  }

  // Fetch enrollment if authenticated
  if (isAuthenticated.value && path.value) {
    try {
      const { $api } = useNuxtApp()
      const { data } = await $api(`/user/learning-paths/${path.value.id}`)
      enrollment.value = data
    } catch (e) {
      // Not enrolled
    }
  }
})

async function enroll() {
  if (!isAuthenticated.value) {
    router.push('/auth/login')
    return
  }

  isEnrolling.value = true
  try {
    const { $api } = useNuxtApp()
    const { data } = await $api(`/learning-paths/${path.value.id}/enroll`, { method: 'POST' })
    enrollment.value = data
  } catch (e: any) {
    alert(e.data?.message || 'ไม่สามารถลงทะเบียนได้')
  } finally {
    isEnrolling.value = false
  }
}

function isCompleted(itemId: number) {
  return enrollment.value?.completed_items?.includes(itemId)
}

function isUnlocked(index: number) {
  if (!enrollment.value) return true // Show all if not enrolled
  if (index === 0) return true
  
  // Check if previous item is completed
  const prevItem = path.value.items[index - 1]
  return isCompleted(prevItem.id)
}

function goToLesson(item: any, index: number) {
  if (!isUnlocked(index)) return
  router.push(`/docs/${item.article?.slug}?learning_path=${path.value.id}`)
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
