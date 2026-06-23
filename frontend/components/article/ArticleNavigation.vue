<template>
  <nav
    v-if="prev || next"
    class="mt-12 grid grid-cols-1 sm:grid-cols-2 gap-4"
    aria-label="Article navigation"
  >
    <NuxtLink
      v-if="prev"
      :to="getArticlePath({ slug: prev.slug, topic })"
      class="group flex flex-col p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors"
    >
      <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1 flex items-center gap-1">
        <ChevronLeftIcon class="w-4 h-4" />
        บทความก่อนหน้า
      </span>
      <span class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 line-clamp-2">
        {{ prev.title }}
      </span>
    </NuxtLink>
    <div v-else class="hidden sm:block" />

    <NuxtLink
      v-if="next"
      :to="getArticlePath({ slug: next.slug, topic })"
      class="group flex flex-col p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors sm:text-right"
    >
      <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1 flex items-center gap-1 sm:justify-end">
        บทความถัดไป
        <ChevronRightIcon class="w-4 h-4" />
      </span>
      <span class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 line-clamp-2">
        {{ next.title }}
      </span>
    </NuxtLink>
  </nav>
</template>

<script setup lang="ts">
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import { getArticlePath } from '~/utils/content'

interface NavArticle {
  id: number
  title: string
  slug: string
}

defineProps<{
  prev?: NavArticle | null
  next?: NavArticle | null
  topic?: {
    slug: string
    category?: { slug: string } | null
  } | null
}>()
</script>
