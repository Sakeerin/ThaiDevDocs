import type { Category } from '~/types'

export interface DocsNavTopic {
  id: number
  name: string
  name_en?: string
  slug: string
  description?: string
  icon?: string | null
  article_count?: number
}

export interface DocsNavCategory extends Pick<Category, 'id' | 'name' | 'slug' | 'icon' | 'color' | 'description'> {
  topics: DocsNavTopic[]
}

interface ApiSuccess<T> {
  success: boolean
  data: T
}

export function useDocsNavigation() {
  const config = useRuntimeConfig()

  const { data, pending, error, refresh } = useAsyncData<DocsNavCategory[]>(
    'docs-navigation',
    async () => {
      const base = config.public.apiBase

      const categoriesRes = await $fetch<ApiSuccess<{ categories: Category[] }>>(`${base}/categories`)
      const categories = categoriesRes.data.categories

      return Promise.all(
        categories.map(async (category) => {
          const topicsRes = await $fetch<ApiSuccess<{ topics: DocsNavTopic[] }>>(
            `${base}/categories/${category.slug}/topics`,
          )

          return {
            id: category.id,
            name: category.name,
            slug: category.slug,
            icon: category.icon,
            color: category.color,
            description: category.description,
            topics: topicsRes.data.topics,
          }
        }),
      )
    },
  )

  return {
    categories: computed(() => data.value ?? []),
    pending,
    error,
    refresh,
  }
}
