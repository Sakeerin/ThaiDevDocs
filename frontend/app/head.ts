// Default SEO meta tags for the application
export const defaultHead = {
  title: 'Thai Developer Docs',
  titleTemplate: '%s | Thai Developer Docs',
  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    {
      name: 'description',
      content: 'เอกสารสำหรับนักพัฒนาไทย - เรียนรู้การพัฒนาเว็บไซต์ด้วยเนื้อหาภาษาไทยที่เข้าใจง่าย พร้อมตัวอย่างโค้ดและแบบฝึกหัด',
    },
    { name: 'theme-color', content: '#6366F1' },
    { name: 'robots', content: 'index, follow' },
    { name: 'author', content: 'Thai Developer Docs' },
    
    // Open Graph
    { property: 'og:type', content: 'website' },
    { property: 'og:site_name', content: 'Thai Developer Docs' },
    { property: 'og:locale', content: 'th_TH' },
    
    // Twitter
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:site', content: '@thaidevdocs' },
  ],
  link: [
    { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
    { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' },
    { rel: 'manifest', href: '/manifest.json' },
    
    // Preconnect to external resources
    { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
    { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
  ],
  htmlAttrs: {
    lang: 'th',
  },
}

// Generate article-specific meta tags
export function generateArticleMeta(article: {
  title: string
  summary?: string
  slug: string
  author?: { name: string }
  published_at?: string
  updated_at?: string
  topic?: { category?: { name: string } }
  tags?: Array<{ name: string }>
}) {
  const url = `https://thaidevdocs.com/docs/${article.slug}`
  const description = article.summary || `เรียนรู้เกี่ยวกับ ${article.title} ด้วยเนื้อหาภาษาไทยที่เข้าใจง่าย`
  
  return {
    title: article.title,
    meta: [
      { name: 'description', content: description },
      { name: 'keywords', content: article.tags?.map(t => t.name).join(', ') },
      
      // Open Graph
      { property: 'og:title', content: article.title },
      { property: 'og:description', content: description },
      { property: 'og:url', content: url },
      { property: 'og:type', content: 'article' },
      { property: 'article:author', content: article.author?.name },
      { property: 'article:published_time', content: article.published_at },
      { property: 'article:modified_time', content: article.updated_at },
      { property: 'article:section', content: article.topic?.category?.name },
      
      // Twitter
      { name: 'twitter:title', content: article.title },
      { name: 'twitter:description', content: description },
    ],
    link: [
      { rel: 'canonical', href: url },
    ],
  }
}

// Generate structured data for article
export function generateArticleJsonLd(article: {
  title: string
  summary?: string
  content?: string
  slug: string
  author?: { name: string }
  published_at?: string
  updated_at?: string
}) {
  return {
    '@context': 'https://schema.org',
    '@type': 'TechArticle',
    headline: article.title,
    description: article.summary,
    author: {
      '@type': 'Person',
      name: article.author?.name || 'Thai Developer Docs',
    },
    publisher: {
      '@type': 'Organization',
      name: 'Thai Developer Docs',
      logo: {
        '@type': 'ImageObject',
        url: 'https://thaidevdocs.com/logo.png',
      },
    },
    datePublished: article.published_at,
    dateModified: article.updated_at,
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': `https://thaidevdocs.com/docs/${article.slug}`,
    },
    inLanguage: 'th-TH',
  }
}

// Generate breadcrumb structured data
export function generateBreadcrumbJsonLd(items: Array<{ name: string; url: string }>) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: item.url,
    })),
  }
}
