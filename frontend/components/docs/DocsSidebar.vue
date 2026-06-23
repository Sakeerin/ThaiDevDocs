<template>
  <nav class="space-y-6">
    <!-- Loading -->
    <div v-if="pending" class="space-y-6">
      <div v-for="i in 3" :key="i" class="animate-pulse">
        <div class="h-5 bg-gray-200 dark:bg-gray-800 rounded w-2/3 mb-3" />
        <div class="space-y-2 ml-7">
          <div v-for="j in 3" :key="j" class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-full" />
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-sm text-red-600 dark:text-red-400">
      <p>โหลดเมนูไม่สำเร็จ</p>
      <button
        type="button"
        class="mt-2 text-primary-600 dark:text-primary-400 hover:underline"
        @click="refresh()"
      >
        ลองใหม่
      </button>
    </div>

    <!-- Navigation -->
    <template v-else>
      <div v-for="category in categories" :key="category.slug">
        <NuxtLink
          :to="`/docs/${category.slug}`"
          class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
          <component :is="getCategoryIcon(category.icon)" class="w-5 h-5 text-gray-500" />
          {{ category.name }}
        </NuxtLink>

        <ul v-if="category.topics.length > 0" class="space-y-1 ml-7">
          <li v-for="topic in category.topics" :key="topic.slug">
            <NuxtLink
              :to="`/docs/${category.slug}/${topic.slug}`"
              class="block py-1.5 px-3 rounded-lg text-sm transition-colors"
              :class="[
                isActive(category.slug, topic.slug)
                  ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-medium'
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              {{ topic.name }}
              <span
                v-if="topic.article_count"
                class="ml-1 text-xs text-gray-400 dark:text-gray-500"
              >
                ({{ topic.article_count }})
              </span>
            </NuxtLink>
          </li>
        </ul>

        <p v-else class="ml-7 text-sm text-gray-400 dark:text-gray-500">
          ยังไม่มีหัวข้อ
        </p>
      </div>

      <p v-if="categories.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
        ยังไม่มีหมวดหมู่เอกสาร
      </p>
    </template>
  </nav>
</template>

<script setup lang="ts">
import { getCategoryIcon } from '~/utils/content'

const route = useRoute()
const { categories, pending, error, refresh } = useDocsNavigation()

const isActive = (categorySlug: string, topicSlug: string) => {
  const parts = route.params.slug
  const segments = Array.isArray(parts) ? parts : parts ? [parts] : []

  return segments[0] === categorySlug && segments[1] === topicSlug
}
</script>
