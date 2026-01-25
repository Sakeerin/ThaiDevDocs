<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-primary-50/50 dark:from-gray-950 dark:to-primary-950/30">
      <div class="absolute inset-0 bg-grid-pattern opacity-5" />
      <div class="container mx-auto px-4 py-20 lg:py-32 relative">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
            เอกสารสำหรับ
            <span class="text-gradient">นักพัฒนาภาษาไทย</span>
          </h1>
          <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
            แหล่งความรู้ด้านการพัฒนาซอฟต์แวร์ภาษาไทยที่ครบถ้วน เข้าถึงง่าย และทันสมัยที่สุด
          </p>

          <!-- Search Bar -->
          <div class="max-w-xl mx-auto mb-8">
            <button
              @click="isSearchOpen = true"
              class="w-full flex items-center gap-3 px-6 py-4 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 text-gray-500 hover:border-primary-500 transition-colors"
            >
              <MagnifyingGlassIcon class="w-6 h-6" />
              <span class="flex-1 text-left text-lg">ค้นหาเอกสาร...</span>
              <kbd class="hidden sm:inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg text-sm text-gray-500 border border-gray-300 dark:border-gray-600">
                ⌘K
              </kbd>
            </button>
          </div>

          <!-- Quick Links -->
          <div class="flex flex-wrap justify-center gap-3">
            <NuxtLink to="/docs/html" class="btn-primary">
              เริ่มต้นเรียนรู้
            </NuxtLink>
            <NuxtLink to="/learn" class="btn-outline">
              เส้นทางการเรียนรู้
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Categories -->
    <section class="py-16 lg:py-24">
      <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">
          หมวดหมู่เอกสาร
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <NuxtLink
            v-for="category in featuredCategories"
            :key="category.slug"
            :to="`/docs/${category.slug}`"
            class="card p-6 hover:shadow-lg hover:border-primary-200 dark:hover:border-primary-800 transition-all group"
          >
            <div
              class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center"
              :style="{ backgroundColor: category.color + '20' }"
            >
              <component
                :is="getCategoryIcon(category.icon)"
                class="w-6 h-6"
                :style="{ color: category.color }"
              />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
              {{ category.name }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
              {{ category.description }}
            </p>
            <span class="text-sm text-primary-600 dark:text-primary-400 flex items-center gap-1">
              {{ category.topics_count }} หัวข้อ
              <ArrowRightIcon class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </span>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Featured Articles -->
    <section class="py-16 lg:py-24 bg-gray-50 dark:bg-gray-900/50">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-12">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
            บทความแนะนำ
          </h2>
          <NuxtLink to="/articles" class="text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1">
            ดูทั้งหมด
            <ArrowRightIcon class="w-4 h-4" />
          </NuxtLink>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <ArticleCard
            v-for="article in featuredArticles"
            :key="article.id"
            :article="article"
          />
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 lg:py-24">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">500+</div>
            <div class="text-gray-600 dark:text-gray-400">บทความ</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">50+</div>
            <div class="text-gray-600 dark:text-gray-400">หัวข้อ</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">1000+</div>
            <div class="text-gray-600 dark:text-gray-400">ผู้ใช้งาน</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 dark:text-primary-400 mb-2">100%</div>
            <div class="text-gray-600 dark:text-gray-400">ฟรี</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 lg:py-24 bg-primary-600">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
          พร้อมที่จะเริ่มต้นหรือยัง?
        </h2>
        <p class="text-primary-100 mb-8 max-w-2xl mx-auto">
          เริ่มต้นเรียนรู้การพัฒนาซอฟต์แวร์ด้วยเอกสารภาษาไทยที่เข้าใจง่าย
        </p>
        <NuxtLink to="/docs/html/getting-started" class="btn bg-white text-primary-600 hover:bg-primary-50">
          เริ่มต้นเลย
        </NuxtLink>
      </div>
    </section>

    <!-- Search Modal -->
    <SearchModal v-model="isSearchOpen" />
  </div>
</template>

<script setup lang="ts">
import { MagnifyingGlassIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'
import {
  CodeBracketIcon,
  SwatchIcon,
  CubeIcon,
  GlobeAltIcon,
  ServerIcon,
  DevicePhoneMobileIcon,
} from '@heroicons/vue/24/outline'

definePageMeta({
  layout: 'default',
})

useHead({
  title: 'หน้าแรก',
})

const isSearchOpen = ref(false)

// TODO: Fetch from API
const featuredCategories = ref([
  {
    name: 'HTML',
    slug: 'html',
    icon: 'code',
    color: '#E34C26',
    description: 'โครงสร้างพื้นฐานของเว็บไซต์ รู้จักกับ Elements และ Tags ต่างๆ',
    topics_count: 12,
  },
  {
    name: 'CSS',
    slug: 'css',
    icon: 'swatch',
    color: '#264DE4',
    description: 'การตกแต่งหน้าเว็บ Layout และ Animation ต่างๆ',
    topics_count: 18,
  },
  {
    name: 'JavaScript',
    slug: 'javascript',
    icon: 'cube',
    color: '#F7DF1E',
    description: 'ภาษาโปรแกรมสำหรับเว็บ รู้จักกับ DOM และ APIs',
    topics_count: 25,
  },
  {
    name: 'Web APIs',
    slug: 'web-apis',
    icon: 'globe',
    color: '#9B59B6',
    description: 'APIs ต่างๆ ที่เบราว์เซอร์มีให้ เช่น Fetch, Storage, etc.',
    topics_count: 15,
  },
  {
    name: 'Vue.js',
    slug: 'vue',
    icon: 'cube',
    color: '#42B883',
    description: 'Framework สำหรับสร้าง UI ที่ได้รับความนิยมสูง',
    topics_count: 20,
  },
  {
    name: 'Node.js',
    slug: 'nodejs',
    icon: 'server',
    color: '#339933',
    description: 'JavaScript Runtime สำหรับ Backend Development',
    topics_count: 14,
  },
])

const featuredArticles = ref([
  {
    id: 1,
    title: 'เริ่มต้นกับ HTML5',
    slug: 'html/getting-started',
    summary: 'เรียนรู้พื้นฐาน HTML5 สำหรับผู้เริ่มต้นที่ต้องการสร้างเว็บไซต์',
    difficulty: 'beginner',
    reading_time: 10,
    topic: { name: 'HTML', slug: 'html' },
  },
  {
    id: 2,
    title: 'CSS Flexbox Layout',
    slug: 'css/flexbox',
    summary: 'ทำความเข้าใจ Flexbox สำหรับจัดวาง Layout อย่างยืดหยุ่น',
    difficulty: 'intermediate',
    reading_time: 15,
    topic: { name: 'CSS', slug: 'css' },
  },
  {
    id: 3,
    title: 'JavaScript Async/Await',
    slug: 'javascript/async-await',
    summary: 'จัดการ Asynchronous Code ด้วย async/await อย่างมืออาชีพ',
    difficulty: 'intermediate',
    reading_time: 12,
    topic: { name: 'JavaScript', slug: 'javascript' },
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
</script>

<style scoped>
.bg-grid-pattern {
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
</style>
