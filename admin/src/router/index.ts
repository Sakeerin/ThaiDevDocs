import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue')
      },
      {
        path: 'articles',
        name: 'articles',
        component: () => import('@/views/articles/ArticleListView.vue')
      },
      {
        path: 'articles/create',
        name: 'article-create',
        component: () => import('@/views/articles/ArticleEditorView.vue')
      },
      {
        path: 'articles/:id/edit',
        name: 'article-edit',
        component: () => import('@/views/articles/ArticleEditorView.vue')
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/views/categories/CategoryListView.vue')
      },
      {
        path: 'topics',
        name: 'topics',
        component: () => import('@/views/topics/TopicListView.vue')
      },
      {
        path: 'tags',
        name: 'tags',
        component: () => import('@/views/tags/TagListView.vue')
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/views/users/UserListView.vue')
      },
      {
        path: 'comments',
        name: 'comments',
        component: () => import('@/views/comments/CommentListView.vue')
      },
      {
        path: 'media',
        name: 'media',
        component: () => import('@/views/media/MediaLibraryView.vue')
      },
      {
        path: 'analytics',
        name: 'analytics',
        component: () => import('@/views/analytics/AnalyticsView.vue')
      },
      {
        path: 'settings',
        name: 'settings',
        component: () => import('@/views/settings/SettingsView.vue')
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue')
  }
]

const router = createRouter({
  history: createWebHistory('/admin'),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'dashboard' })
  } else {
    next()
  }
})

export default router
