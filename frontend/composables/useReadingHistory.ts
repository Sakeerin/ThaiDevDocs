function getScrollProgress(): number {
  if (!import.meta.client) return 0

  const scrollTop = window.scrollY
  const scrollHeight = document.documentElement.scrollHeight - window.innerHeight

  if (scrollHeight <= 0) return 100

  return Math.min(100, Math.round((scrollTop / scrollHeight) * 100))
}

export function useReadingHistory() {
  const { isAuthenticated } = useAuth()
  const { $api } = useNuxtApp()

  let activeArticleId: number | null = null
  let sessionStart = 0
  let lastSavedProgress = 0
  let scrollHandler: (() => void) | null = null
  let intervalId: ReturnType<typeof setInterval> | null = null

  const record = async (
    articleId: number,
    options: { progress?: number; timeSpent?: number } = {},
  ) => {
    if (!isAuthenticated.value) return

    const body: Record<string, number> = { article_id: articleId }

    if (options.progress !== undefined) {
      body.progress = options.progress
    }

    if (options.timeSpent !== undefined && options.timeSpent > 0) {
      body.time_spent = options.timeSpent
    }

    try {
      await $api('/history', { method: 'POST', body })
    } catch {
      // Non-blocking — reading history is best-effort
    }
  }

  const flushSession = () => {
    if (!activeArticleId || !sessionStart) return

    const elapsed = Math.floor((Date.now() - sessionStart) / 1000)
    if (elapsed >= 5) {
      record(activeArticleId, {
        progress: lastSavedProgress,
        timeSpent: elapsed,
      })
    }
  }

  const stopTracking = () => {
    flushSession()

    if (scrollHandler) {
      window.removeEventListener('scroll', scrollHandler)
      scrollHandler = null
    }

    if (intervalId) {
      clearInterval(intervalId)
      intervalId = null
    }

    activeArticleId = null
    sessionStart = 0
    lastSavedProgress = 0
  }

  const startTracking = (articleId: number) => {
    if (!import.meta.client || !isAuthenticated.value) return

    if (activeArticleId === articleId) return

    stopTracking()

    activeArticleId = articleId
    sessionStart = Date.now()
    lastSavedProgress = 0

    record(articleId)

    scrollHandler = () => {
      const progress = getScrollProgress()
      if (progress >= lastSavedProgress + 10 || progress === 100) {
        lastSavedProgress = Math.max(lastSavedProgress, progress)
        record(articleId, { progress: lastSavedProgress })
      }
    }

    window.addEventListener('scroll', scrollHandler, { passive: true })

    intervalId = setInterval(() => {
      const progress = getScrollProgress()
      lastSavedProgress = Math.max(lastSavedProgress, progress)
      record(articleId, { progress: lastSavedProgress, timeSpent: 30 })
      sessionStart = Date.now()
    }, 30_000)
  }

  onUnmounted(stopTracking)

  return {
    record,
    startTracking,
    stopTracking,
  }
}
