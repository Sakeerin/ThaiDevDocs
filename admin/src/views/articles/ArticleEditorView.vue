<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <button @click="goBack" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ isEditing ? 'แก้ไขบทความ' : 'สร้างบทความใหม่' }}
          </h1>
          <p v-if="lastSaved" class="text-sm text-gray-500">
            บันทึกล่าสุด: {{ formatTime(lastSaved) }}
          </p>
        </div>
      </div>
      <div class="flex gap-3">
        <button @click="togglePreview" class="btn-outline">
          <EyeIcon v-if="!showFullPreview" class="w-5 h-5 mr-2" />
          <PencilSquareIcon v-else class="w-5 h-5 mr-2" />
          {{ showFullPreview ? 'แก้ไข' : 'ดูตัวอย่าง' }}
        </button>
        <button @click="saveDraft" class="btn-secondary" :disabled="isSaving">
          <ArrowPathIcon v-if="isSaving" class="w-5 h-5 mr-2 animate-spin" />
          บันทึกฉบับร่าง
        </button>
        <button @click="publish" class="btn-primary" :disabled="isSaving">
          {{ isEditing ? 'อัปเดต' : 'เผยแพร่' }}
        </button>
      </div>
    </div>

    <!-- Full Preview Mode -->
    <div v-if="showFullPreview" class="card p-8">
      <article class="prose prose-lg dark:prose-invert max-w-none prose-headings:font-noto">
        <h1>{{ article.title }}</h1>
        <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
          <span v-if="article.difficulty" class="badge-info">{{ getDifficultyLabel(article.difficulty) }}</span>
          <span v-if="article.article_type" class="badge">{{ getTypeLabel(article.article_type) }}</span>
        </div>
        <div v-if="article.summary" class="lead text-gray-600 dark:text-gray-400 mb-8">
          {{ article.summary }}
        </div>
        <div v-html="renderedContent" />
      </article>
    </div>

    <!-- Editor Mode -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Title -->
        <div class="card p-6">
          <label class="label">ชื่อบทความ <span class="text-red-500">*</span></label>
          <input
            v-model="article.title"
            type="text"
            class="input text-xl font-semibold"
            placeholder="ชื่อบทความ..."
            @input="generateSlug"
          />
          <div class="mt-2 flex items-center gap-2">
            <span class="text-sm text-gray-500">/docs/</span>
            <input
              v-model="article.slug"
              type="text"
              class="input text-sm flex-1"
              placeholder="slug-ของ-บทความ"
            />
          </div>
        </div>

        <!-- Editor -->
        <div class="card p-6">
          <label class="label mb-3">เนื้อหา (Markdown) <span class="text-red-500">*</span></label>
          <MarkdownEditor
            ref="editorRef"
            v-model="article.content"
            @open-media="showMediaModal = true"
            placeholder="เขียนเนื้อหาบทความที่นี่..."
          />
        </div>

        <!-- Summary -->
        <div class="card p-6">
          <label class="label">สรุป</label>
          <textarea
            v-model="article.summary"
            class="input resize-none h-24"
            placeholder="สรุปสั้นๆ เกี่ยวกับบทความ (จะแสดงในหน้ารายการ)..."
            maxlength="300"
          />
          <p class="text-xs text-gray-400 mt-1">{{ article.summary?.length || 0 }}/300 ตัวอักษร</p>
        </div>

        <!-- Code Examples -->
        <div class="card p-6">
          <div class="flex items-center justify-between mb-4">
            <label class="label mb-0">ตัวอย่างโค้ดแบบ Interactive</label>
            <button @click="addCodeExample" class="btn-outline btn-sm">
              <PlusIcon class="w-4 h-4 mr-1" />
              เพิ่มตัวอย่าง
            </button>
          </div>

          <div v-if="codeExamples.length === 0" class="text-center py-8 text-gray-500">
            <CodeBracketSquareIcon class="w-12 h-12 mx-auto mb-2 text-gray-300" />
            <p>ยังไม่มีตัวอย่างโค้ด</p>
            <p class="text-sm">เพิ่มตัวอย่างโค้ดที่ผู้อ่านสามารถรันได้</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(example, index) in codeExamples"
              :key="index"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <input
                  v-model="example.title"
                  type="text"
                  class="input input-sm flex-1 mr-4"
                  placeholder="ชื่อตัวอย่าง"
                />
                <div class="flex items-center gap-2">
                  <select v-model="example.language" class="input input-sm w-auto">
                    <option value="javascript">JavaScript</option>
                    <option value="typescript">TypeScript</option>
                    <option value="html">HTML</option>
                    <option value="css">CSS</option>
                    <option value="php">PHP</option>
                    <option value="python">Python</option>
                  </select>
                  <button @click="removeCodeExample(index)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
              <textarea
                v-model="example.code"
                class="input font-mono text-sm h-32 resize-none"
                placeholder="// โค้ดตัวอย่าง..."
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status -->
        <div class="card p-6">
          <label class="label">สถานะ</label>
          <select v-model="article.status" class="input">
            <option value="draft">ฉบับร่าง</option>
            <option value="pending_review">รอตรวจสอบ</option>
            <option value="published">เผยแพร่แล้ว</option>
            <option value="archived">เก็บถาวร</option>
          </select>

          <div v-if="article.status === 'published'" class="mt-4">
            <label class="label">วันที่เผยแพร่</label>
            <input
              v-model="article.published_at"
              type="datetime-local"
              class="input"
            />
          </div>
        </div>

        <!-- Category & Topic -->
        <div class="card p-6 space-y-4">
          <div>
            <label class="label">หมวดหมู่ <span class="text-red-500">*</span></label>
            <select v-model="selectedCategory" @change="onCategoryChange" class="input">
              <option value="">เลือกหมวดหมู่</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="label">หัวข้อ <span class="text-red-500">*</span></label>
            <select v-model="article.topic_id" class="input" :disabled="!selectedCategory">
              <option value="">เลือกหัวข้อ</option>
              <option v-for="topic in filteredTopics" :key="topic.id" :value="topic.id">
                {{ topic.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Difficulty & Type -->
        <div class="card p-6 space-y-4">
          <div>
            <label class="label">ระดับความยาก</label>
            <div class="flex gap-2">
              <button
                v-for="level in difficultyLevels"
                :key="level.value"
                @click="article.difficulty = level.value"
                :class="[
                  'flex-1 py-2 px-3 rounded-lg text-sm font-medium transition-colors border',
                  article.difficulty === level.value
                    ? level.activeClass
                    : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                {{ level.label }}
              </button>
            </div>
          </div>
          <div>
            <label class="label">ประเภทบทความ</label>
            <select v-model="article.article_type" class="input">
              <option value="guide">คู่มือ</option>
              <option value="tutorial">บทเรียน</option>
              <option value="reference">อ้างอิง</option>
              <option value="example">ตัวอย่าง</option>
            </select>
          </div>
        </div>

        <!-- Tags -->
        <div class="card p-6">
          <label class="label">แท็ก</label>
          <div class="flex flex-wrap gap-2 mb-3">
            <span
              v-for="tagId in article.tags"
              :key="tagId"
              class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400"
            >
              {{ getTagName(tagId) }}
              <button @click="removeTag(tagId)" class="hover:text-red-500 ml-1">
                <XMarkIcon class="w-3.5 h-3.5" />
              </button>
            </span>
          </div>
          <div class="relative">
            <input
              v-model="tagSearch"
              type="text"
              class="input"
              placeholder="ค้นหาหรือเพิ่มแท็ก..."
              @focus="showTagDropdown = true"
              @keydown.enter="createTag"
            />
            <div
              v-if="showTagDropdown && (availableTags.length > 0 || tagSearch)"
              class="absolute z-10 top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto"
            >
              <button
                v-for="tag in availableTags"
                :key="tag.id"
                @click="addTag(tag.id)"
                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                {{ tag.name }}
              </button>
              <button
                v-if="tagSearch && !tags.find(t => t.name.toLowerCase() === tagSearch.toLowerCase())"
                @click="createTag"
                class="w-full px-4 py-2 text-left text-sm text-primary-600 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <PlusIcon class="w-4 h-4 inline-block mr-2" />
                สร้าง "{{ tagSearch }}"
              </button>
            </div>
          </div>
        </div>

        <!-- Reading Time -->
        <div class="card p-6">
          <label class="label">เวลาอ่านโดยประมาณ</label>
          <div class="flex items-center gap-2">
            <input
              v-model.number="article.reading_time"
              type="number"
              min="1"
              class="input w-20"
            />
            <span class="text-gray-500">นาที</span>
          </div>
          <p class="text-xs text-gray-400 mt-1">
            คำนวณจากเนื้อหา: ~{{ estimatedReadingTime }} นาที
          </p>
        </div>

        <!-- Options -->
        <div class="card p-6 space-y-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              type="checkbox"
              v-model="article.is_featured"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5"
            />
            <div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">บทความแนะนำ</span>
              <p class="text-xs text-gray-500">แสดงในหน้าแรก</p>
            </div>
          </label>
          <label class="flex items-center gap-3 cursor-pointer">
            <input
              type="checkbox"
              v-model="article.allow_comments"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-5 h-5"
            />
            <div>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">อนุญาตแสดงความคิดเห็น</span>
              <p class="text-xs text-gray-500">ผู้ใช้สามารถแสดงความคิดเห็นได้</p>
            </div>
          </label>
        </div>

        <!-- SEO -->
        <div class="card p-6 space-y-4">
          <h3 class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
            <GlobeAltIcon class="w-5 h-5" />
            SEO
          </h3>
          <div>
            <label class="label">Meta Title</label>
            <input
              v-model="article.meta_title"
              type="text"
              class="input"
              placeholder="หัวข้อสำหรับ SEO"
              maxlength="60"
            />
            <p class="text-xs text-gray-400 mt-1">{{ article.meta_title?.length || 0 }}/60</p>
          </div>
          <div>
            <label class="label">Meta Description</label>
            <textarea
              v-model="article.meta_description"
              class="input resize-none h-20"
              placeholder="คำอธิบายสำหรับ SEO"
              maxlength="160"
            />
            <p class="text-xs text-gray-400 mt-1">{{ article.meta_description?.length || 0 }}/160</p>
          </div>
          <div>
            <label class="label">Canonical URL (ถ้ามี)</label>
            <input
              v-model="article.canonical_url"
              type="url"
              class="input"
              placeholder="https://..."
            />
          </div>
        </div>

        <!-- Related Articles -->
        <div class="card p-6">
          <label class="label">บทความที่เกี่ยวข้อง</label>
          <select @change="addRelatedArticle" class="input">
            <option value="">เลือกบทความ...</option>
            <option
              v-for="art in availableRelatedArticles"
              :key="art.id"
              :value="art.id"
            >
              {{ art.title }}
            </option>
          </select>
          <div v-if="relatedArticles.length > 0" class="mt-3 space-y-2">
            <div
              v-for="related in relatedArticles"
              :key="related.id"
              class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg"
            >
              <span class="text-sm truncate flex-1">{{ related.title }}</span>
              <button @click="removeRelatedArticle(related.id)" class="text-red-500 hover:text-red-700 ml-2">
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Media Upload Modal -->
    <MediaUploadModal
      :is-open="showMediaModal"
      @close="showMediaModal = false"
      @select="insertMedia"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  EyeIcon,
  PencilSquareIcon,
  XMarkIcon,
  PlusIcon,
  TrashIcon,
  GlobeAltIcon,
  CodeBracketSquareIcon,
} from '@heroicons/vue/24/outline'
import MarkdownEditor from '@/components/editor/MarkdownEditor.vue'
import MediaUploadModal from '@/components/editor/MediaUploadModal.vue'
import MarkdownIt from 'markdown-it'
import api from '@/services/api'

interface CodeExample {
  title: string
  language: string
  code: string
}

interface RelatedArticle {
  id: number
  title: string
}

const route = useRoute()
const router = useRouter()

const isEditing = computed(() => !!route.params.id)
const isSaving = ref(false)
const showFullPreview = ref(false)
const showMediaModal = ref(false)
const showTagDropdown = ref(false)
const tagSearch = ref('')
const lastSaved = ref<Date | null>(null)
const editorRef = ref<InstanceType<typeof MarkdownEditor> | null>(null)

const article = reactive({
  title: '',
  slug: '',
  summary: '',
  content: '',
  topic_id: '',
  difficulty: 'beginner',
  article_type: 'guide',
  status: 'draft',
  tags: [] as number[],
  is_featured: false,
  allow_comments: true,
  meta_title: '',
  meta_description: '',
  canonical_url: '',
  reading_time: 5,
  published_at: '',
})

const categories = ref<any[]>([])
const topics = ref<any[]>([])
const tags = ref<any[]>([])
const selectedCategory = ref('')
const codeExamples = ref<CodeExample[]>([])
const relatedArticles = ref<RelatedArticle[]>([])
const allArticles = ref<RelatedArticle[]>([])

const md = new MarkdownIt({
  html: true,
  linkify: true,
  typographer: true,
})

const difficultyLevels = [
  { value: 'beginner', label: 'เริ่มต้น', activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' },
  { value: 'intermediate', label: 'กลาง', activeClass: 'border-amber-500 bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' },
  { value: 'advanced', label: 'สูง', activeClass: 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
]

const renderedContent = computed(() => {
  return md.render(article.content || '')
})

const filteredTopics = computed(() => {
  if (!selectedCategory.value) return []
  return topics.value.filter(t => t.category_id === Number(selectedCategory.value))
})

const availableTags = computed(() => {
  const search = tagSearch.value.toLowerCase()
  return tags.value
    .filter(t => !article.tags.includes(t.id))
    .filter(t => !search || t.name.toLowerCase().includes(search))
    .slice(0, 10)
})

const availableRelatedArticles = computed(() => {
  const currentId = Number(route.params.id)
  const selectedIds = relatedArticles.value.map(r => r.id)
  return allArticles.value.filter(a => a.id !== currentId && !selectedIds.includes(a.id))
})

const estimatedReadingTime = computed(() => {
  const text = article.content || ''
  const thaiChars = text.replace(/[a-zA-Z0-9\s]/g, '').length
  const englishWords = text.match(/[a-zA-Z]+/g)?.length || 0
  const totalWords = Math.ceil(thaiChars / 5) + englishWords
  return Math.max(1, Math.ceil(totalWords / 200))
})

// Auto-save timer
let autoSaveTimer: NodeJS.Timeout | null = null

onMounted(async () => {
  await Promise.all([fetchCategories(), fetchTopics(), fetchTags(), fetchAllArticles()])

  if (isEditing.value) {
    await fetchArticle()
  }

  // Setup auto-save
  autoSaveTimer = setInterval(() => {
    if (article.title && article.content && article.status === 'draft') {
      saveDraft(true)
    }
  }, 60000) // Auto-save every minute

  // Close tag dropdown when clicking outside
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  if (autoSaveTimer) {
    clearInterval(autoSaveTimer)
  }
  document.removeEventListener('click', handleClickOutside)
})

const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.relative')) {
    showTagDropdown.value = false
  }
}

const fetchArticle = async () => {
  try {
    const response = await api.get<any>(`/admin/articles/${route.params.id}`)
    const data = response.data

    Object.assign(article, {
      title: data.title,
      slug: data.slug,
      summary: data.summary || '',
      content: data.content,
      topic_id: data.topic_id,
      difficulty: data.difficulty,
      article_type: data.article_type,
      status: data.status,
      tags: data.tags?.map((t: any) => t.id) || [],
      is_featured: data.is_featured,
      allow_comments: data.allow_comments,
      meta_title: data.meta_title || '',
      meta_description: data.meta_description || '',
      canonical_url: data.canonical_url || '',
      reading_time: data.reading_time || 5,
      published_at: data.published_at ? data.published_at.slice(0, 16) : '',
    })

    if (data.topic?.category_id) {
      selectedCategory.value = String(data.topic.category_id)
    }

    if (data.code_examples) {
      codeExamples.value = data.code_examples.map((e: any) => ({
        title: e.title,
        language: e.language,
        code: e.code,
      }))
    }

    if (data.related_articles) {
      relatedArticles.value = data.related_articles.map((a: any) => ({
        id: a.id,
        title: a.title,
      }))
    }
  } catch (error) {
    console.error('Failed to fetch article:', error)
    router.push('/articles')
  }
}

const fetchCategories = async () => {
  try {
    const response = await api.get<any>('/admin/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  }
}

const fetchTopics = async () => {
  try {
    const response = await api.get<any>('/admin/topics')
    topics.value = response.data
  } catch (error) {
    console.error('Failed to fetch topics:', error)
  }
}

const fetchTags = async () => {
  try {
    const response = await api.get<any>('/admin/tags')
    tags.value = response.data
  } catch (error) {
    console.error('Failed to fetch tags:', error)
  }
}

const fetchAllArticles = async () => {
  try {
    const response = await api.get<any>('/admin/articles', {
      params: { per_page: 100, status: 'published' }
    })
    allArticles.value = response.data.map((a: any) => ({
      id: a.id,
      title: a.title,
    }))
  } catch (error) {
    console.error('Failed to fetch articles:', error)
  }
}

const generateSlug = () => {
  if (isEditing.value) return
  // Simple slug generation (Thai-friendly)
  const slug = article.title
    .toLowerCase()
    .replace(/[^\u0E00-\u0E7Fa-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
  article.slug = slug
}

const onCategoryChange = () => {
  article.topic_id = ''
}

const addTag = (tagId: number) => {
  if (!article.tags.includes(tagId)) {
    article.tags.push(tagId)
  }
  tagSearch.value = ''
  showTagDropdown.value = false
}

const removeTag = (tagId: number) => {
  article.tags = article.tags.filter(id => id !== tagId)
}

const createTag = async () => {
  if (!tagSearch.value.trim()) return

  try {
    const response = await api.post<any>('/admin/tags', {
      name: tagSearch.value.trim()
    })
    tags.value.push(response.data)
    article.tags.push(response.data.id)
    tagSearch.value = ''
    showTagDropdown.value = false
  } catch (error) {
    console.error('Failed to create tag:', error)
  }
}

const getTagName = (tagId: number) => {
  return tags.value.find(t => t.id === tagId)?.name || ''
}

const addCodeExample = () => {
  codeExamples.value.push({
    title: `ตัวอย่างที่ ${codeExamples.value.length + 1}`,
    language: 'javascript',
    code: '',
  })
}

const removeCodeExample = (index: number) => {
  codeExamples.value.splice(index, 1)
}

const addRelatedArticle = (e: Event) => {
  const select = e.target as HTMLSelectElement
  const articleId = Number(select.value)
  if (!articleId) return

  const art = allArticles.value.find(a => a.id === articleId)
  if (art && !relatedArticles.value.find(r => r.id === articleId)) {
    relatedArticles.value.push(art)
  }
  select.value = ''
}

const removeRelatedArticle = (id: number) => {
  relatedArticles.value = relatedArticles.value.filter(r => r.id !== id)
}

const togglePreview = () => {
  showFullPreview.value = !showFullPreview.value
}

const goBack = () => {
  router.push('/articles')
}

const insertMedia = ({ url, alt }: { url: string; alt: string }) => {
  editorRef.value?.insertImage(url, alt)
}

const getDifficultyLabel = (difficulty: string) => {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[difficulty] || difficulty
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    guide: 'คู่มือ',
    tutorial: 'บทเรียน',
    reference: 'อ้างอิง',
    example: 'ตัวอย่าง',
  }
  return labels[type] || type
}

const formatTime = (date: Date) => {
  return date.toLocaleTimeString('th-TH', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

const saveDraft = async (silent = false) => {
  article.status = 'draft'
  await save(silent)
}

const publish = async () => {
  if (!article.topic_id) {
    alert('กรุณาเลือกหัวข้อ')
    return
  }
  if (!article.title.trim()) {
    alert('กรุณาใส่ชื่อบทความ')
    return
  }
  if (!article.content.trim()) {
    alert('กรุณาใส่เนื้อหาบทความ')
    return
  }

  article.status = 'published'
  await save()
}

const save = async (silent = false) => {
  if (isSaving.value) return
  isSaving.value = true

  try {
    const payload = {
      ...article,
      topic_id: Number(article.topic_id),
      reading_time: article.reading_time || estimatedReadingTime.value,
      code_examples: codeExamples.value,
      related_article_ids: relatedArticles.value.map(r => r.id),
    }

    if (isEditing.value) {
      await api.put(`/admin/articles/${route.params.id}`, payload)
    } else {
      const response = await api.post<any>('/admin/articles', payload)
      // Navigate to edit mode after creation
      router.replace(`/articles/${response.data.id}/edit`)
    }

    lastSaved.value = new Date()

    if (!silent && article.status !== 'draft') {
      router.push('/articles')
    }
  } catch (error: any) {
    console.error('Failed to save article:', error)
    if (!silent) {
      alert(error.response?.data?.error?.message || 'เกิดข้อผิดพลาดในการบันทึก')
    }
  } finally {
    isSaving.value = false
  }
}

// Update reading time when content changes
watch(() => article.content, () => {
  if (!article.reading_time || article.reading_time === estimatedReadingTime.value - 1 || article.reading_time === estimatedReadingTime.value + 1) {
    article.reading_time = estimatedReadingTime.value
  }
})
</script>
