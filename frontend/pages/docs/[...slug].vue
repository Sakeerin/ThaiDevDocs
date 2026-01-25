<template>
  <div>
    <NuxtLayout name="docs">
      <!-- Loading State -->
      <div v-if="pending" class="flex items-center justify-center py-20">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="container mx-auto px-4 py-20 text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
          ไม่พบบทความ
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          บทความที่คุณค้นหาอาจถูกลบหรือย้ายไปแล้ว
        </p>
        <NuxtLink to="/docs" class="btn-primary">
          กลับไปหน้าเอกสาร
        </NuxtLink>
      </div>

      <!-- Article Content -->
      <div v-else-if="article" class="container max-w-4xl mx-auto px-4 py-8 lg:py-12">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
          <NuxtLink to="/docs" class="hover:text-primary-600 dark:hover:text-primary-400">
            เอกสาร
          </NuxtLink>
          <ChevronRightIcon class="w-4 h-4" />
          <NuxtLink
            v-if="article.topic?.category"
            :to="`/docs/${article.topic.category.slug}`"
            class="hover:text-primary-600 dark:hover:text-primary-400"
          >
            {{ article.topic.category.name }}
          </NuxtLink>
          <template v-if="article.topic?.category">
            <ChevronRightIcon class="w-4 h-4" />
          </template>
          <NuxtLink
            v-if="article.topic"
            :to="`/docs/${article.topic.category?.slug}/${article.topic.slug}`"
            class="hover:text-primary-600 dark:hover:text-primary-400"
          >
            {{ article.topic.name }}
          </NuxtLink>
        </nav>

        <!-- Article Header -->
        <header class="mb-8">
          <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="badge-primary">{{ article.topic?.name }}</span>
            <span :class="difficultyClass">{{ difficultyLabel }}</span>
          </div>
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            {{ article.title }}
          </h1>
          <p v-if="article.summary" class="text-lg text-gray-600 dark:text-gray-400 mb-6">
            {{ article.summary }}
          </p>
          <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1">
              <ClockIcon class="w-4 h-4" />
              {{ article.reading_time }} นาที
            </span>
            <span v-if="article.view_count" class="flex items-center gap-1">
              <EyeIcon class="w-4 h-4" />
              {{ article.view_count }} วิว
            </span>
            <span v-if="article.published_at" class="flex items-center gap-1">
              <CalendarIcon class="w-4 h-4" />
              {{ formatDate(article.published_at) }}
            </span>
          </div>
        </header>

        <!-- Tags -->
        <div v-if="article.tags?.length" class="flex flex-wrap gap-2 mb-8">
          <NuxtLink
            v-for="tag in article.tags"
            :key="tag.id"
            :to="`/tags/${tag.slug}`"
            class="px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700"
          >
            #{{ tag.name }}
          </NuxtLink>
        </div>

        <!-- Article Content -->
        <ArticleContent :content="article.content_html || ''" />

        <!-- Code Examples -->
        <div v-if="article.code_examples?.length" class="mt-12 space-y-8">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            ตัวอย่างโค้ด
          </h2>
          <div v-for="example in article.code_examples" :key="example.id" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ example.title }}
            </h3>
            <p v-if="example.description" class="text-gray-600 dark:text-gray-400">
              {{ example.description }}
            </p>
            <CodeBlock
              :code="example.code"
              :language="example.language"
              :output="example.output"
              :output-type="example.output_type"
              :is-runnable="example.is_runnable"
            />
          </div>
        </div>

        <!-- Article Actions -->
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Feedback -->
            <div class="flex items-center gap-4">
              <span class="text-gray-600 dark:text-gray-400">บทความนี้มีประโยชน์หรือไม่?</span>
              <button
                @click="submitFeedback(true)"
                class="p-2 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 text-gray-500 hover:text-green-600"
              >
                <HandThumbUpIcon class="w-6 h-6" />
              </button>
              <button
                @click="submitFeedback(false)"
                class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-500 hover:text-red-600"
              >
                <HandThumbDownIcon class="w-6 h-6" />
              </button>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <button
                @click="toggleBookmark"
                class="btn-secondary flex items-center gap-2"
              >
                <BookmarkIcon v-if="!isBookmarked" class="w-5 h-5" />
                <BookmarkSolidIcon v-else class="w-5 h-5 text-primary-600" />
                บุ๊คมาร์ค
              </button>
              <button
                @click="shareArticle"
                class="btn-secondary flex items-center gap-2"
              >
                <ShareIcon class="w-5 h-5" />
                แชร์
              </button>
            </div>
          </div>
        </div>

        <!-- Related Articles -->
        <div v-if="relatedArticles?.length" class="mt-12">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            บทความที่เกี่ยวข้อง
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <ArticleCard
              v-for="related in relatedArticles"
              :key="related.id"
              :article="related"
            />
          </div>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import {
  ChevronRightIcon,
  ClockIcon,
  EyeIcon,
  CalendarIcon,
  BookmarkIcon,
  ShareIcon,
  HandThumbUpIcon,
  HandThumbDownIcon,
} from '@heroicons/vue/24/outline'
import { BookmarkIcon as BookmarkSolidIcon } from '@heroicons/vue/24/solid'

const route = useRoute()
const config = useRuntimeConfig()

// Get article slug from route
const slug = computed(() => {
  const slugParts = route.params.slug
  return Array.isArray(slugParts) ? slugParts.join('/') : slugParts
})

// Fetch article data
const { data: articleData, pending, error } = await useFetch<any>(
  () => `${config.public.apiBase}/articles/${slug.value}`,
  {
    key: `article-${slug.value}`,
    transform: (response) => response.data,
  }
)

const article = computed(() => articleData.value?.article)

// Fetch related articles
const { data: relatedData } = await useFetch<any>(
  () => `${config.public.apiBase}/articles/${slug.value}/related`,
  {
    key: `related-${slug.value}`,
    transform: (response) => response.data?.articles || [],
  }
)

const relatedArticles = computed(() => relatedData.value || [])

// Page meta
useHead({
  title: () => article.value?.title || 'บทความ',
  meta: [
    { name: 'description', content: () => article.value?.summary || '' },
    { property: 'og:title', content: () => article.value?.title || '' },
    { property: 'og:description', content: () => article.value?.summary || '' },
  ],
})

// State
const isBookmarked = ref(false)

// Computed
const difficultyLabel = computed(() => {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[article.value?.difficulty] || article.value?.difficulty
})

const difficultyClass = computed(() => {
  const classes: Record<string, string> = {
    beginner: 'difficulty-beginner',
    intermediate: 'difficulty-intermediate',
    advanced: 'difficulty-advanced',
  }
  return classes[article.value?.difficulty] || 'difficulty-beginner'
})

// Methods
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const submitFeedback = async (isHelpful: boolean) => {
  // TODO: Implement feedback submission
  console.log('Feedback:', isHelpful)
}

const toggleBookmark = async () => {
  // TODO: Implement bookmark toggle
  isBookmarked.value = !isBookmarked.value
}

const shareArticle = async () => {
  if (navigator.share) {
    await navigator.share({
      title: article.value?.title,
      text: article.value?.summary,
      url: window.location.href,
    })
  } else {
    // Fallback: copy to clipboard
    await navigator.clipboard.writeText(window.location.href)
    alert('คัดลอกลิงก์แล้ว!')
  }
}
</script>
