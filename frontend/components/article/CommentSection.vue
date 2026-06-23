<template>
  <section class="mt-12 pt-8 border-t border-gray-200 dark:border-slate-700">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
      <Icon name="heroicons:chat-bubble-left-right" class="w-7 h-7" />
      ความคิดเห็น
      <span class="text-lg font-normal text-gray-500">({{ displayCount }})</span>
    </h2>

    <!-- Comment Form -->
    <div v-if="isAuthenticated" class="mb-8">
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-medium shrink-0">
          {{ user?.name?.charAt(0).toUpperCase() }}
        </div>
        <div class="flex-1">
          <textarea
            v-model="newComment"
            rows="3"
            class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white resize-none"
            placeholder="เขียนความคิดเห็น..."
            @focus="showCommentForm = true"
          />
          <div v-if="showCommentForm" class="mt-3 flex items-center justify-between">
            <p class="text-sm text-gray-500">
              รองรับ <span class="font-medium">Markdown</span>
            </p>
            <div class="flex gap-2">
              <button
                type="button"
                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-xl"
                @click="cancelComment"
              >
                ยกเลิก
              </button>
              <button
                type="button"
                :disabled="!newComment.trim() || isSubmitting"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium disabled:opacity-50"
                @click="submitComment"
              >
                {{ isSubmitting ? 'กำลังส่ง...' : 'ส่งความคิดเห็น' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Login Prompt -->
    <div v-else class="mb-8 p-6 bg-gray-50 dark:bg-slate-800 rounded-xl text-center">
      <p class="text-gray-600 dark:text-gray-400 mb-4">เข้าสู่ระบบเพื่อแสดงความคิดเห็น</p>
      <NuxtLink
        :to="{ path: '/auth/login', query: { redirect: route.fullPath } }"
        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium"
      >
        <Icon name="heroicons:arrow-right-on-rectangle" class="w-5 h-5" />
        เข้าสู่ระบบ
      </NuxtLink>
    </div>

    <!-- Sort Options -->
    <div v-if="comments.length > 0" class="flex items-center gap-4 mb-6">
      <span class="text-sm text-gray-500">เรียงตาม:</span>
      <select v-model="sortBy" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg text-sm text-gray-700 dark:text-gray-300">
        <option value="newest">ใหม่สุด</option>
        <option value="oldest">เก่าสุด</option>
        <option value="popular">ยอดนิยม</option>
      </select>
    </div>

    <!-- Comments List -->
    <div class="space-y-6">
      <CommentItem
        v-for="comment in sortedComments"
        :key="comment.id"
        :comment="comment"
        :current-user="user"
        @reply="handleReply"
        @vote="handleVote"
        @delete="handleDelete"
        @updated="handleUpdated"
      />
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && comments.length === 0" class="text-center py-12">
      <Icon name="heroicons:chat-bubble-bottom-center-text" class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">ยังไม่มีความคิดเห็น</h3>
      <p class="text-gray-500 dark:text-gray-400">เป็นคนแรกที่แสดงความคิดเห็น</p>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="space-y-6">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-gray-200 dark:bg-slate-700 rounded-full" />
          <div class="flex-1">
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/4 mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-full mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-2/3" />
          </div>
        </div>
      </div>
    </div>

    <!-- Load More -->
    <div v-if="hasMore && !isLoading" class="mt-8 text-center">
      <button
        type="button"
        class="px-6 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium transition-colors"
        @click="loadMore"
      >
        โหลดเพิ่มเติม
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Comment } from '~/types'

interface PaginatedCommentsResponse {
  success: boolean
  data: Comment[]
  meta: {
    current_page: number
    last_page: number
    total: number
  }
}

interface CommentCreateResponse {
  success: boolean
  data: {
    comment: Comment
  }
}

const props = defineProps<{
  articleSlug: string
  commentsCount?: number
}>()

const route = useRoute()
const { isAuthenticated, user } = useAuth()
const { $api } = useNuxtApp()

const comments = ref<Comment[]>([])
const isLoading = ref(true)
const isSubmitting = ref(false)
const newComment = ref('')
const showCommentForm = ref(false)
const sortBy = ref('newest')
const page = ref(1)
const hasMore = ref(false)
const totalCount = ref(props.commentsCount || 0)

const displayCount = computed(() => totalCount.value || comments.value.length)

const sortedComments = computed(() => {
  const sorted = [...comments.value]

  switch (sortBy.value) {
    case 'oldest':
      return sorted.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
    case 'popular':
      return sorted.sort((a, b) => b.upvote_count - a.upvote_count)
    default:
      return sorted.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  }
})

watch(() => props.articleSlug, () => {
  page.value = 1
  comments.value = []
  fetchComments()
})

onMounted(fetchComments)

async function fetchComments() {
  isLoading.value = true
  try {
    const response = await $api<PaginatedCommentsResponse>(`/articles/${props.articleSlug}/comments`, {
      params: { page: page.value, per_page: 10 },
    })

    if (page.value === 1) {
      comments.value = response.data
    } else {
      comments.value.push(...response.data)
    }

    totalCount.value = response.meta?.total ?? comments.value.length
    hasMore.value = response.meta.current_page < response.meta.last_page
  } catch (e) {
    console.error('Failed to fetch comments:', e)
  } finally {
    isLoading.value = false
  }
}

async function submitComment() {
  if (!newComment.value.trim()) return

  isSubmitting.value = true
  try {
    const response = await $api<CommentCreateResponse>(`/articles/${props.articleSlug}/comments`, {
      method: 'POST',
      body: { content: newComment.value },
    })

    comments.value.unshift(response.data.comment)
    totalCount.value++
    newComment.value = ''
    showCommentForm.value = false
  } catch (e: any) {
    alert(e?.error?.message || e?.message || 'ไม่สามารถส่งความคิดเห็นได้')
  } finally {
    isSubmitting.value = false
  }
}

function cancelComment() {
  newComment.value = ''
  showCommentForm.value = false
}

async function handleReply(parentId: number, content: string) {
  try {
    const response = await $api<CommentCreateResponse>(`/articles/${props.articleSlug}/comments`, {
      method: 'POST',
      body: { content, parent_id: parentId },
    })

    const parent = findComment(comments.value, parentId)
    if (parent) {
      if (!parent.replies) parent.replies = []
      parent.replies.push(response.data.comment)
    }
    totalCount.value++
  } catch (e: any) {
    alert(e?.error?.message || e?.message || 'ไม่สามารถตอบกลับได้')
  }
}

async function handleVote(commentId: number, type: 'up' | 'down') {
  try {
    const response = await $api<{ data: { upvote_count: number } }>(`/comments/${commentId}/vote`, {
      method: 'POST',
      body: { vote_type: type },
    })

    const comment = findComment(comments.value, commentId)
    if (comment) {
      comment.upvote_count = response.data.upvote_count
    }
  } catch (e) {
    console.error('Failed to vote:', e)
  }
}

async function handleDelete(commentId: number) {
  if (!confirm('ต้องการลบความคิดเห็นนี้หรือไม่?')) return

  try {
    await $api(`/comments/${commentId}`, { method: 'DELETE' })
    comments.value = comments.value.filter(c => c.id !== commentId)
    totalCount.value = Math.max(0, totalCount.value - 1)
  } catch (e) {
    console.error('Failed to delete comment:', e)
  }
}

function handleUpdated(updated: Comment) {
  const comment = findComment(comments.value, updated.id)
  if (comment) {
    comment.content = updated.content
    comment.updated_at = updated.updated_at
  }
}

function loadMore() {
  page.value++
  fetchComments()
}

function findComment(items: Comment[], id: number): Comment | null {
  for (const comment of items) {
    if (comment.id === id) return comment
    if (comment.replies) {
      const found = findComment(comment.replies, id)
      if (found) return found
    }
  }
  return null
}
</script>
