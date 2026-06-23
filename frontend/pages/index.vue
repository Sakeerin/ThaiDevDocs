<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-primary-50/50 dark:from-gray-950 dark:to-primary-950/30">
      <div class="absolute inset-0 bg-grid-pattern opacity-5" />
      <div class="container mx-auto px-4 py-20 lg:py-32 relative">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
            เอกสารสำหรับ
            <span class="text-gradient">นักพัฒนาภาษาไทย</span>
          </h1>
          <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
            แหล่งความรู้ด้านการพัฒนาซอฟต์แวร์ภาษาไทยที่ครบถ้วน เข้าถึงง่าย และทันสมัยที่สุด
          </p>

          <!-- Search Bar -->
          <div class="max-w-xl mx-auto mb-8">
            <button
              @click="isSearchOpen = true"
              class="w-full flex items-center gap-3 px-6 py-4 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 text-gray-500 hover:border-primary-500 transition-colors"
            >
              <MagnifyingGlassIcon class="w-6 h-6" />
              <span class="flex-1 text-left text-lg">ค้นหาเอกสาร...</span>
              <kbd class="hidden sm:inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm text-gray-500 border border-gray-300 dark:border-gray-600">
                ⌘K
              </kbd>
            </button>
          </div>

          <!-- Quick Links -->
          <div class="flex flex-wrap justify-center gap-3">
            <NuxtLink :to="startLearningLink" class="btn-primary">
              เริ่มต้นเรียนรู้
            </NuxtLink>
            <NuxtLink to="/learn" class="btn-outline">
              เส้นทางการเรียนรู้
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Categories -->
    <section class="py-16 lg:py-24">
      <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">
          หมวดหมู่เอกสาร
        </h2>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="i in 6"
            :key="i"
            class="card p-6 animate-pulse"
          >
            <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-800 mb-4" />
            <div class="h-6 bg-gray-200 dark:bg-gray-800 rounded w-2/3 mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-full mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-4/5" />
          </div>
        </div>

        <div v-else-if="categories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <NuxtLink
            v-for="category in categories"
            :key="category.slug"
            :to="`/docs/${category.slug}`"
            class="card p-6 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all group"
          >
            <div
              class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center"
              :style="{ backgroundColor: (category.color || defaultCategoryColor) + '20' }"
            >
              <component
                :is="getCategoryIcon(category.icon)"
                class="w-6 h-6"
                :style="{ color: category.color || defaultCategoryColor }"
              />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
              {{ category.name }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              {{ category.description }}
            </p>
            <span class="text-sm text-primary-600 dark:text-primary-400 flex items-center gap-1">
              {{ category.topics_count }} หัวข้อ
              <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </span>
          </NuxtLink>
        </div>

        <p v-else class="text-center text-gray-500 dark:text-gray-400">
          ยังไม่มีหมวดหมู่เอกสาร
        </p>
      </div>
    </section>

    <!-- Featured Articles -->
    <section class="py-16 lg:py-24 bg-gray-50 dark:bg-gray-900/50">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-12">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
            บทความแนะนำ
          </h2>
          <NuxtLink to="/search" class="text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            ดูทั้งหมด
            <ArrowRightIcon class="w-4 h-4" />
          </NuxtLink>
        </div>

        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="card p-6 animate-pulse">
            <div class="h-5 bg-gray-200 dark:bg-gray-800 rounded w-1/3 mb-3" />
            <div class="h-6 bg-gray-200 dark:bg-gray-800 rounded w-full mb-2" />
            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-full mb-4" />
            <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/2" />
          </div>
        </div>

        <div v-else-if="featuredArticles.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <ArticleCard
            v-for="article in featuredArticles"
            :key="article.id"
            :article="article"
          />
        </div>

        <p v-else class="text-center text-gray-500 dark:text-gray-400">
          ยังไม่มีบทความแนะนำ
        </p>
      </div>
    </section>

    <!-- Recent & Popular Articles -->
    <section v-if="!isLoading && (recentArticles.length > 0 || popularArticles.length > 0)" class="py-16 lg:py-24">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Recent -->
          <div v-if="recentArticles.length > 0">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
              บทความล่าสุด
            </h2>
            <div class="space-y-4">
              <ArticleCard
                v-for="article in recentArticles"
                :key="`recent-${article.id}`"
                :article="article"
              />
            </div>
          </div>

          <!-- Popular -->
          <div v-if="popularArticles.length > 0">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
              บทความยอดนิยม
            </h2>
            <div class="space-y-4">
              <ArticleCard
                v-for="article in popularArticles"
                :key="`popular-${article.id}`"
                :article="article"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 lg:py-24 bg-gray-50 dark:bg-gray-900/50">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">
              {{ stats.articles }}+
            </div>
            <div class="text-gray-600 dark:text-gray-400">บทความ</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">
              {{ stats.topics }}+
            </div>
            <div class="text-gray-600 dark:text-gray-400">หัวข้อ</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">
              {{ stats.categories }}+
            </div>
            <div class="text-gray-600 dark:text-gray-400">หมวดหมู่</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">
              100%
            </div>
            <div class="text-gray-600 dark:text-gray-400">ฟรี</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 lg:py-24 bg-primary-600">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
          พร้อมที่จะเริ่มต้นหรือยัง?
        </h2>
        <p class="text-primary-100 mb-8 max-w-2xl mx-auto">
          เริ่มต้นเรียนรู้การพัฒนาซอฟต์แวร์ด้วยเอกสารภาษาไทยที่เข้าใจง่าย
        </p>
        <NuxtLink :to="ctaLink" class="btn bg-white text-primary-600 hover:bg-primary-50">
          เริ่มต้นเลย
        </NuxtLink>
      </div>
    </section>

    <!-- Search Modal -->
    <SearchModal v-model="isSearchOpen" />
  </div>
</template>

<script setup lang="ts">
import { MagnifyingGlassIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'
import type { Article, Category } from '~/types'
import { getArticlePath, getCategoryIcon, defaultCategoryColor } from '~/utils/content'

definePageMeta({
  layout: 'default',
})

useHead({
  title: 'หน้าแรก',
})

const config = useRuntimeConfig()
const isSearchOpen = ref(false)

interface ApiSuccess<T> {
  success: boolean
  data: T
  meta?: {
    total: number
  }
}

interface HomepageData {
  categories: Category[]
  featured: Article[]
  recent: Article[]
  popular: Article[]
  totalArticles: number
}

const { data: homepageData, pending: isLoading } = await useAsyncData<HomepageData>(
  'homepage',
  async () => {
    const base = config.public.apiBase

    const [categoriesRes, featuredRes, recentRes, popularRes, articlesMetaRes] = await Promise.all([
      $fetch<ApiSuccess<{ categories: Category[] }>>(`${base}/categories`),
      $fetch<ApiSuccess<{ articles: Article[] }>>(`${base}/articles/featured`, { params: { limit: 6 } }),
      $fetch<ApiSuccess<{ articles: Article[] }>>(`${base}/articles/recent`, { params: { limit: 3 } }),
      $fetch<ApiSuccess<{ articles: Article[] }>>(`${base}/articles/popular`, { params: { limit: 3 } }),
      $fetch<ApiSuccess<Article[]>>(`${base}/articles`, { params: { per_page: 1 } }),
    ])

    return {
      categories: categoriesRes.data.categories,
      featured: featuredRes.data.articles,
      recent: recentRes.data.articles,
      popular: popularRes.data.articles,
      totalArticles: articlesMetaRes.meta?.total ?? 0,
    }
  },
)

const categories = computed(() => homepageData.value?.categories ?? [])

const featuredArticles = computed(() => {
  const featured = homepageData.value?.featured ?? []
  if (featured.length > 0) return featured

  const recent = homepageData.value?.recent ?? []
  return recent.slice(0, 6)
})

const recentArticles = computed(() => {
  const recent = homepageData.value?.recent ?? []
  const featuredIds = new Set(featuredArticles.value.map((a) => a.id))
  return recent.filter((a) => !featuredIds.has(a.id)).slice(0, 3)
})

const popularArticles = computed(() => {
  const popular = homepageData.value?.popular ?? []
  const shownIds = new Set([
    ...featuredArticles.value.map((a) => a.id),
    ...recentArticles.value.map((a) => a.id),
  ])
  return popular.filter((a) => !shownIds.has(a.id)).slice(0, 3)
})

const stats = computed(() => ({
  articles: homepageData.value?.totalArticles ?? 0,
  topics: categories.value.reduce((sum, cat) => sum + (cat.topics_count ?? 0), 0),
  categories: categories.value.length,
}))

const startLearningLink = computed(() => {
  const first = categories.value[0]
  return first ? `/docs/${first.slug}` : '/docs'
})

const ctaLink = computed(() => {
  const first = featuredArticles.value[0]
  return first ? getArticlePath(first) : startLearningLink.value
})
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
</style>
