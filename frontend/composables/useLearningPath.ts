export interface LearningPathSummary {
  id: number
  title: string
  slug: string
  description?: string | null
  difficulty: string
  estimated_hours?: number | null
  thumbnail?: string | null
  is_featured?: boolean
  enrollment_count?: number
  items_count?: number
}

export interface LearningPathProgress {
  enrolled: boolean
  progress?: {
    started_at?: string
    completed_at?: string | null
    progress_percentage: number
    current_item_id?: number | null
    completed_item_ids: number[]
    is_completed?: boolean
  }
}

export interface MyLearningEntry {
  path: {
    id: number
    title: string
    slug: string
    thumbnail?: string | null
    difficulty: string
    estimated_hours?: number | null
    total_items: number
  } | null
  started_at?: string
  completed_at?: string | null
  progress_percentage: number
  is_completed: boolean
}

export const useLearningPath = () => {
  const { $api } = useNuxtApp()

  const fetchPaths = async (params?: Record<string, string | number | boolean>) => {
    const config = useRuntimeConfig()
    const response = await $fetch<{ data: LearningPathSummary[] }>(
      `${config.public.apiBase}/learning-paths`,
      { params },
    )
    return response.data || []
  }

  const fetchPath = async (slug: string) => {
    const config = useRuntimeConfig()
    const response = await $fetch<{ data: { path: any; user_progress?: any } }>(
      `${config.public.apiBase}/learning-paths/${slug}`,
    )
    return response.data
  }

  const fetchMyLearning = async () => {
    const response = await $api<{ data: { learning: MyLearningEntry[] } }>('/my-learning')
    return response.data.learning || []
  }

  const fetchProgress = async (slug: string) => {
    const response = await $api<{ data: LearningPathProgress }>(`/learning-paths/${slug}/progress`)
    return response.data
  }

  const enroll = async (slug: string) => {
    const response = await $api<{ data: { enrolled: boolean; progress: { progress_percentage: number } } }>(
      `/learning-paths/${slug}/enroll`,
      { method: 'POST' },
    )
    return response.data
  }

  const markItemComplete = async (slug: string, itemId: number) => {
    const response = await $api<{ data: { progress: LearningPathProgress['progress'] } }>(
      `/learning-paths/${slug}/progress`,
      {
        method: 'PATCH',
        body: { completed_item_id: itemId },
      },
    )
    return response.data.progress
  }

  return {
    fetchPaths,
    fetchPath,
    fetchMyLearning,
    fetchProgress,
    enroll,
    markItemComplete,
  }
}
