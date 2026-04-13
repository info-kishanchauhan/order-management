import { defineStore } from 'pinia'

export interface OrderRow {
  id: number
  order_code: string | null
  customer_name: string
  item_name: string
  price_cents: number
  status: string
  created_at: string | null
  updated_at: string | null
}

export interface OrdersPayload {
  items: OrderRow[]
  pagination: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    has_more_pages: boolean
    prev_page_url: string | null
    next_page_url: string | null
  }
}

interface ListResponse {
  status: number
  message: string
  data: OrdersPayload
}

export const useOrdersStore = defineStore('orders', () => {
  const { apiFetch } = useApi()

  const items = ref<OrderRow[]>([])
  const pagination = ref<OrdersPayload['pagination'] | null>(null)

  const page = ref(1)
  const perPage = ref(10)
  const sort = ref<
    'created_at' | 'price_cents' | 'status' | 'customer_name' | 'item_name' | 'order_code'
  >('created_at')
  const direction = ref<'asc' | 'desc'>('desc')
  const statusFilter = ref<string>('')
  const searchQ = ref('')

  const loading = ref(false)
  const error = ref<string | null>(null)

  let listAbort: AbortController | null = null

  function isAbortError (e: unknown): boolean {
    if (typeof e !== 'object' || e === null) { return false }
    const err = e as Error & { cause?: unknown }
    if (err.name === 'AbortError') { return true }
    const c = err.cause
    if (c && typeof c === 'object' && c !== null && 'name' in c) {
      return (c as Error).name === 'AbortError'
    }
    return false
  }

  function buildQuery (): Record<string, string | number | undefined> {
    const q: Record<string, string | number | undefined> = {
      page: page.value,
      per_page: perPage.value,
      sort: sort.value,
      direction: direction.value
    }
    if (statusFilter.value) {
      q.status = statusFilter.value
    }
    if (searchQ.value.trim()) {
      q.q = searchQ.value.trim()
    }
    return q
  }

  async function fetchOrders () {
    listAbort?.abort()
    listAbort = new AbortController()
    loading.value = true
    error.value = null
    try {
      const res = await apiFetch<ListResponse>('/api/orders', {
        query: buildQuery(),
        signal: listAbort.signal
      })
      items.value = res.data.items
      pagination.value = res.data.pagination
    } catch (e: unknown) {
      if (isAbortError(e)) { return }
      const err = e as Error & { data?: { message?: string } }
      error.value = err.data?.message || err.message || 'Failed to load orders'
      items.value = []
      pagination.value = null
    } finally {
      loading.value = false
    }
  }

  return {
    items,
    pagination,
    page,
    perPage,
    sort,
    direction,
    statusFilter,
    searchQ,
    loading,
    error,
    fetchOrders
  }
})
