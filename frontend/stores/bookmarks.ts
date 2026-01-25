import { defineStore } from 'pinia'

interface Bookmark {
  id: number
  article_id: number
  collection_id: number | null
  notes: string | null
  created_at: string
  article: {
    id: number
    title: string
    slug: string
    summary: string
    difficulty: string
    reading_time: number
  }
}

interface BookmarkState {
  bookmarks: Bookmark[]
  bookmarkedArticleIds: Set<number>
  isLoading: boolean
}

export const useBookmarkStore = defineStore('bookmarks', {
  state: (): BookmarkState => ({
    bookmarks: [],
    bookmarkedArticleIds: new Set(),
    isLoading: false,
  }),

  getters: {
    isBookmarked: (state) => (articleId: number) => state.bookmarkedArticleIds.has(articleId),
    bookmarkCount: (state) => state.bookmarks.length,
  },

  actions: {
    async fetchBookmarks() {
      const api = useApi()
      this.isLoading = true

      try {
        const response = await api.get<any>('/bookmarks')
        this.bookmarks = response.data || []
        this.bookmarkedArticleIds = new Set(this.bookmarks.map(b => b.article_id))
      } finally {
        this.isLoading = false
      }
    },

    async addBookmark(articleId: number, collectionId?: number, notes?: string) {
      const api = useApi()

      try {
        const response = await api.post<any>('/bookmarks', {
          article_id: articleId,
          collection_id: collectionId,
          notes,
        })

        this.bookmarkedArticleIds.add(articleId)
        await this.fetchBookmarks() // Refresh list

        return response
      } catch (error) {
        throw error
      }
    },

    async removeBookmark(bookmarkId: number) {
      const api = useApi()
      const bookmark = this.bookmarks.find(b => b.id === bookmarkId)

      try {
        await api.del(`/bookmarks/${bookmarkId}`)

        if (bookmark) {
          this.bookmarkedArticleIds.delete(bookmark.article_id)
        }
        this.bookmarks = this.bookmarks.filter(b => b.id !== bookmarkId)

        return true
      } catch (error) {
        throw error
      }
    },

    async toggleBookmark(articleId: number) {
      if (this.isBookmarked(articleId)) {
        const bookmark = this.bookmarks.find(b => b.article_id === articleId)
        if (bookmark) {
          await this.removeBookmark(bookmark.id)
        }
        return false
      } else {
        await this.addBookmark(articleId)
        return true
      }
    },

    async checkBookmarkStatus(articleSlug: string) {
      const api = useApi()

      try {
        const response = await api.get<any>(`/articles/${articleSlug}/bookmark-status`)
        return response.data
      } catch (error) {
        return { is_bookmarked: false, bookmark_id: null }
      }
    },
  },
})
