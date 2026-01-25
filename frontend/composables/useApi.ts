export const useApi = () => {
  const config = useRuntimeConfig()
  const baseUrl = config.public.apiBase

  const authToken = useCookie('auth-token')

  const getHeaders = () => {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }

    if (authToken.value) {
      headers['Authorization'] = `Bearer ${authToken.value}`
    }

    return headers
  }

  const handleError = (error: any) => {
    console.error('API Error:', error)

    if (error.response?.status === 401) {
      // Clear token and redirect to login
      authToken.value = null
      navigateTo('/auth/login')
    }

    throw error
  }

  const get = async <T>(endpoint: string, params?: Record<string, any>): Promise<T> => {
    try {
      const response = await $fetch<T>(`${baseUrl}${endpoint}`, {
        method: 'GET',
        headers: getHeaders(),
        params,
      })
      return response
    } catch (error) {
      return handleError(error)
    }
  }

  const post = async <T>(endpoint: string, body?: any): Promise<T> => {
    try {
      const response = await $fetch<T>(`${baseUrl}${endpoint}`, {
        method: 'POST',
        headers: getHeaders(),
        body,
      })
      return response
    } catch (error) {
      return handleError(error)
    }
  }

  const put = async <T>(endpoint: string, body?: any): Promise<T> => {
    try {
      const response = await $fetch<T>(`${baseUrl}${endpoint}`, {
        method: 'PUT',
        headers: getHeaders(),
        body,
      })
      return response
    } catch (error) {
      return handleError(error)
    }
  }

  const patch = async <T>(endpoint: string, body?: any): Promise<T> => {
    try {
      const response = await $fetch<T>(`${baseUrl}${endpoint}`, {
        method: 'PATCH',
        headers: getHeaders(),
        body,
      })
      return response
    } catch (error) {
      return handleError(error)
    }
  }

  const del = async <T>(endpoint: string): Promise<T> => {
    try {
      const response = await $fetch<T>(`${baseUrl}${endpoint}`, {
        method: 'DELETE',
        headers: getHeaders(),
      })
      return response
    } catch (error) {
      return handleError(error)
    }
  }

  return {
    get,
    post,
    put,
    patch,
    del,
  }
}
