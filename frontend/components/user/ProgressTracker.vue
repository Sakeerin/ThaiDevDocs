<template>
  <div>
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ label }}</span>
      <span class="text-sm font-medium text-primary-600">{{ clampedPercentage }}%</span>
    </div>
    <div class="h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
      <div
        class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-500"
        :style="{ width: `${clampedPercentage}%` }"
      />
    </div>
    <p v-if="showCount && total > 0" class="mt-2 text-xs text-gray-500">
      {{ completed }} / {{ total }} บทเรียน
    </p>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  percentage: number
  completed?: number
  total?: number
  label?: string
  showCount?: boolean
}>(), {
  completed: 0,
  total: 0,
  label: 'ความก้าวหน้า',
  showCount: true,
})

const clampedPercentage = computed(() => Math.min(100, Math.max(0, Math.round(props.percentage))))
</script>
