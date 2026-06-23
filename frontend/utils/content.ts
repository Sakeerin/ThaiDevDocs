import {
  CodeBracketIcon,
  SwatchIcon,
  CubeIcon,
  GlobeAltIcon,
  ServerIcon,
  DevicePhoneMobileIcon,
  PaintBrushIcon,
} from '@heroicons/vue/24/outline'

interface ArticlePathInput {
  slug: string
  topic?: {
    slug: string
    category?: {
      slug: string
    } | null
  } | null
}

export function getArticlePath(article: ArticlePathInput): string {
  const categorySlug = article.topic?.category?.slug
  const topicSlug = article.topic?.slug

  if (categorySlug && topicSlug) {
    return `/docs/${categorySlug}/${topicSlug}/${article.slug}`
  }

  return `/docs/${article.slug}`
}

const categoryIconMap: Record<string, typeof CodeBracketIcon> = {
  code: CodeBracketIcon,
  swatch: SwatchIcon,
  'paint-brush': PaintBrushIcon,
  cube: CubeIcon,
  globe: GlobeAltIcon,
  server: ServerIcon,
  mobile: DevicePhoneMobileIcon,
}

export function getCategoryIcon(icon?: string | null) {
  return categoryIconMap[icon || ''] || CodeBracketIcon
}

export const defaultCategoryColor = '#6366F1'
