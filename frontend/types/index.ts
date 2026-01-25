export interface User {
  id: number
  name: string
  email: string
  username?: string
  avatar_url?: string
  bio?: string
  website?: string
  role: 'admin' | 'editor' | 'contributor' | 'user'
  contribution_points: number
  articles_count: number
  created_at: string
  updated_at: string
}

export interface Category {
  id: number
  name: string
  name_en: string
  slug: string
  description?: string
  icon?: string
  color?: string
  order_index: number
  is_active: boolean
  topics_count: number
  articles_count: number
}

export interface Topic {
  id: number
  category_id: number
  name: string
  name_en: string
  slug: string
  description?: string
  icon?: string
  order_index: number
  is_active: boolean
  articles_count: number
  category?: Category
}

export interface Article {
  id: number
  topic_id: number
  author_id: number
  title: string
  slug: string
  summary?: string
  content: string
  difficulty: 'beginner' | 'intermediate' | 'advanced'
  article_type: 'guide' | 'tutorial' | 'reference' | 'example'
  status: 'draft' | 'pending_review' | 'published' | 'archived'
  reading_time: number
  view_count: number
  is_featured: boolean
  allow_comments: boolean
  meta_title?: string
  meta_description?: string
  published_at?: string
  created_at: string
  updated_at: string
  topic?: Topic
  author?: User
  tags?: Tag[]
}

export interface Tag {
  id: number
  name: string
  name_en?: string
  slug: string
  color?: string
  articles_count: number
}

export interface Bookmark {
  id: number
  user_id: number
  article_id: number
  collection_id?: number
  notes?: string
  created_at: string
  article?: Article
}

export interface Collection {
  id: number
  user_id: number
  name: string
  description?: string
  is_public: boolean
  bookmarks_count: number
  created_at: string
}

export interface ReadingHistory {
  id: number
  user_id: number
  article_id: number
  reading_progress: number
  last_read_at: string
  time_spent: number
  article?: Article
}

export interface Comment {
  id: number
  article_id: number
  user_id: number
  parent_id?: number
  content: string
  status: 'pending' | 'approved' | 'rejected' | 'spam'
  upvotes_count: number
  downvotes_count: number
  created_at: string
  updated_at: string
  user?: User
  replies?: Comment[]
}

export interface LearningPath {
  id: number
  name: string
  slug: string
  description?: string
  difficulty: 'beginner' | 'intermediate' | 'advanced'
  estimated_hours: number
  is_published: boolean
  items_count: number
  enrollments_count: number
  items?: LearningPathItem[]
}

export interface LearningPathItem {
  id: number
  learning_path_id: number
  article_id: number
  order_index: number
  is_required: boolean
  article?: Article
}

export interface UserLearningProgress {
  id: number
  user_id: number
  learning_path_id: number
  completed_items: number[]
  started_at: string
  completed_at?: string
  learning_path?: LearningPath
}

export interface SearchResult {
  articles: Article[]
  total: number
  processing_time_ms: number
}

export interface ApiResponse<T> {
  data: T
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
  }
}
