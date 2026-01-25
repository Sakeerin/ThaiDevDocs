<template>
  <nav class="space-y-6">
    <!-- Categories -->
    <div v-for="category in categories" :key="category.slug">
      <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
        <component :is="getCategoryIcon(category.icon)" class="w-5 h-5 text-gray-500" />
        {{ category.name }}
      </h3>
      <ul class="space-y-1 ml-7">
        <li v-for="topic in category.topics" :key="topic.slug">
          <NuxtLink
            :to="`/docs/${category.slug}/${topic.slug}`"
            class="block py-1.5 px-3 rounded-lg text-sm transition-colors"
            :class="[
              isActive(category.slug, topic.slug)
                ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-medium'
                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
          >
            {{ topic.name }}
          </NuxtLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script setup lang="ts">
import {
  CodeBracketIcon,
  SwatchIcon,
  CubeIcon,
  GlobeAltIcon,
  ServerIcon,
  DevicePhoneMobileIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()

// TODO: Fetch from API
const categories = ref([
  {
    name: 'HTML',
    slug: 'html',
    icon: 'code',
    topics: [
      { name: 'เริ่มต้น', slug: 'getting-started' },
      { name: 'องค์ประกอบ', slug: 'elements' },
      { name: 'ฟอร์ม', slug: 'forms' },
      { name: 'Semantic HTML', slug: 'semantic' },
    ],
  },
  {
    name: 'CSS',
    slug: 'css',
    icon: 'swatch',
    topics: [
      { name: 'พื้นฐาน', slug: 'basics' },
      { name: 'Selectors', slug: 'selectors' },
      { name: 'Box Model', slug: 'box-model' },
      { name: 'Flexbox', slug: 'flexbox' },
      { name: 'Grid', slug: 'grid' },
    ],
  },
  {
    name: 'JavaScript',
    slug: 'javascript',
    icon: 'cube',
    topics: [
      { name: 'เริ่มต้น', slug: 'getting-started' },
      { name: 'ตัวแปร', slug: 'variables' },
      { name: 'ฟังก์ชัน', slug: 'functions' },
      { name: 'อาร์เรย์', slug: 'arrays' },
      { name: 'ออบเจกต์', slug: 'objects' },
    ],
  },
])

const getCategoryIcon = (icon: string) => {
  const icons: Record<string, any> = {
    code: CodeBracketIcon,
    swatch: SwatchIcon,
    cube: CubeIcon,
    globe: GlobeAltIcon,
    server: ServerIcon,
    mobile: DevicePhoneMobileIcon,
  }
  return icons[icon] || CodeBracketIcon
}

const isActive = (categorySlug: string, topicSlug: string) => {
  const path = route.path
  return path.includes(`/docs/${categorySlug}/${topicSlug}`)
}
</script>
