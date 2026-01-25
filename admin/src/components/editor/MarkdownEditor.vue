<template>
  <div class="markdown-editor rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
    <!-- Toolbar -->
    <div class="bg-gray-50 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-600 px-3 py-2">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1 flex-wrap">
          <!-- Text Formatting -->
          <div class="flex items-center gap-0.5 mr-2">
            <button @click="insertFormat('**', '**')" class="toolbar-btn" title="ตัวหนา (Ctrl+B)">
              <BoldIcon class="w-4 h-4" />
            </button>
            <button @click="insertFormat('*', '*')" class="toolbar-btn" title="ตัวเอียง (Ctrl+I)">
              <ItalicIcon class="w-4 h-4" />
            </button>
            <button @click="insertFormat('~~', '~~')" class="toolbar-btn" title="ขีดฆ่า">
              <StrikethroughIcon class="w-4 h-4" />
            </button>
          </div>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

          <!-- Headings -->
          <div class="flex items-center gap-0.5 mr-2">
            <button @click="insertFormat('# ', '')" class="toolbar-btn font-bold text-xs" title="หัวข้อ 1">
              H1
            </button>
            <button @click="insertFormat('## ', '')" class="toolbar-btn font-bold text-xs" title="หัวข้อ 2">
              H2
            </button>
            <button @click="insertFormat('### ', '')" class="toolbar-btn font-bold text-xs" title="หัวข้อ 3">
              H3
            </button>
          </div>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

          <!-- Code -->
          <div class="flex items-center gap-0.5 mr-2">
            <button @click="insertFormat('`', '`')" class="toolbar-btn" title="โค้ดอินไลน์">
              <CodeBracketIcon class="w-4 h-4" />
            </button>
            <button @click="insertCodeBlock" class="toolbar-btn" title="บล็อกโค้ด">
              <CommandLineIcon class="w-4 h-4" />
            </button>
          </div>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

          <!-- Lists & Blocks -->
          <div class="flex items-center gap-0.5 mr-2">
            <button @click="insertFormat('- ', '')" class="toolbar-btn" title="รายการ">
              <ListBulletIcon class="w-4 h-4" />
            </button>
            <button @click="insertFormat('1. ', '')" class="toolbar-btn" title="รายการลำดับ">
              <NumberedListIcon class="w-4 h-4" />
            </button>
            <button @click="insertFormat('> ', '')" class="toolbar-btn" title="อ้างอิง">
              <ChatBubbleBottomCenterTextIcon class="w-4 h-4" />
            </button>
          </div>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

          <!-- Links & Media -->
          <div class="flex items-center gap-0.5 mr-2">
            <button @click="insertLink" class="toolbar-btn" title="ลิงก์">
              <LinkIcon class="w-4 h-4" />
            </button>
            <button @click="$emit('open-media')" class="toolbar-btn" title="รูปภาพ">
              <PhotoIcon class="w-4 h-4" />
            </button>
            <button @click="insertTable" class="toolbar-btn" title="ตาราง">
              <TableCellsIcon class="w-4 h-4" />
            </button>
          </div>

          <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1" />

          <!-- Callouts -->
          <div class="flex items-center gap-0.5">
            <button @click="insertCallout('tip')" class="toolbar-btn text-emerald-600" title="เคล็ดลับ">
              <LightBulbIcon class="w-4 h-4" />
            </button>
            <button @click="insertCallout('warning')" class="toolbar-btn text-amber-600" title="คำเตือน">
              <ExclamationTriangleIcon class="w-4 h-4" />
            </button>
            <button @click="insertCallout('info')" class="toolbar-btn text-blue-600" title="ข้อมูล">
              <InformationCircleIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-2 ml-4">
          <button
            @click="viewMode = 'edit'"
            :class="['px-2 py-1 text-xs rounded', viewMode === 'edit' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700']"
          >
            แก้ไข
          </button>
          <button
            @click="viewMode = 'split'"
            :class="['px-2 py-1 text-xs rounded', viewMode === 'split' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700']"
          >
            แบ่งหน้า
          </button>
          <button
            @click="viewMode = 'preview'"
            :class="['px-2 py-1 text-xs rounded', viewMode === 'preview' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700']"
          >
            ดูตัวอย่าง
          </button>
        </div>
      </div>
    </div>

    <!-- Editor & Preview -->
    <div class="flex h-[500px]">
      <!-- Editor Pane -->
      <div
        v-show="viewMode !== 'preview'"
        :class="['flex-1 relative', viewMode === 'split' ? 'border-r border-gray-300 dark:border-gray-600' : '']"
      >
        <textarea
          ref="editorRef"
          :value="modelValue"
          @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
          @keydown="handleKeydown"
          class="w-full h-full p-4 font-mono text-sm bg-white dark:bg-gray-900 border-0 focus:ring-0 resize-none"
          :placeholder="placeholder"
        />
        <!-- Line Numbers (optional enhancement) -->
        <div class="absolute top-0 left-0 w-10 h-full bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 pointer-events-none">
          <div class="p-4 text-right text-xs text-gray-400 font-mono leading-5 select-none">
            <div v-for="n in lineCount" :key="n">{{ n }}</div>
          </div>
        </div>
        <textarea
          ref="editorRef"
          :value="modelValue"
          @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
          @keydown="handleKeydown"
          @scroll="syncScroll"
          class="absolute inset-0 w-full h-full pl-12 pr-4 py-4 font-mono text-sm bg-transparent border-0 focus:ring-0 resize-none leading-5"
          :placeholder="placeholder"
        />
      </div>

      <!-- Preview Pane -->
      <div
        v-show="viewMode !== 'edit'"
        :class="['flex-1 overflow-auto p-6 bg-white dark:bg-gray-900', viewMode === 'preview' ? 'w-full' : '']"
        ref="previewRef"
      >
        <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:font-noto" v-html="renderedContent" />
      </div>
    </div>

    <!-- Status Bar -->
    <div class="bg-gray-50 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-600 px-4 py-1.5 flex items-center justify-between text-xs text-gray-500">
      <div class="flex items-center gap-4">
        <span>{{ charCount }} ตัวอักษร</span>
        <span>{{ wordCount }} คำ</span>
        <span>{{ lineCount }} บรรทัด</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-gray-400">Markdown</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import {
  CodeBracketIcon,
  CommandLineIcon,
  LinkIcon,
  PhotoIcon,
  TableCellsIcon,
  ListBulletIcon,
  ChatBubbleBottomCenterTextIcon,
  LightBulbIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'
import MarkdownIt from 'markdown-it'

// Custom icons
const BoldIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M8 11h4.5a2.5 2.5 0 1 0 0-5H8v5Zm10 4.5a4.5 4.5 0 0 1-4.5 4.5H6V4h6.5a4.5 4.5 0 0 1 3.256 7.606A4.5 4.5 0 0 1 18 15.5ZM8 13v5h5.5a2.5 2.5 0 1 0 0-5H8Z"/></svg>`
}

const ItalicIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M15 4v2h-2.21l-3.42 12H12v2H5v-2h2.21l3.42-12H8V4h7Z"/></svg>`
}

const StrikethroughIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.154 14c.23.516.346 1.09.346 1.72 0 1.342-.524 2.392-1.571 3.147C14.88 19.622 13.433 20 11.586 20c-1.64 0-3.263-.381-4.87-1.144v-2.66c1.55.966 3.097 1.449 4.642 1.449.9 0 1.596-.188 2.088-.563.491-.376.737-.872.737-1.49 0-.27-.047-.505-.142-.702H3v-2h18v2h-3.846ZM7.556 11c-.278-.328-.467-.702-.568-1.121A4.703 4.703 0 0 1 6.87 8.72c0-1.28.488-2.312 1.466-3.096C9.313 4.875 10.586 4.483 12.155 4.483c1.64 0 3.123.334 4.449 1.001v2.49c-1.325-.699-2.674-1.048-4.049-1.048-.838 0-1.492.174-1.963.523-.47.35-.706.827-.706 1.432 0 .372.088.693.265.963.177.27.417.485.72.645L7.556 11Z"/></svg>`
}

const NumberedListIcon = {
  template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M8 4h13v2H8V4ZM5 3v3h1v1H3V6h1V4H3V3h2ZM3 14v-2.5h2V11H3v-1h3v2.5H4v.5h2v1H3Zm2 5.5H3v-1h2V18H3v-1h3v4H3v-1h2v-.5ZM8 11h13v2H8v-2Zm0 7h13v2H8v-2Z"/></svg>`
}

const props = withDefaults(defineProps<{
  modelValue: string
  placeholder?: string
}>(), {
  placeholder: 'เขียนเนื้อหาที่นี่...'
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'open-media': []
}>()

const editorRef = ref<HTMLTextAreaElement | null>(null)
const previewRef = ref<HTMLDivElement | null>(null)
const viewMode = ref<'edit' | 'split' | 'preview'>('split')

// Markdown renderer
const md = new MarkdownIt({
  html: true,
  linkify: true,
  typographer: true,
  breaks: true,
})

// Add custom containers for callouts
const defaultRender = md.renderer.rules.fence || ((tokens, idx, options, env, self) => self.renderToken(tokens, idx, options))
md.renderer.rules.fence = (tokens, idx, options, env, self) => {
  const token = tokens[idx]
  const info = token.info.trim()
  
  // Handle callouts
  if (['tip', 'warning', 'info', 'danger'].includes(info)) {
    const iconMap: Record<string, string> = {
      tip: '💡',
      warning: '⚠️',
      info: 'ℹ️',
      danger: '🚨',
    }
    const colorMap: Record<string, string> = {
      tip: 'bg-emerald-50 border-emerald-500 dark:bg-emerald-900/20',
      warning: 'bg-amber-50 border-amber-500 dark:bg-amber-900/20',
      info: 'bg-blue-50 border-blue-500 dark:bg-blue-900/20',
      danger: 'bg-red-50 border-red-500 dark:bg-red-900/20',
    }
    return `<div class="callout ${colorMap[info]} border-l-4 p-4 my-4 rounded-r-lg">
      <span class="mr-2">${iconMap[info]}</span>${md.utils.escapeHtml(token.content)}
    </div>`
  }
  
  return defaultRender(tokens, idx, options, env, self)
}

const renderedContent = computed(() => {
  return md.render(props.modelValue || '')
})

const lineCount = computed(() => {
  return (props.modelValue || '').split('\n').length
})

const charCount = computed(() => {
  return (props.modelValue || '').length
})

const wordCount = computed(() => {
  const text = props.modelValue || ''
  // Thai word approximation (characters / 5) + English words
  const thaiChars = text.replace(/[a-zA-Z0-9\s]/g, '').length
  const englishWords = text.match(/[a-zA-Z]+/g)?.length || 0
  return Math.ceil(thaiChars / 5) + englishWords
})

const insertFormat = (before: string, after: string) => {
  const textarea = editorRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end)

  const newText = text.substring(0, start) + before + selected + after + text.substring(end)
  emit('update:modelValue', newText)

  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + before.length, start + before.length + selected.length)
  })
}

const insertCodeBlock = () => {
  const textarea = editorRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end)

  const newText = text.substring(0, start) + '```javascript\n' + selected + '\n```' + text.substring(end)
  emit('update:modelValue', newText)

  nextTick(() => {
    textarea.focus()
    textarea.setSelectionRange(start + 14, start + 14 + selected.length)
  })
}

const insertLink = () => {
  const url = prompt('ใส่ URL:')
  if (!url) return
  
  const textarea = editorRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end) || 'ข้อความลิงก์'

  const newText = text.substring(0, start) + `[${selected}](${url})` + text.substring(end)
  emit('update:modelValue', newText)
}

const insertTable = () => {
  const textarea = editorRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const text = props.modelValue

  const table = `| หัวข้อ 1 | หัวข้อ 2 | หัวข้อ 3 |
| --- | --- | --- |
| ข้อมูล | ข้อมูล | ข้อมูล |
| ข้อมูล | ข้อมูล | ข้อมูล |
`

  const newText = text.substring(0, start) + table + text.substring(start)
  emit('update:modelValue', newText)
}

const insertCallout = (type: 'tip' | 'warning' | 'info' | 'danger') => {
  const textarea = editorRef.value
  if (!textarea) return

  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end) || 'ข้อความ'

  const newText = text.substring(0, start) + '```' + type + '\n' + selected + '\n```\n' + text.substring(end)
  emit('update:modelValue', newText)
}

const handleKeydown = (e: KeyboardEvent) => {
  // Keyboard shortcuts
  if (e.ctrlKey || e.metaKey) {
    switch (e.key.toLowerCase()) {
      case 'b':
        e.preventDefault()
        insertFormat('**', '**')
        break
      case 'i':
        e.preventDefault()
        insertFormat('*', '*')
        break
      case 'k':
        e.preventDefault()
        insertLink()
        break
    }
  }

  // Tab handling for code blocks
  if (e.key === 'Tab') {
    e.preventDefault()
    const textarea = editorRef.value
    if (!textarea) return

    const start = textarea.selectionStart
    const text = props.modelValue
    const newText = text.substring(0, start) + '  ' + text.substring(start)
    emit('update:modelValue', newText)

    nextTick(() => {
      textarea.setSelectionRange(start + 2, start + 2)
    })
  }
}

const syncScroll = () => {
  if (viewMode.value === 'split' && previewRef.value && editorRef.value) {
    const scrollPercent = editorRef.value.scrollTop / (editorRef.value.scrollHeight - editorRef.value.clientHeight)
    previewRef.value.scrollTop = scrollPercent * (previewRef.value.scrollHeight - previewRef.value.clientHeight)
  }
}

// Expose editor ref for parent components
defineExpose({
  insertImage: (url: string, alt: string = 'image') => {
    const textarea = editorRef.value
    if (!textarea) return

    const start = textarea.selectionStart
    const text = props.modelValue
    const newText = text.substring(0, start) + `![${alt}](${url})` + text.substring(start)
    emit('update:modelValue', newText)
  }
})
</script>

<style scoped>
.toolbar-btn {
  @apply p-1.5 rounded text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors;
}

.prose :deep(pre) {
  @apply bg-gray-900 text-gray-100 rounded-lg p-4 overflow-x-auto;
}

.prose :deep(code) {
  @apply bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-sm font-mono;
}

.prose :deep(pre code) {
  @apply bg-transparent p-0;
}

.prose :deep(.callout) {
  @apply text-gray-800 dark:text-gray-200;
}

.prose :deep(table) {
  @apply w-full border-collapse;
}

.prose :deep(th),
.prose :deep(td) {
  @apply border border-gray-300 dark:border-gray-600 px-4 py-2;
}

.prose :deep(th) {
  @apply bg-gray-100 dark:bg-gray-800 font-semibold;
}
</style>
