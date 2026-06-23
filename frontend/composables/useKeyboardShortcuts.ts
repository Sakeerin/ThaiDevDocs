export const useKeyboardShortcuts = () => {
  const handlers = new Map<string, (event: KeyboardEvent) => void>()

  const register = (key: string, handler: (event: KeyboardEvent) => void) => {
    handlers.set(key, handler)
  }

  const unregister = (key: string) => {
    handlers.delete(key)
  }

  const onKeydown = (event: KeyboardEvent) => {
    const target = event.target as HTMLElement | null
    const tag = target?.tagName?.toLowerCase()
    const isEditable = tag === 'input' || tag === 'textarea' || target?.isContentEditable

    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
      if (!isEditable) {
        event.preventDefault()
        handlers.get('search')?.(event)
      }
      return
    }

    handlers.get(event.key)?.(event)
  }

  onMounted(() => {
    window.addEventListener('keydown', onKeydown)
  })

  onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown)
  })

  return {
    register,
    unregister,
  }
}
