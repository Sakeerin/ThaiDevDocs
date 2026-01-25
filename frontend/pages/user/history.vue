<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">ประวัติการอ่าน</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">บทความที่คุณเคยอ่าน</p>
      </div>
      <button
        v-if="history.length > 0"
        @click="clearHistory"
        class="px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl text-sm font-medium transition-colors"
      >
        <Icon name="heroicons:trash" class="w-4 h-4 inline-block mr-1" />
        ล้างประวัติทั้งหมด
      </button>
    </div>

    <!-- Filter by Date -->
    <div class="flex gap-2 overflow-x-auto pb-4 mb-6">
      <button
        v-for="filter in dateFilters"
        :key="filter.value"
        @click="selectedFilter = filter.value"
        :class="[
          'px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors',
          selectedFilter === filter.value
            ? 'bg-primary-600 text-white'
            : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700'
        ]"
      >
        {{ filter.label }}
      </button>
    </div>

    <!-- History List -->
    <div v-if="!isLoading && groupedHistory.length > 0" class="space-y-8">
      <div v-for="group in groupedHistory" :key="group.date">
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">{{ group.dateLabel }}</h3>
        <div class="space-y-3">
          <div
            v-for="item in group.items"
            :key="item.id"
            class="group bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 hover:shadow-lg transition-all"
          >
            <div class="flex items-start gap-4">
              <!-- Category Icon -->
              <div
                class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                :style="{ backgroundColor: item.article?.topic?.category?.color + '20' }"
              >
                <span class="text-xl">{{ item.article?.topic?.category?.icon || '📄' }}</span>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ item.article?.topic?.category?.name }} / {{ item.article?.topic?.name }}
                  </span>
                </div>
                <NuxtLink :to="`/docs/${item.article?.slug}`" class="block group-hover:text-primary-600 transition-colors">
                  <h4 class="text-lg font-medium text-gray-900 dark:text-white line-clamp-1">
                    {{ item.article?.title }}
                  </h4>
                </NuxtLink>
                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                  <span class="flex items-center gap-1">
                    <Icon name="heroicons:clock" class="w-4 h-4" />
                    {{ formatTime(item.last_read_at) }}
                  </span>
                  <span v-if="item.reading_progress < 100" class="flex items-center gap-2">
                    <div class="w-20 h-1.5 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div
                        class="h-full bg-primary-500 rounded-full"
                        :style="{ width: `${item.reading_progress}%` }"
                      />
                    </div>
                    {{ item.reading_progress }}% อ่านแล้ว
                  </span>
                  <span v-else class="text-emerald-600 flex items-center gap-1">
                    <Icon name="heroicons:check-circle" class="w-4 h-4" />
                    อ่านจบแล้ว
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button
                  @click="addToBookmarks(item.article)"
                  class="p-2 text-gray-400 hover:text-primary-600 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
                  title="เพิ่มบุ๊กมาร์ก"
                >
                  <Icon name="heroicons:bookmark" class="w-5 h-5" />
                </button>
                <button
                  @click="removeFromHistory(item.id)"
                  class="p-2 text-gray-400 hover:text-red-500 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg"
                  title="ลบออกจากประวัติ"
                >
                  <Icon name="heroicons:x-mark" class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && history.length === 0" class="text-center py-16">
      <Icon name="heroicons:clock" class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
      <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">ยังไม่มีประวัติการอ่าน</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">เริ่มอ่านบทความเพื่อบันทึกประวัติการอ่านของคุณ</p>
      <NuxtLink to="/docs" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors">
        สำรวจบทความ
        <Icon name="heroicons:arrow-right" class="w-5 h-5" />
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 animate-pulse">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-gray-200 dark:bg-slate-700 rounded-xl" />
          <div class="flex-1">
            <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-1/4 mb-2" />
            <div class="h-5 bg-gray-200 dark:bg-slate-700 rounded w-3/4 mb-2" />
            <div class="h-3 bg-gray-200 dark:bg-slate-700 rounded w-1/3" />
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

const history = ref<any[]>([])
const isLoading = ref(true)
const selectedFilter = ref('all')

const dateFilters = [
  { value: 'all', label: 'ทั้งหมด' },
  { value: 'today', label: 'วันนี้' },
  { value: 'week', label: 'สัปดาห์นี้' },
  { value: 'month', label: 'เดือนนี้' },
]

const groupedHistory = computed(() => {
  const groups: Record<string, { date: string; dateLabel: string; items: any[] }> = {}
  
  const filteredHistory = history.value.filter(item => {
    const date = new Date(item.last_read_at)
    const now = new Date()
    
    switch (selectedFilter.value) {
      case 'today':
        return date.toDateString() === now.toDateString()
      case 'week':
        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        return date >= weekAgo
      case 'month':
        const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
        return date >= monthAgo
      default:
        return true
    }
  })

  filteredHistory.forEach(item => {
    const date = new Date(item.last_read_at)
    const dateKey = date.toDateString()
    const now = new Date()
    
    let dateLabel = ''
    if (dateKey === now.toDateString()) {
      dateLabel = 'วันนี้'
    } else if (dateKey === new Date(now.getTime() - 24 * 60 * 60 * 1000).toDateString()) {
      dateLabel = 'เมื่อวาน'
    } else {
      dateLabel = date.toLocaleDateString('th-TH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    }

    if (!groups[dateKey]) {
      groups[dateKey] = { date: dateKey, dateLabel, items: [] }
    }
    groups[dateKey].items.push(item)
  })

  return Object.values(groups).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
})

onMounted(fetchHistory)

async function fetchHistory() {
  isLoading.value = true
  try {
    const { data } = await $api('/user/reading-history')
    history.value = data
  } catch (e) {
    console.error('Failed to fetch history:', e)
  } finally {
    isLoading.value = false
  }
}

async function removeFromHistory(id: number) {
  try {
    await $api(`/user/reading-history/${id}`, { method: 'DELETE' })
    history.value = history.value.filter(h => h.id !== id)
  } catch (e) {
    console.error('Failed to remove from history:', e)
  }
}

async function clearHistory() {
  if (!confirm('ต้องการล้างประวัติการอ่านทั้งหมดหรือไม่?')) return
  
  try {
    await $api('/user/reading-history', { method: 'DELETE' })
    history.value = []
  } catch (e) {
    console.error('Failed to clear history:', e)
  }
}

async function addToBookmarks(article: any) {
  try {
    await $api('/bookmarks', {
      method: 'POST',
      body: { article_id: article.id }
    })
    // Could show a toast notification
  } catch (e) {
    console.error('Failed to add bookmark:', e)
  }
}

function formatTime(date: string) {
  return new Date(date).toLocaleTimeString('th-TH', {
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
