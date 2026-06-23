<template>
  <header class="mb-8">
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

    <div class="flex flex-wrap items-center gap-2 mb-4">
      <span v-if="article.topic?.name" class="badge-primary">{{ article.topic.name }}</span>
      <span :class="difficultyClass">{{ difficultyLabel }}</span>
      <span v-if="article.article_type" class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
        {{ typeLabel }}
      </span>
    </div>

    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
      {{ article.title }}
    </h1>

    <p v-if="article.summary" class="text-lg text-gray-600 dark:text-gray-400 mb-6">
      {{ article.summary }}
    </p>

    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
      <span v-if="article.author" class="flex items-center gap-2">
        <span class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-700 dark:text-primary-300 text-xs font-medium">
          {{ article.author.name?.charAt(0).toUpperCase() }}
        </span>
        {{ article.author.name }}
      </span>
      <span class="flex items-center gap-1">
        <ClockIcon class="w-4 h-4" />
        {{ article.reading_time }} นาที
      </span>
      <span v-if="article.view_count" class="flex items-center gap-1">
        <EyeIcon class="w-4 h-4" />
        {{ article.view_count.toLocaleString('th-TH') }} วิว
      </span>
      <span v-if="article.published_at" class="flex items-center gap-1">
        <CalendarIcon class="w-4 h-4" />
        {{ formatDate(article.published_at) }}
      </span>
      <span v-if="article.updated_at && article.updated_at !== article.published_at" class="flex items-center gap-1">
        <ArrowPathIcon class="w-4 h-4" />
        อัปเดต {{ formatDate(article.updated_at) }}
      </span>
    </div>

    <div v-if="article.tags?.length" class="flex flex-wrap gap-2 mt-6">
      <NuxtLink
        v-for="tag in article.tags"
        :key="tag.id"
        :to="`/tags/${tag.slug}`"
        class="px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700"
      >
        #{{ tag.name }}
      </NuxtLink>
    </div>
  </header>
</template>

<script setup lang="ts">
import {
  ChevronRightIcon,
  ClockIcon,
  EyeIcon,
  CalendarIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps<{
  article: {
    title: string
    summary?: string | null
    difficulty?: string
    article_type?: string
    reading_time?: number
    view_count?: number
    published_at?: string | null
    updated_at?: string | null
    author?: { name: string } | null
    topic?: {
      name: string
      slug: string
      category?: { name: string; slug: string } | null
    } | null
    tags?: Array<{ id: number; name: string; slug: string }>
  }
}>()

const difficultyLabel = computed(() => {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[props.article.difficulty || ''] || props.article.difficulty
})

const difficultyClass = computed(() => {
  const classes: Record<string, string> = {
    beginner: 'difficulty-beginner',
    intermediate: 'difficulty-intermediate',
    advanced: 'difficulty-advanced',
  }
  return classes[props.article.difficulty || ''] || 'difficulty-beginner'
})

const typeLabel = computed(() => {
  const labels: Record<string, string> = {
    guide: 'คู่มือ',
    tutorial: 'บทเรียน',
    reference: 'อ้างอิง',
    example: 'ตัวอย่าง',
    glossary: 'อภิธานศัพท์',
  }
  return labels[props.article.article_type || ''] || props.article.article_type
})

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>
