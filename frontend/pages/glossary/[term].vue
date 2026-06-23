<template>
  <div>
    <div v-if="pending" class="container mx-auto px-4 py-20 flex justify-center">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin" />
    </div>

    <div v-else-if="error || !termData" class="container mx-auto px-4 py-20 text-center">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">ไม่พบคำศัพท์</h1>
      <NuxtLink to="/glossary" class="btn-primary">กลับไปอภิธานศัพท์</NuxtLink>
    </div>

    <div v-else class="container mx-auto px-4 py-8 max-w-3xl">
      <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
        <NuxtLink to="/glossary" class="hover:text-primary-600">อภิธานศัพท์</NuxtLink>
        <ChevronRightIcon class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white">{{ termData.term }}</span>
      </nav>

      <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
          {{ termData.term }}
        </h1>
        <p v-if="termData.term_en" class="text-xl text-gray-500 dark:text-gray-400">
          {{ termData.term_en }}
        </p>
        <p v-if="termData.pronunciation" class="text-sm text-gray-500 mt-2">
          การออกเสียง: {{ termData.pronunciation }}
        </p>
      </header>

      <div class="prose prose-lg dark:prose-invert max-w-none mb-8">
        <p v-if="termData.definition_short" class="lead text-gray-600 dark:text-gray-300">
          {{ termData.definition_short }}
        </p>
        <div v-if="termData.definition" class="whitespace-pre-wrap">
          {{ termData.definition }}
        </div>
      </div>

      <section v-if="termData.etymology" class="mb-8 p-6 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">ที่มา</h2>
        <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ termData.etymology }}</p>
      </section>

      <section v-if="relatedTerms.length" class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">คำที่เกี่ยวข้อง</h2>
        <div class="flex flex-wrap gap-2">
          <NuxtLink
            v-for="related in relatedTerms"
            :key="related"
            :to="`/glossary/${slugify(related)}`"
            class="px-3 py-1.5 rounded-full bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 text-sm hover:bg-primary-100 dark:hover:bg-primary-900/40"
          >
            {{ related }}
          </NuxtLink>
        </div>
      </section>

      <section v-if="externalLinks.length" class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">ลิงก์เพิ่มเติม</h2>
        <ul class="space-y-2">
          <li v-for="(link, index) in externalLinks" :key="index">
            <a
              :href="link.url || link"
              target="_blank"
              rel="noopener noreferrer"
              class="text-primary-600 hover:underline"
            >
              {{ link.title || link.url || link }}
            </a>
          </li>
        </ul>
      </section>

      <div class="text-sm text-gray-500">
        {{ termData.view_count?.toLocaleString('th-TH') || 0 }} ครั้งที่ดู
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChevronRightIcon } from '@heroicons/vue/24/outline'

interface GlossaryTermDetail {
  id: number
  term: string
  term_en?: string | null
  slug: string
  definition?: string | null
  definition_short?: string | null
  pronunciation?: string | null
  etymology?: string | null
  related_terms?: string[] | string | null
  external_links?: Array<{ title?: string; url: string } | string> | string | null
  view_count?: number
}

const route = useRoute()
const config = useRuntimeConfig()
const termSlug = computed(() => route.params.term as string)

const { data, pending, error } = await useFetch<{ data: { term: GlossaryTermDetail } }>(
  () => `${config.public.apiBase}/glossary/${termSlug.value}`,
  {
    key: () => `glossary-${termSlug.value}`,
    watch: [termSlug],
  },
)

const termData = computed(() => data.value?.data?.term)

const relatedTerms = computed(() => {
  const raw = termData.value?.related_terms
  if (!raw) return []
  if (Array.isArray(raw)) return raw
  try {
    return JSON.parse(raw as string)
  } catch {
    return String(raw).split(',').map(s => s.trim()).filter(Boolean)
  }
})

const externalLinks = computed(() => {
  const raw = termData.value?.external_links
  if (!raw) return []
  if (Array.isArray(raw)) return raw
  try {
    return JSON.parse(raw as string)
  } catch {
    return []
  }
})

const slugify = (text: string) => text.toLowerCase().replace(/\s+/g, '-')

useHead(() => ({
  title: termData.value?.term || 'คำศัพท์',
  meta: [{
    name: 'description',
    content: termData.value?.definition_short || termData.value?.definition || '',
  }],
}))
</script>
