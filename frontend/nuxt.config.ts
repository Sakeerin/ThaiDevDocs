// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  devtools: { enabled: true },

  modules: [
    '@nuxtjs/tailwindcss',
    '@nuxtjs/color-mode',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxt/image',
  ],

  app: {
    head: {
      title: 'ThaiDevDocs - เอกสารสำหรับนักพัฒนาภาษาไทย',
      htmlAttrs: {
        lang: 'th',
      },
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'แหล่งความรู้ด้านการพัฒนาซอฟต์แวร์ภาษาไทยที่ครบถ้วน เข้าถึงง่าย และทันสมัยที่สุด' },
        { name: 'format-detection', content: 'telephone=no' },
        { property: 'og:title', content: 'ThaiDevDocs - เอกสารสำหรับนักพัฒนาภาษาไทย' },
        { property: 'og:description', content: 'แหล่งความรู้ด้านการพัฒนาซอฟต์แวร์ภาษาไทยที่ครบถ้วน' },
        { property: 'og:type', content: 'website' },
        { property: 'og:locale', content: 'th_TH' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'sitemap', type: 'application/xml', href: process.env.NUXT_PUBLIC_SITEMAP_URL || 'http://localhost:8000/storage/sitemap.xml' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { 
          rel: 'stylesheet', 
          href: 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap' 
        },
      ],
    },
  },

  css: ['~/assets/css/main.css'],

  colorMode: {
    classSuffix: '',
    preference: 'system',
    fallback: 'light',
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/v1',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3000',
      sitemapUrl: process.env.NUXT_PUBLIC_SITEMAP_URL || 'http://localhost:8000/storage/sitemap.xml',
    },
  },

  routeRules: {
    '/': { prerender: true },
    '/docs/**': { swr: 3600 },
    '/api/**': { proxy: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api/**' },
  },

  nitro: {
    compressPublicAssets: true,
  },

  image: {
    quality: 80,
    format: ['webp', 'jpeg'],
    screens: {
      xs: 320,
      sm: 640,
      md: 768,
      lg: 1024,
      xl: 1280,
    },
  },

  experimental: {
    payloadExtraction: true,
    viewTransition: true,
  },

  compatibilityDate: '2024-01-01',
})
