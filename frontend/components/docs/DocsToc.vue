<template>
  <div v-if="toc && toc.length > 0" class="text-sm">
    <h4 class="font-semibold text-gray-900 dark:text-white mb-4">
      ในหน้านี้
    </h4>
    <nav>
      <ul class="space-y-2">
        <li
          v-for="item in toc"
          :key="item.id"
          :style="{ paddingLeft: `${(item.level - 2) * 12}px` }"
        >
          <a
            :href="`#${item.id}`"
            class="block py-1 transition-colors"
            :class="[
              activeId === item.id
                ? 'text-primary-600 dark:text-primary-400 font-medium'
                : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
            ]"
          >
            {{ item.text }}
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup lang="ts">
interface TocItem {
  id: string
  text: string
  level: number
}

defineProps<{
  toc?: TocItem[]
}>()

const activeId = ref('')

// Track active section on scroll
onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          activeId.value = entry.target.id
        }
      })
    },
    {
      rootMargin: '-20% 0% -35% 0%',
    }
  )

  // Observe all headings
  document.querySelectorAll('h2[id], h3[id], h4[id]').forEach((heading) => {
    observer.observe(heading)
  })

  onUnmounted(() => observer.disconnect())
})
</script>
