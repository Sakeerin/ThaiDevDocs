<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">บุ๊กมาร์กของฉัน</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">บทความที่คุณบันทึกไว้อ่านภายหลัง</p>
      </div>
      <div class="flex items-center gap-4">
        <select v-model="sortBy" class="px-4 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-700 dark:text-gray-300">
          <option value="recent">เพิ่มล่าสุด</option>
          <option value="oldest">เก่าสุด</option>
          <option value="title">ชื่อบทความ</option>
        </select>
      </div>
    </div>

    <!-- Collections -->
    <div class="flex gap-2 overflow-x-auto pb-4 mb-6">
      <button
        @click="selectedCollection = null"
        :class="[
          'px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors',
          !selectedCollection
            ? 'bg-primary-600 text-white'
            : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700'
        ]"
      >
        ทั้งหมด ({{ totalBookmarks }})
      </button>
      <button
        v-for="collection in collections"
        :key="collection.id"
        @click="selectedCollection = collection.id"
        :class="[
          'px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-colors',
          selectedCollection === collection.id
            ? 'bg-primary-600 text-white'
            : 'bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-slate-700'
        ]"
      >
        {{ collection.name }} ({{ collection.bookmarks_count }})
      </button>
      <button
        @click="showNewCollectionModal = true"
        class="px-4 py-2 rounded-xl text-sm font-medium whitespace-nowrap border-2 border-dashed border-gray-300 dark:border-slate-600 text-gray-500 hover:border-primary-500 hover:text-primary-500 transition-colors"
      >
        <Icon name="heroicons:plus" class="w-4 h-4 inline-block mr-1" />
        สร้างคอลเลกชัน
      </button>
    </div>

    <!-- Bookmarks Grid -->
    <div v-if="!isLoading && bookmarks.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="bookmark in bookmarks"
        :key="bookmark.id"
        class="group bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 overflow-hidden hover:shadow-lg transition-all"
      >
        <div class="p-6">
          <!-- Category Badge -->
          <div class="flex items-center gap-2 mb-3">
            <span class="px-2 py-1 text-xs font-medium rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400">
              {{ bookmark.article?.topic?.category?.name }}
            </span>
            <span v-if="bookmark.article?.difficulty" :class="getDifficultyClass(bookmark.article.difficulty)">
              {{ getDifficultyLabel(bookmark.article.difficulty) }}
            </span>
          </div>

          <!-- Title -->
          <NuxtLink :to="`/docs/${bookmark.article?.slug}`" class="block group-hover:text-primary-600 transition-colors">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2">
              {{ bookmark.article?.title }}
            </h3>
          </NuxtLink>

          <!-- Summary -->
          <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm line-clamp-2">
            {{ bookmark.article?.summary }}
          </p>

          <!-- Meta -->
          <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
            <div class="flex items-center gap-3">
              <span class="flex items-center gap-1">
                <Icon name="heroicons:clock" class="w-4 h-4" />
                {{ bookmark.article?.reading_time }} นาที
              </span>
            </div>
            <span>บันทึกเมื่อ {{ formatDate(bookmark.created_at) }}</span>
          </div>

          <!-- Actions -->
          <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-100 dark:border-slate-700">
            <button
              @click="moveToCollection(bookmark)"
              class="text-gray-500 hover:text-primary-600 text-sm flex items-center gap-1"
            >
              <Icon name="heroicons:folder" class="w-4 h-4" />
              ย้ายไปคอลเลกชัน
            </button>
            <button
              @click="removeBookmark(bookmark.id)"
              class="text-gray-400 hover:text-red-500 transition-colors"
            >
              <Icon name="heroicons:trash" class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!isLoading && bookmarks.length === 0" class="text-center py-16">
      <Icon name="heroicons:bookmark" class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" />
      <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">ยังไม่มีบุ๊กมาร์ก</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">เริ่มบันทึกบทความที่น่าสนใจเพื่ออ่านภายหลัง</p>
      <NuxtLink to="/docs" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition-colors">
        สำรวจบทความ
        <Icon name="heroicons:arrow-right" class="w-5 h-5" />
      </NuxtLink>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-6 animate-pulse">
        <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-1/3 mb-4" />
        <div class="h-6 bg-gray-200 dark:bg-slate-700 rounded w-full mb-2" />
        <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded w-2/3" />
      </div>
    </div>

    <!-- New Collection Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showNewCollectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
          <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">สร้างคอลเลกชันใหม่</h3>
            <input
              v-model="newCollectionName"
              type="text"
              placeholder="ชื่อคอลเลกชัน"
              class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:text-white mb-4"
            />
            <div class="flex justify-end gap-3">
              <button @click="showNewCollectionModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-xl">
                ยกเลิก
              </button>
              <button @click="createCollection" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl">
                สร้าง
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { $api } = useNuxtApp()

const bookmarks = ref<any[]>([])
const collections = ref<any[]>([])
const isLoading = ref(true)
const selectedCollection = ref<number | null>(null)
const sortBy = ref('recent')
const showNewCollectionModal = ref(false)
const newCollectionName = ref('')

const totalBookmarks = computed(() => {
  return collections.value.reduce((sum, c) => sum + c.bookmarks_count, 0) || bookmarks.value.length
})

onMounted(async () => {
  await Promise.all([fetchBookmarks(), fetchCollections()])
})

watch([selectedCollection, sortBy], () => {
  fetchBookmarks()
})

async function fetchBookmarks() {
  isLoading.value = true
  try {
    const { data } = await $api('/bookmarks', {
      params: {
        collection_id: selectedCollection.value,
        sort: sortBy.value,
      }
    })
    bookmarks.value = data
  } catch (e) {
    console.error('Failed to fetch bookmarks:', e)
  } finally {
    isLoading.value = false
  }
}

async function fetchCollections() {
  try {
    const { data } = await $api('/collections')
    collections.value = data
  } catch (e) {
    console.error('Failed to fetch collections:', e)
  }
}

async function removeBookmark(id: number) {
  if (!confirm('ต้องการลบบุ๊กมาร์กนี้หรือไม่?')) return
  
  try {
    await $api(`/bookmarks/${id}`, { method: 'DELETE' })
    bookmarks.value = bookmarks.value.filter(b => b.id !== id)
  } catch (e) {
    console.error('Failed to remove bookmark:', e)
  }
}

function moveToCollection(bookmark: any) {
  // Would open a modal to select collection
  console.log('Move to collection:', bookmark)
}

async function createCollection() {
  if (!newCollectionName.value.trim()) return
  
  try {
    await $api('/collections', {
      method: 'POST',
      body: { name: newCollectionName.value }
    })
    await fetchCollections()
    showNewCollectionModal.value = false
    newCollectionName.value = ''
  } catch (e) {
    console.error('Failed to create collection:', e)
  }
}

function getDifficultyLabel(difficulty: string) {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[difficulty] || difficulty
}

function getDifficultyClass(difficulty: string) {
  const classes: Record<string, string> = {
    beginner: 'px-2 py-1 text-xs font-medium rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    intermediate: 'px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    advanced: 'px-2 py-1 text-xs font-medium rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
  }
  return classes[difficulty] || ''
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
  transform: scale(0.95);
}
</style>
