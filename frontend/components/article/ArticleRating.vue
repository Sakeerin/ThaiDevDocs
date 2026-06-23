<template>
  <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800 space-y-8">
    <!-- Star Rating -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
        ให้คะแนนบทความนี้
      </h3>
      <div class="flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-1">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            :disabled="isRatingLoading || ratingSubmitted"
            class="p-1 disabled:opacity-50 transition-transform hover:scale-110"
            @click="submitRating(star)"
            @mouseenter="hoverRating = star"
            @mouseleave="hoverRating = 0"
          >
            <StarIcon
              class="w-7 h-7"
              :class="star <= (hoverRating || selectedRating) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
            />
          </button>
        </div>
        <span v-if="ratingSubmitted" class="text-sm text-green-600 dark:text-green-400">
          ขอบคุณสำหรับคะแนน!
        </span>
        <span v-else-if="!isAuthenticated" class="text-sm text-gray-500">
          <NuxtLink :to="{ path: '/auth/login', query: { redirect: route.fullPath } }" class="text-primary-600 hover:underline">
            เข้าสู่ระบบ
          </NuxtLink>
          เพื่อให้คะแนน
        </span>
      </div>
    </div>

    <!-- Helpful Feedback -->
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3 flex-wrap">
        <template v-if="feedbackSubmitted">
          <span class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
            <CheckCircleIcon class="w-5 h-5" />
            ขอบคุณสำหรับความคิดเห็น!
          </span>
        </template>
        <template v-else>
          <span class="text-gray-600 dark:text-gray-400">บทความนี้มีประโยชน์หรือไม่?</span>
          <button
            type="button"
            :disabled="isFeedbackLoading"
            class="p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 text-gray-500 hover:text-green-600 disabled:opacity-50"
            @click="submitFeedback(true)"
          >
            <HandThumbUpIcon class="w-6 h-6" />
          </button>
          <button
            type="button"
            :disabled="isFeedbackLoading"
            class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-500 hover:text-red-600 disabled:opacity-50"
            @click="submitFeedback(false)"
          >
            <HandThumbDownIcon class="w-6 h-6" />
          </button>
        </template>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          :disabled="isBookmarkLoading"
          class="btn-secondary flex items-center gap-2 disabled:opacity-50"
          @click="handleToggleBookmark"
        >
          <BookmarkIcon v-if="!isBookmarked" class="w-5 h-5" />
          <BookmarkSolidIcon v-else class="w-5 h-5 text-primary-600" />
          {{ isBookmarked ? 'บันทึกแล้ว' : 'บุ๊คมาร์ค' }}
        </button>
        <button
          type="button"
          class="btn-secondary flex items-center gap-2"
          @click="shareArticle"
        >
          <ShareIcon class="w-5 h-5" />
          แชร์
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  HandThumbUpIcon,
  HandThumbDownIcon,
  CheckCircleIcon,
  BookmarkIcon,
  ShareIcon,
} from '@heroicons/vue/24/outline'
import { StarIcon } from '@heroicons/vue/24/solid'
import { BookmarkIcon as BookmarkSolidIcon } from '@heroicons/vue/24/solid'

const props = defineProps<{
  article: {
    id: number
    slug: string
    title?: string
    summary?: string | null
  }
}>()

const route = useRoute()
const { isAuthenticated } = useAuth()
const { $api } = useNuxtApp()
const { isBookmarked, isLoading: isBookmarkLoading, loadStatus, toggle: toggleBookmark } = useBookmarks()

const hoverRating = ref(0)
const selectedRating = ref(0)
const ratingSubmitted = ref(false)
const isRatingLoading = ref(false)
const feedbackSubmitted = ref(false)
const isFeedbackLoading = ref(false)

watch(
  () => props.article.slug,
  (slug) => {
    if (slug) loadStatus(slug)
  },
  { immediate: true },
)

const requireAuth = async () => {
  if (!isAuthenticated.value) {
    await navigateTo({ path: '/auth/login', query: { redirect: route.fullPath } })
    return false
  }
  return true
}

const submitRating = async (rating: number) => {
  if (ratingSubmitted.value || isRatingLoading.value) return
  if (!(await requireAuth())) return

  isRatingLoading.value = true
  selectedRating.value = rating

  try {
    await $api(`/articles/${props.article.slug}/rate`, {
      method: 'POST',
      body: { rating },
    })
    ratingSubmitted.value = true
  } catch {
    selectedRating.value = 0
  } finally {
    isRatingLoading.value = false
  }
}

const submitFeedback = async (isHelpful: boolean) => {
  if (feedbackSubmitted.value || isFeedbackLoading.value) return
  if (!(await requireAuth())) return

  isFeedbackLoading.value = true

  try {
    await $api(`/articles/${props.article.slug}/feedback`, {
      method: 'POST',
      body: { is_helpful: isHelpful },
    })
    feedbackSubmitted.value = true
  } catch {
    // silent
  } finally {
    isFeedbackLoading.value = false
  }
}

const handleToggleBookmark = async () => {
  await toggleBookmark({ id: props.article.id, slug: props.article.slug })
}

const shareArticle = async () => {
  if (navigator.share) {
    await navigator.share({
      title: props.article.title,
      text: props.article.summary || undefined,
      url: window.location.href,
    })
  } else {
    await navigator.clipboard.writeText(window.location.href)
    alert('คัดลอกลิงก์แล้ว!')
  }
}
</script>
