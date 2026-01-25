<template>
  <div class="code-playground rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden bg-white dark:bg-slate-900">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
      <div class="flex items-center gap-3">
        <!-- Language Indicator -->
        <div class="flex items-center gap-2">
          <div :class="['w-3 h-3 rounded-full', languageColor]" />
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ languageLabel }}</span>
        </div>
        <span v-if="title" class="text-sm text-gray-500 dark:text-gray-400">
          — {{ title }}
        </span>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="resetCode"
          class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded hover:bg-gray-100 dark:hover:bg-slate-700"
          title="รีเซ็ตโค้ด"
        >
          <Icon name="heroicons:arrow-path" class="w-4 h-4" />
        </button>
        <button
          @click="copyCode"
          class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded hover:bg-gray-100 dark:hover:bg-slate-700"
          title="คัดลอกโค้ด"
        >
          <Icon :name="copied ? 'heroicons:check' : 'heroicons:clipboard'" class="w-4 h-4" />
        </button>
        <button
          v-if="canRun"
          @click="runCode"
          :disabled="isRunning"
          class="flex items-center gap-1.5 px-3 py-1 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg transition-colors disabled:opacity-50"
        >
          <Icon v-if="isRunning" name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
          <Icon v-else name="heroicons:play" class="w-4 h-4" />
          รัน
        </button>
      </div>
    </div>

    <!-- Editor Tabs (for HTML/CSS/JS combo) -->
    <div v-if="isHTMLPlayground" class="flex border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
      <button
        v-for="tab in editorTabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="[
          'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors',
          activeTab === tab.id
            ? 'border-primary-500 text-primary-600 dark:text-primary-400'
            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
        ]"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Code Editor -->
    <div class="relative">
      <div class="flex">
        <!-- Line Numbers -->
        <div class="flex-shrink-0 py-4 pr-2 text-right font-mono text-sm text-gray-400 dark:text-gray-600 select-none bg-gray-50 dark:bg-slate-800/50 border-r border-gray-200 dark:border-slate-700">
          <div v-for="n in lineCount" :key="n" class="px-3 leading-6">{{ n }}</div>
        </div>

        <!-- Editor -->
        <textarea
          ref="editorRef"
          v-model="editableCode"
          @keydown="handleKeydown"
          spellcheck="false"
          class="flex-1 p-4 font-mono text-sm bg-transparent text-gray-900 dark:text-gray-100 focus:outline-none resize-none overflow-x-auto leading-6"
          :style="{ minHeight: '120px', height: `${Math.max(120, lineCount * 24 + 32)}px` }"
        />
      </div>

      <!-- Syntax Highlight Overlay (optional enhancement) -->
      <div
        v-if="false"
        class="absolute inset-0 pointer-events-none p-4 font-mono text-sm leading-6 overflow-hidden"
        aria-hidden="true"
      >
        <pre class="whitespace-pre-wrap"><code v-html="highlightedCode" /></pre>
      </div>
    </div>

    <!-- Output Section -->
    <div v-if="canRun" class="border-t border-gray-200 dark:border-slate-700">
      <!-- Output Tabs -->
      <div class="flex items-center px-4 py-2 bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">ผลลัพธ์</span>
        <div v-if="isHTMLPlayground" class="flex ml-4 gap-2">
          <button
            @click="outputView = 'preview'"
            :class="[
              'px-2 py-1 text-xs rounded',
              outputView === 'preview'
                ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600'
                : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700'
            ]"
          >
            Preview
          </button>
          <button
            @click="outputView = 'console'"
            :class="[
              'px-2 py-1 text-xs rounded',
              outputView === 'console'
                ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600'
                : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700'
            ]"
          >
            Console
          </button>
        </div>
        <button
          v-if="output || consoleOutput.length > 0"
          @click="clearOutput"
          class="ml-auto text-xs text-gray-400 hover:text-gray-600"
        >
          ล้าง
        </button>
      </div>

      <!-- Output Content -->
      <div class="relative min-h-[120px] max-h-[300px] overflow-auto">
        <!-- Preview (for HTML) -->
        <div v-if="isHTMLPlayground && outputView === 'preview'" class="p-4 bg-white dark:bg-slate-900">
          <iframe
            ref="previewFrame"
            :srcdoc="previewContent"
            class="w-full min-h-[100px] border-0"
            sandbox="allow-scripts"
          />
        </div>

        <!-- Console Output -->
        <div v-else class="p-4 font-mono text-sm">
          <div v-if="output" class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
            {{ output }}
          </div>
          <div v-if="consoleOutput.length > 0" class="space-y-1">
            <div
              v-for="(log, i) in consoleOutput"
              :key="i"
              :class="[
                'py-1 px-2 rounded',
                log.type === 'error' ? 'bg-red-50 dark:bg-red-900/20 text-red-600' :
                log.type === 'warn' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-600' :
                'bg-gray-50 dark:bg-slate-800 text-gray-700 dark:text-gray-300'
              ]"
            >
              <span v-if="log.type !== 'log'" class="text-xs opacity-60 mr-2">[{{ log.type }}]</span>
              {{ log.message }}
            </div>
          </div>
          <div v-if="error" class="text-red-500">
            <Icon name="heroicons:exclamation-triangle" class="w-4 h-4 inline-block mr-1" />
            {{ error }}
          </div>
          <div v-if="!output && !error && consoleOutput.length === 0" class="text-gray-400 dark:text-gray-600 text-center py-8">
            <Icon name="heroicons:play" class="w-8 h-8 mx-auto mb-2" />
            <p>กดปุ่ม "รัน" เพื่อดูผลลัพธ์</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
  code: string
  language: string
  title?: string
  readonly?: boolean
  html?: string
  css?: string
}>()

const emit = defineEmits<{
  'update:code': [code: string]
}>()

const originalCode = ref(props.code)
const editableCode = ref(props.code)
const htmlCode = ref(props.html || '')
const cssCode = ref(props.css || '')
const editorRef = ref<HTMLTextAreaElement | null>(null)
const previewFrame = ref<HTMLIFrameElement | null>(null)

const output = ref('')
const error = ref('')
const consoleOutput = ref<Array<{ type: string; message: string }>>([])
const isRunning = ref(false)
const activeTab = ref('js')
const outputView = ref('console')

const { copy, copied } = useClipboard()

const isHTMLPlayground = computed(() => {
  return props.language === 'html' || !!props.html
})

const canRun = computed(() => {
  return ['javascript', 'js', 'typescript', 'ts', 'html'].includes(props.language.toLowerCase())
})

const languageLabel = computed(() => {
  const labels: Record<string, string> = {
    javascript: 'JavaScript',
    js: 'JavaScript',
    typescript: 'TypeScript',
    ts: 'TypeScript',
    html: 'HTML',
    css: 'CSS',
    python: 'Python',
    php: 'PHP',
  }
  return labels[props.language.toLowerCase()] || props.language
})

const languageColor = computed(() => {
  const colors: Record<string, string> = {
    javascript: 'bg-yellow-400',
    js: 'bg-yellow-400',
    typescript: 'bg-blue-400',
    ts: 'bg-blue-400',
    html: 'bg-orange-400',
    css: 'bg-purple-400',
    python: 'bg-green-400',
    php: 'bg-indigo-400',
  }
  return colors[props.language.toLowerCase()] || 'bg-gray-400'
})

const lineCount = computed(() => {
  return editableCode.value.split('\n').length
})

const editorTabs = [
  { id: 'js', label: 'JavaScript' },
  { id: 'html', label: 'HTML' },
  { id: 'css', label: 'CSS' },
]

const previewContent = computed(() => {
  if (!isHTMLPlayground.value) return ''
  
  return `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin: 0; padding: 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    ${cssCode.value}
  </style>
</head>
<body>
  ${htmlCode.value}
  <script>
    // Capture console output
    const originalConsole = { ...console };
    ['log', 'warn', 'error', 'info'].forEach(type => {
      console[type] = (...args) => {
        parent.postMessage({ type: 'console', logType: type, message: args.join(' ') }, '*');
        originalConsole[type](...args);
      };
    });
    
    try {
      ${editableCode.value}
    } catch (e) {
      parent.postMessage({ type: 'error', message: e.message }, '*');
    }
  <\/script>
</body>
</html>
  `
})

watch(() => props.code, (newCode) => {
  originalCode.value = newCode
  editableCode.value = newCode
})

onMounted(() => {
  // Listen for messages from iframe
  window.addEventListener('message', handleIframeMessage)
})

onUnmounted(() => {
  window.removeEventListener('message', handleIframeMessage)
})

function handleIframeMessage(event: MessageEvent) {
  if (event.data.type === 'console') {
    consoleOutput.value.push({
      type: event.data.logType,
      message: event.data.message,
    })
  } else if (event.data.type === 'error') {
    error.value = event.data.message
  }
}

function runCode() {
  output.value = ''
  error.value = ''
  consoleOutput.value = []
  isRunning.value = true

  if (isHTMLPlayground.value) {
    outputView.value = 'preview'
    // Iframe will auto-execute
    setTimeout(() => {
      isRunning.value = false
    }, 100)
    return
  }

  // JavaScript execution
  try {
    // Create a custom console
    const logs: Array<{ type: string; message: string }> = []
    const customConsole = {
      log: (...args: any[]) => logs.push({ type: 'log', message: args.map(String).join(' ') }),
      warn: (...args: any[]) => logs.push({ type: 'warn', message: args.map(String).join(' ') }),
      error: (...args: any[]) => logs.push({ type: 'error', message: args.map(String).join(' ') }),
      info: (...args: any[]) => logs.push({ type: 'info', message: args.map(String).join(' ') }),
    }

    // Execute code in sandboxed context
    const fn = new Function('console', editableCode.value)
    const result = fn(customConsole)

    consoleOutput.value = logs
    
    if (result !== undefined) {
      output.value = String(result)
    }
  } catch (e: any) {
    error.value = e.message
  } finally {
    isRunning.value = false
  }
}

function resetCode() {
  editableCode.value = originalCode.value
  output.value = ''
  error.value = ''
  consoleOutput.value = []
}

function copyCode() {
  copy(editableCode.value)
}

function clearOutput() {
  output.value = ''
  error.value = ''
  consoleOutput.value = []
}

function handleKeydown(e: KeyboardEvent) {
  // Tab handling
  if (e.key === 'Tab') {
    e.preventDefault()
    const textarea = editorRef.value
    if (!textarea) return

    const start = textarea.selectionStart
    const end = textarea.selectionEnd

    editableCode.value = editableCode.value.substring(0, start) + '  ' + editableCode.value.substring(end)

    nextTick(() => {
      textarea.selectionStart = textarea.selectionEnd = start + 2
    })
  }

  // Cmd/Ctrl + Enter to run
  if ((e.metaKey || e.ctrlKey) && e.key === 'Enter' && canRun.value) {
    e.preventDefault()
    runCode()
  }
}
</script>

<style scoped>
.code-playground textarea {
  tab-size: 2;
  -moz-tab-size: 2;
}
</style>
