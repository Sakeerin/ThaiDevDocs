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
        <ArticleMeta :article="article" />

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
            <CodePlayground
              v-if="example.is_runnable"
              :code="example.code"
              :language="example.language"
              :title="example.title"
            />
            <CodeBlock
              v-else
              :code="example.code"
              :language="example.language"
              :output="example.output"
              :output-type="example.output_type"
              :is-runnable="false"
            />
          </div>
        </div>

        <BrowserCompat :article-slug="article.slug" />

        <ArticleRating :article="article" />

        <ArticleNavigation
          :prev="article.navigation?.prev"
          :next="article.navigation?.next"
          :topic="article.topic"
        />

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

        <CommentSection
          v-if="article.allow_comments"
          :article-slug="article.slug"
          :comments-count="article.comments_count"
        />
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import { getArticleSlugFromRoute } from '~/utils/article'
import { getArticlePath } from '~/utils/content'

const route = useRoute()
const config = useRuntimeConfig()
const { isAuthenticated } = useAuth()
const { startTracking, stopTracking } = useReadingHistory()

const articleSlug = computed(() => getArticleSlugFromRoute(route.params.slug))
const siteUrl = config.public.siteUrl || 'http://localhost:3000'

const { data: articleData, pending, error } = await useFetch<any>(
  () => `${config.public.apiBase}/articles/${articleSlug.value}`,
  {
    key: () => `article-${articleSlug.value}`,
    transform: (response) => response.data,
    watch: [articleSlug],
  },
)

const article = computed(() => articleData.value?.article)

const { data: relatedData } = await useFetch<any>(
  () => `${config.public.apiBase}/articles/${articleSlug.value}/related`,
  {
    key: () => `related-${articleSlug.value}`,
    transform: (response) => response.data?.articles || [],
    watch: [articleSlug],
  },
)

const relatedArticles = computed(() => relatedData.value || [])

const articleUrl = computed(() => {
  if (!article.value) return siteUrl
  return `${siteUrl}${getArticlePath(article.value)}`
})

const structuredData = computed(() => {
  if (!article.value) return null

  return {
    '@context': 'https://schema.org',
    '@type': 'TechArticle',
    headline: article.value.meta_title || article.value.title,
    description: article.value.meta_description || article.value.summary,
    author: article.value.author ? {
      '@type': 'Person',
      name: article.value.author.name,
    } : undefined,
    datePublished: article.value.published_at,
    dateModified: article.value.updated_at,
    url: articleUrl.value,
    inLanguage: 'th',
    proficiencyLevel: article.value.difficulty,
  }
})

useHead(() => {
  if (!article.value) {
    return { title: 'บทความ' }
  }

  const title = article.value.meta_title || article.value.title
  const description = article.value.meta_description || article.value.summary || ''
  const canonical = article.value.canonical_url || articleUrl.value

  return {
    title,
    link: [{ rel: 'canonical', href: canonical }],
    meta: [
      { name: 'description', content: description },
      { property: 'og:title', content: title },
      { property: 'og:description', content: description },
      { property: 'og:type', content: 'article' },
      { property: 'og:url', content: articleUrl.value },
      { property: 'og:locale', content: 'th_TH' },
      { name: 'twitter:card', content: 'summary_large_image' },
      { name: 'twitter:title', content: title },
      { name: 'twitter:description', content: description },
      { name: 'article:published_time', content: article.value.published_at || '' },
      { name: 'article:modified_time', content: article.value.updated_at || '' },
    ],
    script: structuredData.value ? [{
      type: 'application/ld+json',
      innerHTML: JSON.stringify(structuredData.value),
    }] : [],
  }
})

watch(
  [article, isAuthenticated],
  ([currentArticle, authed]) => {
    if (currentArticle?.id && authed) {
      startTracking(currentArticle.id)
    } else {
      stopTracking()
    }
  },
  { immediate: true },
)

watch(articleSlug, () => {
  stopTracking()
})
</script>
