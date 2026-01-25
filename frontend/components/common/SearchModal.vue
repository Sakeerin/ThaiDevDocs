<template>
  <TransitionRoot :show="modelValue" as="template">
    <Dialog as="div" class="relative z-50" @close="emit('update:modelValue', false)">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto p-4 sm:p-6 md:p-20">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="mx-auto max-w-2xl transform rounded-xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-gray-900/5 dark:ring-gray-800 transition-all">
            <!-- Search Input -->
            <div class="relative">
              <MagnifyingGlassIcon class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
              <input
                ref="inputRef"
                v-model="query"
                type="text"
                class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                placeholder="ค้นหาเอกสาร, บทความ, คำศัพท์..."
                @input="handleSearch"
              />
            </div>

            <!-- Results -->
            <div v-if="query" class="border-t border-gray-100 dark:border-gray-800">
              <!-- Loading -->
              <div v-if="isLoading" class="p-6 text-center text-gray-500">
                <div class="inline-block w-6 h-6 border-2 border-primary-600 border-t-transparent rounded-full animate-spin" />
                <p class="mt-2">กำลังค้นหา...</p>
              </div>

              <!-- Results List -->
              <ul v-else-if="results.length > 0" class="max-h-96 overflow-y-auto py-2">
                <li
                  v-for="(result, index) in results"
                  :key="result.id"
                  @click="goToResult(result)"
                >
                  <a
                    class="flex items-start gap-4 px-4 py-3 cursor-pointer"
                    :class="[
                      index === selectedIndex 
                        ? 'bg-primary-50 dark:bg-primary-900/20' 
                        : 'hover:bg-gray-50 dark:hover:bg-gray-800'
                    ]"
                  >
                    <DocumentTextIcon class="w-6 h-6 text-gray-400 flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white" v-html="result._formatted?.title || result.title" />
                      <p class="mt-1 text-sm text-gray-500 line-clamp-2" v-html="result._formatted?.summary || result.summary" />
                      <div class="mt-2 flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ result.category_name }}</span>
                        <span v-if="result.difficulty" :class="['text-xs', difficultyClass(result.difficulty)]">
                          {{ difficultyLabel(result.difficulty) }}
                        </span>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>

              <!-- No Results -->
              <div v-else class="p-6 text-center text-gray-500">
                <DocumentMagnifyingGlassIcon class="mx-auto h-12 w-12 text-gray-400" />
                <p class="mt-2">ไม่พบผลลัพธ์สำหรับ "{{ query }}"</p>
                <p class="mt-1 text-sm">ลองใช้คำค้นหาอื่น</p>
              </div>
            </div>

            <!-- Quick Links -->
            <div v-else class="border-t border-gray-100 dark:border-gray-800 py-4 px-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">หมวดหมู่ยอดนิยม</p>
              <div class="flex flex-wrap gap-2">
                <NuxtLink
                  v-for="category in popularCategories"
                  :key="category.slug"
                  :to="`/docs/${category.slug}`"
                  class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700"
                  @click="emit('update:modelValue', false)"
                >
                  {{ category.name }}
                </NuxtLink>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex flex-wrap items-center bg-gray-50 dark:bg-gray-800/50 py-2.5 px-4 text-xs text-gray-500 border-t border-gray-100 dark:border-gray-800 rounded-b-xl">
              <span class="flex items-center gap-1">
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">↵</kbd>
                เลือก
              </span>
              <span class="flex items-center gap-1 ml-4">
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">↑↓</kbd>
                เลื่อน
              </span>
              <span class="flex items-center gap-1 ml-4">
                <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">Esc</kbd>
                ปิด
              </span>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { MagnifyingGlassIcon, DocumentTextIcon, DocumentMagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const router = useRouter()
const inputRef = ref<HTMLInputElement | null>(null)
const query = ref('')
const results = ref<any[]>([])
const isLoading = ref(false)
const selectedIndex = ref(0)

const popularCategories = [
  { name: 'HTML', slug: 'html' },
  { name: 'CSS', slug: 'css' },
  { name: 'JavaScript', slug: 'javascript' },
  { name: 'Vue.js', slug: 'vue' },
  { name: 'React', slug: 'react' },
]

const difficultyLabel = (difficulty: string) => {
  const labels: Record<string, string> = {
    beginner: 'เริ่มต้น',
    intermediate: 'กลาง',
    advanced: 'สูง',
  }
  return labels[difficulty] || difficulty
}

const difficultyClass = (difficulty: string) => {
  const classes: Record<string, string> = {
    beginner: 'text-green-600',
    intermediate: 'text-yellow-600',
    advanced: 'text-red-600',
  }
  return classes[difficulty] || ''
}

const handleSearch = useDebounceFn(async () => {
  if (!query.value.trim()) {
    results.value = []
    return
  }

  isLoading.value = true
  try {
    const config = useRuntimeConfig()
    const response = await $fetch<any>(`${config.public.apiBase}/search`, {
      params: { q: query.value },
    })
    results.value = response.data?.hits || []
    selectedIndex.value = 0
  } catch (error) {
    console.error('Search error:', error)
    results.value = []
  } finally {
    isLoading.value = false
  }
}, 300)

const goToResult = (result: any) => {
  emit('update:modelValue', false)
  router.push(`/docs/${result.slug}`)
}

// Focus input when modal opens
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    nextTick(() => {
      inputRef.value?.focus()
    })
  } else {
    query.value = ''
    results.value = []
  }
})

// Keyboard navigation
onMounted(() => {
  const handleKeydown = (e: KeyboardEvent) => {
    if (!props.modelValue) return

    if (e.key === 'ArrowDown') {
      e.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, results.value.length - 1)
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
    } else if (e.key === 'Enter' && results.value.length > 0) {
      e.preventDefault()
      goToResult(results.value[selectedIndex.value])
    }
  }

  window.addEventListener('keydown', handleKeydown)
  onUnmounted(() => window.removeEventListener('keydown', handleKeydown))
})
</script>
