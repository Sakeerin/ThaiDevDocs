<template>
  <article class="prose prose-lg dark:prose-dark max-w-none">
    <div v-html="processedContent" />
  </article>
</template>

<script setup lang="ts">
import hljs from 'highlight.js'

const props = defineProps<{
  content: string
}>()

const processedContent = computed(() => {
  if (!props.content) return ''

  // Process the HTML content to add syntax highlighting
  const div = document.createElement('div')
  div.innerHTML = props.content

  // Highlight code blocks
  div.querySelectorAll('pre code').forEach((block) => {
    hljs.highlightElement(block as HTMLElement)
  })

  // Add IDs to headings for TOC
  div.querySelectorAll('h2, h3, h4').forEach((heading) => {
    if (!heading.id) {
      const text = heading.textContent || ''
      heading.id = text
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w\u0E00-\u0E7F-]/g, '') // Allow Thai characters
    }
  })

  return div.innerHTML
})
</script>

<style>
/* Override prose styles for Thai content */
.prose {
  line-height: 1.8;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4 {
  scroll-margin-top: 5rem;
}

.prose h2 {
  @apply border-b border-gray-200 dark:border-gray-700 pb-2;
}

.prose pre {
  @apply relative;
}

.prose pre code {
  @apply block overflow-x-auto p-4;
}

/* Custom block styles */
.prose .tip,
.prose .warning,
.prose .danger,
.prose .info {
  @apply p-4 rounded-lg my-4;
}

.prose .tip {
  @apply bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500;
}

.prose .warning {
  @apply bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500;
}

.prose .danger {
  @apply bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500;
}

.prose .info {
  @apply bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500;
}
</style>
