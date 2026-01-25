export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const token = useCookie('auth-token')

  const $api = async <T>(
    url: string,
    options: {
      method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
      body?: any
      params?: Record<string, any>
    } = {}
  ): Promise<T> => {
    const headers: HeadersInit = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    }

    if (token.value) {
      headers['Authorization'] = `Bearer ${token.value}`
    }

    const response = await $fetch<T>(`${config.public.apiBase}${url}`, {
      method: options.method || 'GET',
      headers,
      body: options.body,
      params: options.params,
      onResponseError({ response }) {
        // Handle 401 Unauthorized
        if (response.status === 401) {
          token.value = null
          if (process.client) {
            navigateTo('/auth/login')
          }
        }
        throw response._data
      },
    })

    return response
  }

  return {
    provide: {
      api: $api,
    },
  }
})
