<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="text-center mb-10">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">อภิธานศัพท์</h1>
      <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
        ค้นหาและเรียนรู้คำศัพท์ด้านการพัฒนาซอฟต์แวร์
      </p>
    </div>

    <!-- Search -->
    <div class="relative max-w-2xl mx-auto mb-8">
      <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
      <input
        v-model="searchQuery"
        type="text"
        class="w-full pl-12 pr-4 py-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white"
        placeholder="ค้นหาคำศัพท์..."
        @input="debouncedSearch"
      />
    </div>

    <!-- Letter Filter -->
    <div v-if="!searchQuery" class="flex flex-wrap justify-center gap-2 mb-8">
      <button
        type="button"
        :class="letterButtonClass('')"
        @click="selectLetter('')"
      >
        ทั้งหมด
      </button>
      <button
        v-for="letter in letters"
        :key="letter"
        type="button"
        :class="letterButtonClass(letter)"
        @click="selectLetter(letter)"
      >
        {{ letter }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="pending" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 9" :key="i" class="h-28 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
    </div>

    <!-- Search Results -->
    <div v-else-if="searchQuery && searchResults.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <NuxtLink
        v-for="term in searchResults"
        :key="term.id"
        :to="`/glossary/${term.slug}`"
        class="p-5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-primary-300 dark:hover:border-primary-700 transition-colors"
      >
        <h2 class="font-semibold text-gray-900 dark:text-white">{{ term.term }}</h2>
        <p v-if="term.term_en" class="text-sm text-gray-500 mt-1">{{ term.term_en }}</p>
        <p v-if="term.definition_short" class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
          {{ term.definition_short }}
        </p>
      </NuxtLink>
    </div>

    <div v-else-if="searchQuery && !isSearching" class="text-center py-16 text-gray-500">
      ไม่พบคำศัพท์สำหรับ "{{ searchQuery }}"
    </div>

    <!-- Browse List -->
    <div v-else-if="terms.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <NuxtLink
        v-for="term in terms"
        :key="term.id"
        :to="`/glossary/${term.slug}`"
        class="p-5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-primary-300 dark:hover:border-primary-700 transition-colors"
      >
        <h2 class="font-semibold text-gray-900 dark:text-white">{{ term.term }}</h2>
        <p v-if="term.term_en" class="text-sm text-gray-500 mt-1">{{ term.term_en }}</p>
        <p v-if="term.definition_short" class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">
          {{ term.definition_short }}
        </p>
      </NuxtLink>
    </div>

    <div v-else class="text-center py-16 text-gray-500">
      ยังไม่มีคำศัพท์ในหมวดนี้
    </div>

    <!-- Pagination -->
    <div v-if="!searchQuery && lastPage > 1" class="mt-8 flex justify-center gap-2">
      <button
        type="button"
        class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 disabled:opacity-50"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
      >
        ก่อนหน้า
      </button>
      <span class="px-4 py-2 text-gray-500">{{ currentPage }} / {{ lastPage }}</span>
      <button
        type="button"
        class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 disabled:opacity-50"
        :disabled="currentPage === lastPage"
        @click="goToPage(currentPage + 1)"
      >
        ถัดไป
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useDebounceFn } from '@vueuse/core'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

interface GlossaryTerm {
  id: number
  term: string
  term_en?: string | null
  slug: string
  definition_short?: string | null
}

const config = useRuntimeConfig()

useHead({
  title: 'อภิธานศัพท์',
  meta: [{ name: 'description', content: 'อภิธานศัพท์ด้านการพัฒนาซอฟต์แวร์ภาษาไทย' }],
})

const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')
const selectedLetter = ref('')
const searchQuery = ref('')
const searchResults = ref<GlossaryTerm[]>([])
const isSearching = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)

const { data, pending, refresh } = await useFetch<{ data: GlossaryTerm[]; meta: { last_page: number; current_page: number } }>(
  () => `${config.public.apiBase}/glossary`,
  {
    key: 'glossary-list',
    query: computed(() => ({
      letter: selectedLetter.value || undefined,
      page: currentPage.value,
      per_page: 30,
    })),
    watch: [selectedLetter, currentPage],
  },
)

const terms = computed(() => data.value?.data || [])

watch(data, (value) => {
  if (value?.meta) {
    lastPage.value = value.meta.last_page
    currentPage.value = value.meta.current_page
  }
})

const debouncedSearch = useDebounceFn(async () => {
  if (!searchQuery.value.trim()) {
    searchResults.value = []
    return
  }

  isSearching.value = true
  try {
    const response = await $fetch<{ data: { terms: GlossaryTerm[] } }>(
      `${config.public.apiBase}/glossary/search`,
      { params: { q: searchQuery.value } },
    )
    searchResults.value = response.data?.terms || []
  } catch {
    searchResults.value = []
  } finally {
    isSearching.value = false
  }
}, 300)

function selectLetter(letter: string) {
  selectedLetter.value = letter
  currentPage.value = 1
  searchQuery.value = ''
  searchResults.value = []
}

function goToPage(page: number) {
  currentPage.value = page
  refresh()
}

function letterButtonClass(letter: string) {
  const active = selectedLetter.value === letter
  return [
    'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
    active
      ? 'bg-primary-600 text-white'
      : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700',
  ]
}
</script>
