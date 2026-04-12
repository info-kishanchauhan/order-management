export interface ApiErrorBody {
  status: number
  message: string
  errors?: Record<string, string[]>
}

export function useApi () {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  function apiUrl (path: string): string {
    const base = (config.public.apiBase as string).replace(/\/$/, '')
    const p = path.startsWith('/') ? path : `/${path}`
    return `${base}${p}`
  }

  async function apiFetch<T> (
    path: string,
    options: {
      method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
      body?: Record<string, unknown>
      query?: Record<string, string | number | undefined>
      signal?: AbortSignal
    } = {}
  ): Promise<T> {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      'Content-Type': 'application/json'
    }
    if (authStore.token) {
      headers.Authorization = `Bearer ${authStore.token}`
    }

    return await $fetch<T>(apiUrl(path), {
      method: options.method ?? 'GET',
      body: options.body,
      query: options.query,
      headers,
      signal: options.signal
    })
  }

  return { apiUrl, apiFetch }
}
