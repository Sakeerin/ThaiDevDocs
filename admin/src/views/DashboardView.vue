<template>
  <div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label">บทความทั้งหมด</p>
            <p class="stat-value">{{ stats.articles?.total || 0 }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
            <DocumentTextIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
          </div>
        </div>
        <p class="stat-change positive">
          {{ stats.articles?.published || 0 }} เผยแพร่แล้ว
        </p>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label">ผู้ใช้</p>
            <p class="stat-value">{{ stats.users?.total || 0 }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
            <UsersIcon class="w-6 h-6 text-green-600 dark:text-green-400" />
          </div>
        </div>
        <p class="stat-change positive">
          +{{ stats.users?.new_this_week || 0 }} สัปดาห์นี้
        </p>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label">ยอดเข้าชม</p>
            <p class="stat-value">{{ formatNumber(stats.views?.total || 0) }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
            <EyeIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
        <p class="stat-change positive">
          {{ formatNumber(stats.views?.this_week || 0) }} สัปดาห์นี้
        </p>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label">รอตรวจสอบ</p>
            <p class="stat-value">{{ stats.comments?.pending || 0 }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
            <ChatBubbleLeftIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
          </div>
        </div>
        <p class="stat-change">
          {{ stats.contributions?.pending || 0 }} การมีส่วนร่วม
        </p>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          ยอดเข้าชมรายวัน
        </h3>
        <apexchart
          type="area"
          height="300"
          :options="viewsChartOptions"
          :series="viewsChartSeries"
        />
      </div>

      <div class="card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          บทความใหม่รายวัน
        </h3>
        <apexchart
          type="bar"
          height="300"
          :options="articlesChartOptions"
          :series="articlesChartSeries"
        />
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          กิจกรรมล่าสุด
        </h3>
      </div>
      <div class="divide-y divide-gray-200 dark:divide-gray-700">
        <div
          v-for="(activity, index) in recentActivity"
          :key="index"
          class="px-6 py-4 flex items-start gap-4"
        >
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
            :class="getActivityBgClass(activity.type)"
          >
            <component :is="getActivityIcon(activity.type)" class="w-5 h-5" :class="getActivityIconClass(activity.type)" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-900 dark:text-white">
              {{ activity.message }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ formatDate(activity.timestamp) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import {
  DocumentTextIcon,
  UsersIcon,
  EyeIcon,
  ChatBubbleLeftIcon,
  CheckCircleIcon,
  PlusCircleIcon,
} from '@heroicons/vue/24/outline'

const stats = ref<any>({})
const chartData = ref<any>({})
const recentActivity = ref<any[]>([])

onMounted(async () => {
  await Promise.all([
    fetchStats(),
    fetchCharts(),
    fetchRecentActivity(),
  ])
})

const fetchStats = async () => {
  try {
    const response = await api.get<any>('/admin/dashboard/stats')
    stats.value = response.data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

const fetchCharts = async () => {
  try {
    const response = await api.get<any>('/admin/dashboard/charts', {
      params: { days: 30 }
    })
    chartData.value = response.data
  } catch (error) {
    console.error('Failed to fetch charts:', error)
  }
}

const fetchRecentActivity = async () => {
  try {
    const response = await api.get<any>('/admin/dashboard/recent-activity')
    recentActivity.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch recent activity:', error)
  }
}

const formatNumber = (num: number) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num.toString()
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getActivityIcon = (type: string) => {
  const icons: Record<string, any> = {
    article_created: PlusCircleIcon,
    comment_added: ChatBubbleLeftIcon,
    contribution_approved: CheckCircleIcon,
  }
  return icons[type] || DocumentTextIcon
}

const getActivityBgClass = (type: string) => {
  const classes: Record<string, string> = {
    article_created: 'bg-blue-100 dark:bg-blue-900/30',
    comment_added: 'bg-green-100 dark:bg-green-900/30',
    contribution_approved: 'bg-purple-100 dark:bg-purple-900/30',
  }
  return classes[type] || 'bg-gray-100 dark:bg-gray-800'
}

const getActivityIconClass = (type: string) => {
  const classes: Record<string, string> = {
    article_created: 'text-blue-600 dark:text-blue-400',
    comment_added: 'text-green-600 dark:text-green-400',
    contribution_approved: 'text-purple-600 dark:text-purple-400',
  }
  return classes[type] || 'text-gray-600 dark:text-gray-400'
}

const viewsChartOptions = computed(() => ({
  chart: {
    id: 'views-chart',
    toolbar: { show: false },
    fontFamily: 'IBM Plex Sans Thai, sans-serif',
  },
  colors: ['#6366F1'],
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.1,
    },
  },
  xaxis: {
    categories: chartData.value.views?.map((v: any) => v.date) || [],
    labels: { style: { colors: '#9CA3AF' } },
  },
  yaxis: { labels: { style: { colors: '#9CA3AF' } } },
  grid: { borderColor: '#374151' },
  tooltip: { theme: 'dark' },
}))

const viewsChartSeries = computed(() => [{
  name: 'ยอดเข้าชม',
  data: chartData.value.views?.map((v: any) => v.count) || [],
}])

const articlesChartOptions = computed(() => ({
  chart: {
    id: 'articles-chart',
    toolbar: { show: false },
    fontFamily: 'IBM Plex Sans Thai, sans-serif',
  },
  colors: ['#10B981'],
  plotOptions: {
    bar: { borderRadius: 4, columnWidth: '60%' },
  },
  xaxis: {
    categories: chartData.value.articles?.map((a: any) => a.date) || [],
    labels: { style: { colors: '#9CA3AF' } },
  },
  yaxis: { labels: { style: { colors: '#9CA3AF' } } },
  grid: { borderColor: '#374151' },
  tooltip: { theme: 'dark' },
}))

const articlesChartSeries = computed(() => [{
  name: 'บทความใหม่',
  data: chartData.value.articles?.map((a: any) => a.count) || [],
}])
</script>
