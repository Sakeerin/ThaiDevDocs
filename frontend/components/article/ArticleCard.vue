<template>
  <NuxtLink
    :to="`/docs/${article.slug}`"
    class="card p-6 hover:shadow-md hover:border-primary-200 dark:hover:border-primary-800 transition-all group"
  >
    <!-- Topic Badge -->
    <div class="flex items-center gap-2 mb-3">
      <span class="badge-primary">
        {{ article.topic?.name }}
      </span>
      <span :class="difficultyClass">
        {{ difficultyLabel }}
      </span>
    </div>

    <!-- Title -->
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors line-clamp-2">
      {{ article.title }}
    </h3>

    <!-- Summary -->
    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
      {{ article.summary }}
    </p>

    <!-- Meta -->
    <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
      <span class="flex items-center gap-1">
        <ClockIcon class="w-4 h-4" />
        {{ article.reading_time }} นาที
      </span>
      <span v-if="article.view_count" class="flex items-center gap-1">
        <EyeIcon class="w-4 h-4" />
        {{ formatNumber(article.view_count) }}
      </span>
    </div>
  </NuxtLink>
</template>

<script setup lang="ts">
import { ClockIcon, EyeIcon } from '@heroicons/vue/24/outline'

interface Article {
  id: number
  title: string
  slug: string
  summary?: string
  difficulty?: string
  reading_time?: number
  view_count?: number
  topic?: {
    name: string
    slug: string
  }
}

const props = defineProps<{
  article: Article
}>()

const difficultyLabel = computed(() => {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[props.article.difficulty || 'beginner'] || props.article.difficulty
})

const difficultyClass = computed(() => {
  const classes: Record<string, string> = {
    beginner: 'difficulty-beginner',
    intermediate: 'difficulty-intermediate',
    advanced: 'difficulty-advanced',
  }
  return classes[props.article.difficulty || 'beginner'] || 'difficulty-beginner'
})

const formatNumber = (num: number) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}
</script>
