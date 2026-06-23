<template>
  <div>
    <NuxtLayout name="docs">
      <div class="container max-w-4xl mx-auto px-4 py-8 lg:py-12">
        <!-- Header -->
        <header class="mb-10">
          <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
            <NuxtLink to="/" class="hover:text-primary-600 dark:hover:text-primary-400">
              หน้าแรก
            </NuxtLink>
            <ChevronRightIcon class="w-4 h-4" />
            <span class="text-gray-900 dark:text-white">เอกสาร</span>
          </nav>

          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
            เอกสารทั้งหมด
          </h1>
          <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mb-6">
            ค้นหาและเรียนรู้จากเอกสารด้านการพัฒนาซอฟต์แวร์ภาษาไทย จัดหมวดหมู่ตามเทคโนโลยีและหัวข้อ
          </p>

          <button
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm"
            @click="isSearchOpen = true"
          >
            <MagnifyingGlassIcon class="w-5 h-5" />
            ค้นหาในเอกสาร...
            <kbd class="hidden sm:inline-flex items-center gap-0.5 px-2 py-0.5 bg-white dark:bg-gray-900 rounded text-xs border border-gray-300 dark:border-gray-600">
              ⌘K
            </kbd>
          </button>
        </header>

        <!-- Summary stats -->
        <div
          v-if="!pending && !error"
          class="grid grid-cols-3 gap-4 mb-10 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800"
        >
          <div class="text-center">
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ stats.categories }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">หมวดหมู่</p>
          </div>
          <div class="text-center border-x border-gray-200 dark:border-gray-800">
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ stats.topics }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">หัวข้อ</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ stats.articles }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">บทความ</p>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="pending" class="space-y-8">
          <div v-for="i in 3" :key="i" class="animate-pulse">
            <div class="h-8 bg-gray-200 dark:bg-gray-800 rounded w-1/3 mb-4" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div v-for="j in 4" :key="j" class="h-16 bg-gray-200 dark:bg-gray-800 rounded-lg" />
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-12">
          <p class="text-gray-600 dark:text-gray-400 mb-4">โหลดเอกสารไม่สำเร็จ</p>
          <button type="button" class="btn-primary" @click="refresh()">
            ลองใหม่
          </button>
        </div>

        <!-- Categories -->
        <div v-else-if="categories.length > 0" class="space-y-10">
          <section
            v-for="category in categories"
            :key="category.slug"
            :id="category.slug"
          >
            <div class="flex items-start gap-4 mb-5">
              <div
                class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                :style="{ backgroundColor: (category.color || defaultCategoryColor) + '20' }"
              >
                <component
                  :is="getCategoryIcon(category.icon)"
                  class="w-6 h-6"
                  :style="{ color: category.color || defaultCategoryColor }"
                />
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ category.name }}
                </h2>
                <p v-if="category.description" class="text-gray-600 dark:text-gray-400 mt-1">
                  {{ category.description }}
                </p>
              </div>
            </div>

            <div v-if="category.topics.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <NuxtLink
                v-for="topic in category.topics"
                :key="topic.slug"
                :to="`/docs/${category.slug}/${topic.slug}`"
                class="group flex items-start gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50/50 dark:hover:bg-primary-950/20 transition-all"
              >
                <DocumentTextIcon class="w-5 h-5 text-gray-400 group-hover:text-primary-500 flex-shrink-0 mt-0.5" />
                <div class="min-w-0">
                  <h3 class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    {{ topic.name }}
                  </h3>
                  <p v-if="topic.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ topic.description }}
                  </p>
                  <p v-if="topic.article_count" class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                    {{ topic.article_count }} บทความ
                  </p>
                </div>
                <ArrowRightIcon class="w-4 h-4 text-gray-300 group-hover:text-primary-500 flex-shrink-0 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
              </NuxtLink>
            </div>

            <p v-else class="text-sm text-gray-500 dark:text-gray-400 pl-16">
              ยังไม่มีหัวข้อในหมวดนี้
            </p>
          </section>
        </div>

        <p v-else class="text-center text-gray-500 dark:text-gray-400 py-12">
          ยังไม่มีเอกสาร
        </p>

        <!-- CTA -->
        <div
          v-if="!pending && !error"
          class="mt-12 p-6 rounded-2xl bg-gradient-to-br from-primary-50 to-primary-100/50 dark:from-primary-950/30 dark:to-primary-900/20 border border-primary-200 dark:border-primary-800"
        >
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            ต้องการเรียนรู้แบบเป็นระบบ?
          </h2>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            ลองเส้นทางการเรียนรู้ที่จัดลำดับบทเรียนให้แล้ว
          </p>
          <NuxtLink to="/learn" class="btn-primary inline-flex">
            ดูเส้นทางการเรียนรู้
          </NuxtLink>
        </div>
      </div>
    </NuxtLayout>

    <SearchModal v-model="isSearchOpen" />
  </div>
</template>

<script setup lang="ts">
import {
  ChevronRightIcon,
  MagnifyingGlassIcon,
  DocumentTextIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline'
import { getCategoryIcon, defaultCategoryColor } from '~/utils/content'

definePageMeta({
  layout: false,
})

useHead({
  title: 'เอกสาร',
  meta: [
    {
      name: 'description',
      content: 'เอกสารด้านการพัฒนาซอฟต์แวร์ภาษาไทย จัดหมวดหมู่ตามเทคโนโลยีและหัวข้อ',
    },
  ],
})

const isSearchOpen = ref(false)
const { categories, pending, error, refresh } = useDocsNavigation()

const stats = computed(() => {
  const cats = categories.value
  return {
    categories: cats.length,
    topics: cats.reduce((sum, c) => sum + c.topics.length, 0),
    articles: cats.reduce(
      (sum, c) => sum + c.topics.reduce((tSum, t) => tSum + (t.article_count ?? 0), 0),
      0,
    ),
  }
})

onMounted(() => {
  const handleKeydown = (e: KeyboardEvent) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault()
      isSearchOpen.value = true
    }
  }
  window.addEventListener('keydown', handleKeydown)
  onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
})
</script>
