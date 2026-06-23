<template>
  <section v-if="hasData" class="mt-12">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
      <GlobeAltIcon class="w-7 h-7" />
      ความเข้ากันได้ของเบราว์เซอร์
    </h2>

    <div v-if="pending" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div v-for="i in 4" :key="i" class="h-20 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
    </div>

    <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
      <table class="w-full min-w-[480px] text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800/80">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">เบราว์เซอร์</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">สถานะ</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">เวอร์ชัน</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">หมายเหตุ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
          <tr
            v-for="item in compatibility"
            :key="item.browser.id"
            class="bg-white dark:bg-gray-900"
          >
            <td class="px-4 py-3">
              <div class="flex items-center gap-2 font-medium text-gray-900 dark:text-white">
                <span
                  class="w-3 h-3 rounded-full shrink-0"
                  :style="{ backgroundColor: item.browser.color || '#6366F1' }"
                />
                {{ item.browser.name }}
              </div>
            </td>
            <td class="px-4 py-3">
              <span :class="statusClass(item.support_status)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium">
                <component :is="statusIcon(item.support_status)" class="w-4 h-4" />
                {{ statusLabel(item.support_status) }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
              <span v-if="item.version_added">ตั้งแต่ {{ item.version_added }}</span>
              <span v-if="item.version_removed" class="ml-1">(ถึง {{ item.version_removed }})</span>
              <span v-if="!item.version_added && !item.version_removed">—</span>
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
              {{ item.notes || '—' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<script setup lang="ts">
import {
  GlobeAltIcon,
  CheckCircleIcon,
  XCircleIcon,
  MinusCircleIcon,
  QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline'

export interface BrowserCompatibilityItem {
  browser: {
    id: number
    name: string
    slug: string
    icon?: string | null
    color?: string | null
  }
  support_status: 'yes' | 'no' | 'partial' | 'unknown'
  version_added?: string | null
  version_removed?: string | null
  notes?: string | null
}

const props = defineProps<{
  articleSlug: string
}>()

const config = useRuntimeConfig()

const { data, pending } = await useFetch<{ data: { compatibility: BrowserCompatibilityItem[] } }>(
  () => `${config.public.apiBase}/articles/${props.articleSlug}/compatibility`,
  {
    key: () => `compat-${props.articleSlug}`,
    watch: [() => props.articleSlug],
  },
)

const compatibility = computed(() => data.value?.data?.compatibility || [])
const hasData = computed(() => pending.value || compatibility.value.length > 0)

const statusLabel = (status: BrowserCompatibilityItem['support_status']) => {
  const labels = {
    yes: 'รองรับ',
    no: 'ไม่รองรับ',
    partial: 'รองรับบางส่วน',
    unknown: 'ไม่ทราบ',
  }
  return labels[status] || status
}

const statusClass = (status: BrowserCompatibilityItem['support_status']) => {
  const classes = {
    yes: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    no: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    partial: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    unknown: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
  }
  return classes[status] || classes.unknown
}

const statusIcon = (status: BrowserCompatibilityItem['support_status']) => {
  const icons = {
    yes: CheckCircleIcon,
    no: XCircleIcon,
    partial: MinusCircleIcon,
    unknown: QuestionMarkCircleIcon,
  }
  return icons[status] || QuestionMarkCircleIcon
}
</script>
