<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">การวิเคราะห์</h1>
        <p class="text-gray-600 dark:text-gray-400">ดูสถิติและข้อมูลเชิงลึก</p>
      </div>
      <div class="flex gap-2">
        <select v-model="period" @change="fetchAnalytics" class="input w-auto">
          <option value="7d">7 วันที่ผ่านมา</option>
          <option value="30d">30 วันที่ผ่านมา</option>
          <option value="90d">90 วันที่ผ่านมา</option>
          <option value="1y">1 ปี</option>
        </select>
        <button @click="exportReport" class="btn-outline">
          <ArrowDownTrayIcon class="w-5 h-5 mr-2" />
          ส่งออก
        </button>
      </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">การเข้าชมทั้งหมด</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ formatNumber(stats.total_views) }}</p>
            <p :class="['text-sm mt-1', stats.views_change >= 0 ? 'text-emerald-600' : 'text-red-600']">
              {{ stats.views_change >= 0 ? '+' : '' }}{{ stats.views_change }}% จากช่วงก่อน
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <EyeIcon class="w-6 h-6 text-blue-600" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">ผู้ใช้ใหม่</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ formatNumber(stats.new_users) }}</p>
            <p :class="['text-sm mt-1', stats.users_change >= 0 ? 'text-emerald-600' : 'text-red-600']">
              {{ stats.users_change >= 0 ? '+' : '' }}{{ stats.users_change }}% จากช่วงก่อน
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
            <UserGroupIcon class="w-6 h-6 text-emerald-600" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">เวลาอ่านเฉลี่ย</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.avg_read_time }}<span class="text-lg">นาที</span></p>
            <p :class="['text-sm mt-1', stats.time_change >= 0 ? 'text-emerald-600' : 'text-red-600']">
              {{ stats.time_change >= 0 ? '+' : '' }}{{ stats.time_change }}% จากช่วงก่อน
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
            <ClockIcon class="w-6 h-6 text-purple-600" />
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">อัตราตีกลับ</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.bounce_rate }}<span class="text-lg">%</span></p>
            <p :class="['text-sm mt-1', stats.bounce_change <= 0 ? 'text-emerald-600' : 'text-red-600']">
              {{ stats.bounce_change >= 0 ? '+' : '' }}{{ stats.bounce_change }}% จากช่วงก่อน
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
            <ArrowTrendingUpIcon class="w-6 h-6 text-amber-600" />
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Page Views Chart -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">การเข้าชมรายวัน</h3>
        <div class="h-64">
          <apexchart
            type="area"
            height="100%"
            :options="pageViewsChartOptions"
            :series="pageViewsSeries"
          />
        </div>
      </div>

      <!-- Users Chart -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">ผู้ใช้ใหม่</h3>
        <div class="h-64">
          <apexchart
            type="bar"
            height="100%"
            :options="usersChartOptions"
            :series="usersSeries"
          />
        </div>
      </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Articles -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">บทความยอดนิยม</h3>
        <div class="space-y-4">
          <div
            v-for="(article, index) in topArticles"
            :key="article.id"
            class="flex items-center gap-4"
          >
            <span class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-medium text-gray-600 dark:text-gray-400">
              {{ index + 1 }}
            </span>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 dark:text-white truncate">{{ article.title }}</p>
              <p class="text-sm text-gray-500">{{ formatNumber(article.views) }} เข้าชม</p>
            </div>
            <div class="text-right">
              <p class="font-medium text-gray-900 dark:text-white">{{ article.avg_time }}น.</p>
              <p class="text-sm text-gray-500">เวลาอ่าน</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Search Terms -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">คำค้นหายอดนิยม</h3>
        <div class="space-y-4">
          <div
            v-for="(search, index) in topSearches"
            :key="index"
            class="flex items-center gap-4"
          >
            <span class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
              <MagnifyingGlassIcon class="w-4 h-4 text-primary-600" />
            </span>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 dark:text-white">{{ search.term }}</p>
            </div>
            <div class="text-right">
              <p class="font-medium text-gray-900 dark:text-white">{{ formatNumber(search.count) }}</p>
              <p class="text-sm text-gray-500">ครั้ง</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Category & Difficulty Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- By Category -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">การเข้าชมตามหมวดหมู่</h3>
        <div class="h-64">
          <apexchart
            type="donut"
            height="100%"
            :options="categoryChartOptions"
            :series="categorySeries"
          />
        </div>
      </div>

      <!-- By Difficulty -->
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">การเข้าชมตามระดับความยาก</h3>
        <div class="space-y-4">
          <div v-for="level in difficultyStats" :key="level.name">
            <div class="flex items-center justify-between mb-1">
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ level.name }}</span>
              <span class="text-sm text-gray-500">{{ level.percentage }}%</span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="level.colorClass"
                :style="{ width: `${level.percentage}%` }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Device & Browser Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">อุปกรณ์</h3>
        <div class="space-y-3">
          <div v-for="device in deviceStats" :key="device.name" class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <component :is="device.icon" class="w-5 h-5 text-gray-400" />
              <span class="text-gray-700 dark:text-gray-300">{{ device.name }}</span>
            </div>
            <span class="font-medium text-gray-900 dark:text-white">{{ device.percentage }}%</span>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">เบราว์เซอร์</h3>
        <div class="space-y-3">
          <div v-for="browser in browserStats" :key="browser.name" class="flex items-center justify-between">
            <span class="text-gray-700 dark:text-gray-300">{{ browser.name }}</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ browser.percentage }}%</span>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">ช่วงเวลา</h3>
        <div class="space-y-3">
          <div v-for="time in timeStats" :key="time.name" class="flex items-center justify-between">
            <span class="text-gray-700 dark:text-gray-300">{{ time.name }}</span>
            <span class="font-medium text-gray-900 dark:text-white">{{ time.percentage }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import {
  EyeIcon,
  UserGroupIcon,
  ClockIcon,
  ArrowTrendingUpIcon,
  ArrowDownTrayIcon,
  MagnifyingGlassIcon,
  DevicePhoneMobileIcon,
  ComputerDesktopIcon,
  DeviceTabletIcon,
} from '@heroicons/vue/24/outline'
import api from '@/services/api'

const period = ref('30d')

const stats = reactive({
  total_views: 0,
  views_change: 0,
  new_users: 0,
  users_change: 0,
  avg_read_time: 0,
  time_change: 0,
  bounce_rate: 0,
  bounce_change: 0,
})

const topArticles = ref<any[]>([])
const topSearches = ref<any[]>([])

const pageViewsSeries = ref([{ name: 'เข้าชม', data: [] as number[] }])
const usersSeries = ref([{ name: 'ผู้ใช้ใหม่', data: [] as number[] }])
const categorySeries = ref<number[]>([])
const categoryLabels = ref<string[]>([])

const difficultyStats = ref([
  { name: 'เริ่มต้น', percentage: 45, colorClass: 'bg-emerald-500' },
  { name: 'กลาง', percentage: 35, colorClass: 'bg-amber-500' },
  { name: 'สูง', percentage: 20, colorClass: 'bg-red-500' },
])

const deviceStats = ref([
  { name: 'มือถือ', percentage: 55, icon: DevicePhoneMobileIcon },
  { name: 'เดสก์ท็อป', percentage: 38, icon: ComputerDesktopIcon },
  { name: 'แท็บเล็ต', percentage: 7, icon: DeviceTabletIcon },
])

const browserStats = ref([
  { name: 'Chrome', percentage: 65 },
  { name: 'Safari', percentage: 20 },
  { name: 'Firefox', percentage: 8 },
  { name: 'อื่นๆ', percentage: 7 },
])

const timeStats = ref([
  { name: 'เช้า (6-12)', percentage: 25 },
  { name: 'บ่าย (12-18)', percentage: 35 },
  { name: 'เย็น (18-24)', percentage: 30 },
  { name: 'ดึก (0-6)', percentage: 10 },
])

const pageViewsChartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    zoom: { enabled: false },
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0,
    },
  },
  colors: ['#6366F1'],
  xaxis: {
    categories: getLast30Days(),
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: { labels: { show: false } },
  grid: { show: false },
  tooltip: {
    x: { show: true },
  },
}))

const usersChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    toolbar: { show: false },
  },
  dataLabels: { enabled: false },
  colors: ['#10B981'],
  xaxis: {
    categories: getLast30Days(),
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: { labels: { show: false } },
  grid: { show: false },
  plotOptions: {
    bar: {
      borderRadius: 4,
      columnWidth: '60%',
    },
  },
}))

const categoryChartOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: categoryLabels.value,
  colors: ['#6366F1', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6'],
  legend: {
    position: 'bottom',
  },
  dataLabels: {
    enabled: true,
    formatter: (val: number) => val.toFixed(0) + '%',
  },
}))

onMounted(fetchAnalytics)

async function fetchAnalytics() {
  try {
    const response = await api.get<any>('/admin/analytics', {
      params: { period: period.value }
    })
    
    Object.assign(stats, response.data.stats)
    topArticles.value = response.data.top_articles || getMockTopArticles()
    topSearches.value = response.data.top_searches || getMockTopSearches()
    
    pageViewsSeries.value = [{ name: 'เข้าชม', data: response.data.page_views || generateMockData(30, 100, 1000) }]
    usersSeries.value = [{ name: 'ผู้ใช้ใหม่', data: response.data.user_registrations || generateMockData(30, 5, 50) }]
    
    if (response.data.categories) {
      categoryLabels.value = response.data.categories.map((c: any) => c.name)
      categorySeries.value = response.data.categories.map((c: any) => c.percentage)
    } else {
      categoryLabels.value = ['JavaScript', 'CSS', 'HTML', 'React', 'อื่นๆ']
      categorySeries.value = [35, 25, 20, 12, 8]
    }
  } catch (error) {
    console.error('Failed to fetch analytics:', error)
    // Use mock data
    stats.total_views = 125420
    stats.views_change = 12
    stats.new_users = 3420
    stats.users_change = 8
    stats.avg_read_time = 4.5
    stats.time_change = 5
    stats.bounce_rate = 32
    stats.bounce_change = -3
    
    topArticles.value = getMockTopArticles()
    topSearches.value = getMockTopSearches()
    pageViewsSeries.value = [{ name: 'เข้าชม', data: generateMockData(30, 100, 1000) }]
    usersSeries.value = [{ name: 'ผู้ใช้ใหม่', data: generateMockData(30, 5, 50) }]
    categoryLabels.value = ['JavaScript', 'CSS', 'HTML', 'React', 'อื่นๆ']
    categorySeries.value = [35, 25, 20, 12, 8]
  }
}

function exportReport() {
  // In a real app, this would generate and download a report
  alert('ฟีเจอร์ส่งออกรายงานจะมาในเร็วๆ นี้')
}

function formatNumber(num: number) {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

function getLast30Days() {
  const days = []
  for (let i = 29; i >= 0; i--) {
    const date = new Date()
    date.setDate(date.getDate() - i)
    days.push(date.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' }))
  }
  return days
}

function generateMockData(count: number, min: number, max: number) {
  return Array.from({ length: count }, () => Math.floor(Math.random() * (max - min + 1)) + min)
}

function getMockTopArticles() {
  return [
    { id: 1, title: 'JavaScript เบื้องต้นสำหรับมือใหม่', views: 15420, avg_time: 8 },
    { id: 2, title: 'CSS Flexbox อธิบายแบบเข้าใจง่าย', views: 12350, avg_time: 6 },
    { id: 3, title: 'React Hooks: useState และ useEffect', views: 9870, avg_time: 10 },
    { id: 4, title: 'HTML5 Semantic Elements', views: 8540, avg_time: 5 },
    { id: 5, title: 'TypeScript สำหรับ React', views: 7230, avg_time: 12 },
  ]
}

function getMockTopSearches() {
  return [
    { term: 'javascript', count: 3240 },
    { term: 'css flexbox', count: 2180 },
    { term: 'react hooks', count: 1950 },
    { term: 'html form', count: 1420 },
    { term: 'typescript', count: 1280 },
  ]
}
</script>
