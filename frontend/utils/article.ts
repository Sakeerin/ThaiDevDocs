/** Extract article slug (last segment) from docs route params */
export function getArticleSlugFromRoute(slugParam: string | string[] | undefined): string {
  const segments = Array.isArray(slugParam) ? slugParam : slugParam ? [slugParam] : []
  return segments[segments.length - 1] ?? ''
}
