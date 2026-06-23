export function useBookmarks() {
  const route = useRoute()
  const { isAuthenticated } = useAuth()
  const { $api } = useNuxtApp()

  const bookmarkId = ref<number | null>(null)
  const isLoading = ref(false)

  const isBookmarked = computed(() => bookmarkId.value !== null)

  const loadStatus = async (articleSlug: string) => {
    if (!isAuthenticated.value) {
      bookmarkId.value = null
      return
    }

    try {
      const response = await $api<{
        data: { is_bookmarked: boolean; bookmark_id: number | null }
      }>(`/articles/${articleSlug}/bookmark-status`)

      bookmarkId.value = response.data.is_bookmarked ? response.data.bookmark_id : null
    } catch {
      bookmarkId.value = null
    }
  }

  const toggle = async (article: { id: number; slug: string }) => {
    if (!isAuthenticated.value) {
      await navigateTo({ path: '/auth/login', query: { redirect: route.fullPath } })
      return
    }

    isLoading.value = true

    try {
      if (bookmarkId.value) {
        await $api(`/bookmarks/${bookmarkId.value}`, { method: 'DELETE' })
        bookmarkId.value = null
      } else {
        const response = await $api<{
          data: { bookmark: { id: number } }
        }>('/bookmarks', {
          method: 'POST',
          body: { article_id: article.id },
        })
        bookmarkId.value = response.data.bookmark.id
      }
    } finally {
      isLoading.value = false
    }
  }

  watch(isAuthenticated, () => {
    bookmarkId.value = null
  })

  return {
    bookmarkId,
    isBookmarked,
    isLoading,
    loadStatus,
    toggle,
  }
}
