export interface SearchFilters {
  category: string
  difficulty: string
  type: string
}

export interface SearchResult {
  id: number
  title: string
  slug: string
  summary?: string
  difficulty?: string
  topic?: {
    name: string
    slug: string
    category?: { slug: string } | null
  } | null
}

export const useSearch = () => {
  const config = useRuntimeConfig()

  const query = ref('')
  const results = ref<SearchResult[]>([])
  const isLoading = ref(false)
  const totalResults = ref(0)
  const searchTime = ref(0)
  const currentPage = ref(1)
  const perPage = 20
  const sortBy = ref('relevance')

  const filters = reactive<SearchFilters>({
    category: '',
    difficulty: '',
    type: '',
  })

  const totalPages = computed(() => Math.ceil(totalResults.value / perPage))

  const buildParams = () => {
    const params: Record<string, string | number> = {
      q: query.value,
      page: currentPage.value,
      per_page: perPage,
    }

    if (filters.category) params.category_id = filters.category
    if (filters.difficulty) params.difficulty = filters.difficulty
    if (filters.type) params.type = filters.type
    if (sortBy.value !== 'relevance') params.sort = sortBy.value

    return params
  }

  const search = async () => {
    if (!query.value.trim()) {
      results.value = []
      totalResults.value = 0
      return
    }

    isLoading.value = true

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/search`, {
        params: buildParams(),
      })

      results.value = response.data?.hits || []
      totalResults.value = response.data?.estimatedTotalHits || results.value.length
      searchTime.value = response.data?.processingTimeMs || 0
    } catch (error) {
      console.error('Search error:', error)
      results.value = []
      totalResults.value = 0
    } finally {
      isLoading.value = false
    }
  }

  const fetchSuggestions = async (term: string) => {
    if (!term.trim()) return []

    try {
      const response = await $fetch<any>(`${config.public.apiBase}/search/suggestions`, {
        params: { q: term },
      })
      return response.data?.suggestions || []
    } catch {
      return []
    }
  }

  const fetchPopularSearches = async () => {
    try {
      const response = await $fetch<any>(`${config.public.apiBase}/search/popular`)
      return response.data?.queries || []
    } catch {
      return []
    }
  }

  const reset = () => {
    query.value = ''
    results.value = []
    totalResults.value = 0
    currentPage.value = 1
  }

  return {
    query,
    results,
    isLoading,
    totalResults,
    searchTime,
    currentPage,
    perPage,
    sortBy,
    filters,
    totalPages,
    search,
    fetchSuggestions,
    fetchPopularSearches,
    reset,
  }
}
