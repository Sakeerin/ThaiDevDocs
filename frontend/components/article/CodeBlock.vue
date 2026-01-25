<template>
  <div class="relative group rounded-lg overflow-hidden bg-gray-900">
    <!-- Language Badge -->
    <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
      <span class="text-xs font-medium text-gray-400 uppercase">
        {{ language }}
      </span>
      <div class="flex items-center gap-2">
        <button
          v-if="isRunnable"
          @click="runCode"
          class="flex items-center gap-1 px-2 py-1 text-xs text-green-400 hover:bg-green-900/20 rounded transition-colors"
        >
          <PlayIcon class="w-4 h-4" />
          Run
        </button>
        <button
          @click="copyCode"
          class="flex items-center gap-1 px-2 py-1 text-xs text-gray-400 hover:bg-gray-700 rounded transition-colors"
        >
          <ClipboardIcon v-if="!copied" class="w-4 h-4" />
          <CheckIcon v-else class="w-4 h-4 text-green-400" />
          {{ copied ? 'Copied!' : 'Copy' }}
        </button>
      </div>
    </div>

    <!-- Code Content -->
    <pre class="overflow-x-auto scrollbar-custom"><code ref="codeRef" :class="`language-${language}`">{{ code }}</code></pre>

    <!-- Output (if any) -->
    <div v-if="output" class="border-t border-gray-700">
      <div class="px-4 py-2 bg-gray-800 text-xs text-gray-400 font-medium">
        Output
      </div>
      <div
        class="p-4 bg-gray-950 font-mono text-sm"
        :class="[
          outputType === 'html' ? 'bg-white text-gray-900' : 'text-gray-200'
        ]"
      >
        <div v-if="outputType === 'html'" v-html="output" />
        <pre v-else>{{ output }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PlayIcon, ClipboardIcon, CheckIcon } from '@heroicons/vue/24/outline'
import hljs from 'highlight.js'

const props = defineProps<{
  code: string
  language: string
  output?: string
  outputType?: 'text' | 'html' | 'console'
  isRunnable?: boolean
  isEditable?: boolean
}>()

const emit = defineEmits<{
  run: []
}>()

const codeRef = ref<HTMLElement | null>(null)
const copied = ref(false)

// Highlight code on mount
onMounted(() => {
  if (codeRef.value) {
    hljs.highlightElement(codeRef.value)
  }
})

const copyCode = async () => {
  try {
    await navigator.clipboard.writeText(props.code)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (err) {
    console.error('Failed to copy:', err)
  }
}

const runCode = () => {
  emit('run')
}
</script>
