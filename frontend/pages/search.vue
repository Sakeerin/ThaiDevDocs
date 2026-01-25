<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Search Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">ค้นหา</h1>
      <div class="relative">
        <Icon name="heroicons:magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input
          v-model="query"
          type="text"
          class="w-full pl-12 pr-4 py-4 text-lg bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
          placeholder="ค้นหาบทความ, หัวข้อ, หรือคำศัพท์..."
          @input="debouncedSearch"
        />
        <button
          v-if="query"
          @click="clearSearch"
          class="absolute right-4 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
        >
          <Icon name="heroicons:x-mark" class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-4 mb-6">
      <select v-model="filters.category" @change="performSearch" class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-700 dark:text-gray-300">
        <option value="">หมวดหมู่ทั้งหมด</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>

      <select v-model="filters.difficulty" @change="performSearch" class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-700 dark:text-gray-300">
        <option value="">ทุกระดับ</option>
        <option value="beginner">เริ่มต้น</option>
        <option value="intermediate">กลาง</option>
        <option value="advanced">สูง</option>
      </select>

      <select v-model="filters.type" @change="performSearch" class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-700 dark:text-gray-300">
        <option value="">ทุกประเภท</option>
        <option value="guide">คู่มือ</option>
        <option value="tutorial">บทเรียน</option>
        <option value="reference">อ้างอิง</option>
        <option value="example">ตัวอย่าง</option>
      </select>

      <select v-model="sortBy" @change="performSearch" class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-700 dark:text-gray-300">
        <option value="relevance">ความเกี่ยวข้อง</option>
        <option value="newest">ใหม่ล่าสุด</option>
        <option value="popular">ยอดนิยม</option>
      </select>
    </div>

    <!-- Search Stats -->
    <div v-if="query && !isLoading" class="mb-6 text-gray-500 dark:text-gray-400">
      <span v-if="results.length > 0">
        พบ <span class="font-medium text-gray-900 dark:text-white">{{ totalResults }}</span> ผลลัพธ์
        <span v-if="searchTime">({{ searchTime }} มิลลิวินาที)</span>
      </span>
      <span v-else>
        ไม่พบผลลัพธ์สำหรับ "{{ query }}"
      </span>
    </div>

    <!-- Results -->
    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 animate-pulse">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-gray-200 dark:bg-slate-700 rounded-lg" />
          <div class="flex-1">
            <div class="h-6 bg-gray-200 dark:bg-slate-700 rounded w-3/4 mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-full mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-2/3" />
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="results.length > 0" class="space-y-4">
      <article
        v-for="result in results"
        :key="result.id"
        class="group bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 hover:shadow-lg hover:border-primary-300 dark:hover:border-primary-700 transition-all"
      >
        <div class="flex items-start gap-4">
          <!-- Category Icon -->
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
            :style="{ backgroundColor: (result.category_color || '#6366F1') + '20' }"
          >
            <span class="text-xl">{{ result.category_icon || '📄' }}</span>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
              <span>{{ result.category_name }}</span>
              <Icon name="heroicons:chevron-right" class="w-4 h-4" />
              <span>{{ result.topic_name }}</span>
            </div>

            <!-- Title -->
            <NuxtLink :to="`/docs/${result.slug}`" class="block group-hover:text-primary-600 transition-colors">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white" v-html="result._formatted?.title || result.title" />
            </NuxtLink>

            <!-- Summary -->
            <p class="mt-2 text-gray-600 dark:text-gray-400 line-clamp-2" v-html="result._formatted?.summary || result.summary" />

            <!-- Meta -->
            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
              <span :class="getDifficultyClass(result.difficulty)">
                {{ getDifficultyLabel(result.difficulty) }}
              </span>
              <span class="flex items-center gap-1 text-gray-500">
                <Icon name="heroicons:clock" class="w-4 h-4" />
                {{ result.reading_time || 5 }} นาที
              </span>
              <span class="flex items-center gap-1 text-gray-500">
                <Icon name="heroicons:eye" class="w-4 h-4" />
                {{ formatNumber(result.view_count) }}
              </span>
              <div v-if="result.tags?.length" class="flex items-center gap-2">
                <span
                  v-for="tag in result.tags.slice(0, 3)"
                  :key="tag"
                  class="px-2 py-0.5 bg-gray-100 dark:bg-slate-700 rounded text-xs text-gray-600 dark:text-gray-400"
                >
                  {{ tag }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- No Results -->
    <div v-else-if="query && !isLoading" class="text-center py-16">
      <Icon name="heroicons:document-magnifying-glass" class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
      <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">ไม่พบผลลัพธ์</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">ลองใช้คำค้นหาอื่น หรือปรับตัวกรอง</p>
      <div class="flex flex-wrap justify-center gap-2">
        <span class="text-gray-500 text-sm">ลองค้นหา:</span>
        <button
          v-for="suggestion in suggestions"
          :key="suggestion"
          @click="searchSuggestion(suggestion)"
          class="px-3 py-1 bg-gray-100 dark:bg-slate-800 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700"
        >
          {{ suggestion }}
        </button>
      </div>
    </div>

    <!-- Initial State -->
    <div v-else class="py-16">
      <div class="text-center mb-12">
        <Icon name="heroicons:magnifying-glass" class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">เริ่มค้นหา</h3>
        <p class="text-gray-500 dark:text-gray-400">พิมพ์คำค้นหาเพื่อเริ่มต้น</p>
      </div>

      <!-- Popular Searches -->
      <div class="max-w-2xl mx-auto">
        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">คำค้นหายอดนิยม</h4>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="term in popularSearches"
            :key="term"
            @click="searchSuggestion(term)"
            class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-700 dark:text-gray-300 hover:border-primary-500 hover:text-primary-600 transition-colors"
          >
            {{ term }}
          </button>
        </div>
      </div>

      <!-- Recent Searches -->
      <div v-if="recentSearches.length > 0" class="max-w-2xl mx-auto mt-8">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">ค้นหาล่าสุด</h4>
          <button @click="clearRecentSearches" class="text-sm text-gray-400 hover:text-gray-600">
            ล้าง
          </button>
        </div>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="term in recentSearches"
            :key="term"
            @click="searchSuggestion(term)"
            class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-slate-800/50 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
          >
            <Icon name="heroicons:clock" class="w-4 h-4" />
            {{ term }}
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-8 flex items-center justify-center gap-2">
      <button
        @click="prevPage"
        :disabled="currentPage === 1"
        class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-slate-700"
      >
        ก่อนหน้า
      </button>
      <span class="px-4 py-2 text-gray-600 dark:text-gray-400">
        {{ currentPage }} / {{ totalPages }}
      </span>
      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-slate-700"
      >
        ถัดไป
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useDebounceFn } from '@vueuse/core'

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()

const query = ref((route.query.q as string) || '')
const results = ref<any[]>([])
const isLoading = ref(false)
const totalResults = ref(0)
const searchTime = ref(0)
const currentPage = ref(1)
const perPage = 20

const filters = reactive({
  category: '',
  difficulty: '',
  type: '',
})

const sortBy = ref('relevance')

const categories = ref<any[]>([])
const recentSearches = ref<string[]>([])
const suggestions = ['JavaScript เบื้องต้น', 'CSS Flexbox', 'React Hooks', 'TypeScript']
const popularSearches = ['JavaScript', 'CSS Grid', 'Vue.js', 'HTML5', 'React', 'TypeScript', 'Node.js', 'API']

const totalPages = computed(() => Math.ceil(totalResults.value / perPage))

onMounted(async () => {
  // Load categories
  try {
    const response = await $fetch<any>(`${config.public.apiBase}/categories`)
    categories.value = response.data
  } catch (e) {
    console.error('Failed to load categories:', e)
  }

  // Load recent searches from localStorage
  const stored = localStorage.getItem('recent-searches')
  if (stored) {
    recentSearches.value = JSON.parse(stored)
  }

  // Perform initial search if query exists
  if (query.value) {
    performSearch()
  }
})

const debouncedSearch = useDebounceFn(() => {
  currentPage.value = 1
  performSearch()
}, 300)

async function performSearch() {
  if (!query.value.trim()) {
    results.value = []
    return
  }

  isLoading.value = true
  try {
    const params: any = {
      q: query.value,
      page: currentPage.value,
      per_page: perPage,
    }

    if (filters.category) params.category_id = filters.category
    if (filters.difficulty) params.difficulty = filters.difficulty
    if (filters.type) params.type = filters.type
    if (sortBy.value !== 'relevance') params.sort = sortBy.value

    const response = await $fetch<any>(`${config.public.apiBase}/search`, { params })
    
    results.value = response.data?.hits || []
    totalResults.value = response.data?.estimatedTotalHits || results.value.length
    searchTime.value = response.data?.processingTimeMs || 0

    // Save to recent searches
    saveRecentSearch(query.value)

    // Update URL
    router.replace({ query: { q: query.value, ...filters } })
  } catch (error) {
    console.error('Search error:', error)
    results.value = []
  } finally {
    isLoading.value = false
  }
}

function searchSuggestion(term: string) {
  query.value = term
  currentPage.value = 1
  performSearch()
}

function clearSearch() {
  query.value = ''
  results.value = []
  router.replace({ query: {} })
}

function saveRecentSearch(term: string) {
  const searches = recentSearches.value.filter(s => s !== term)
  searches.unshift(term)
  recentSearches.value = searches.slice(0, 10)
  localStorage.setItem('recent-searches', JSON.stringify(recentSearches.value))
}

function clearRecentSearches() {
  recentSearches.value = []
  localStorage.removeItem('recent-searches')
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--
    performSearch()
  }
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    performSearch()
  }
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
    beginner: 'px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    intermediate: 'px-2 py-0.5 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    advanced: 'px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
  }
  return classes[difficulty] || ''
}

function formatNumber(num: number) {
  if (!num) return '0'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num.toString()
}
</script>
