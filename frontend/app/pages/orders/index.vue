<template>
  <div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">
          Your orders
        </h1>
        <p class="mt-1 text-slate-600">
          Click column headers to sort. Edit or delete a row from Actions.
        </p>
      </div>
      <UiButton type="button" @click="navigateTo('/orders/new')">
        New order
      </UiButton>
    </div>

    <div class="grid gap-4 rounded-2xl border border-emerald-200/60 bg-white/90 p-4 shadow-sm sm:grid-cols-2">
      <div>
        <UiLabel for-id="filter-q">Search</UiLabel>
        <UiInputText
          id="filter-q"
          v-model="searchInput"
          placeholder="Customer or item…"
        />
      </div>
      <div>
        <UiLabel for-id="filter-status">Filter by status</UiLabel>
        <select
          id="filter-status"
          v-model="store.statusFilter"
          :class="selectClass"
          @change="onFilterChange"
        >
          <option value="">
            All
          </option>
          <option value="pending">
            Pending
          </option>
          <option value="paid">
            Paid
          </option>
          <option value="cancelled">
            Cancelled
          </option>
        </select>
      </div>
    </div>

    <div
      v-if="store.loading"
      class="rounded-xl border border-emerald-200/60 bg-white p-12 text-center text-slate-600 shadow-sm"
    >
      Loading orders…
    </div>

    <div
      v-else-if="store.error"
      class="rounded-xl border border-red-200 bg-red-50 p-6 text-red-800 shadow-sm"
    >
      {{ store.error }}
      <UiButton class="ml-4" size="sm" @click="store.fetchOrders()">
        Retry
      </UiButton>
    </div>

    <div
      v-else-if="!store.items.length"
      class="rounded-xl border border-dashed border-emerald-300/70 bg-emerald-50/40 p-12 text-center text-slate-600"
    >
      No orders match your filters.
      <NuxtLink
        to="/orders/new"
        class="font-medium text-emerald-800 underline hover:text-emerald-950"
      >
        Create an order
      </NuxtLink>
      or adjust search.
    </div>

    <template v-else>
      <UiDataTable
        show-pagination
        :pagination="tablePagination"
        :loading="store.loading"
        @pagination-prev="prevPage"
        @pagination-next="nextPage"
        @pagination-go-to-page="goToPage"
      >
        <template #head>
          <tr>
            <th class="px-4 py-3">
              <button
                type="button"
                class="group inline-flex w-full items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide text-emerald-900/80 hover:text-emerald-950"
                @click="toggleSort('order_code')"
              >
                Order #
                <span
                  v-if="store.sort === 'order_code'"
                  class="text-emerald-700"
                  aria-hidden="true"
                >{{ store.direction === 'asc' ? '↑' : '↓' }}</span>
                <span
                  v-else
                  class="text-[0.65rem] font-normal text-emerald-600/50 opacity-0 transition group-hover:opacity-100"
                  aria-hidden="true"
                >↕</span>
              </button>
            </th>
            <th class="px-4 py-3">
              <button
                type="button"
                class="group inline-flex w-full items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide text-emerald-900/80 hover:text-emerald-950"
                @click="toggleSort('customer_name')"
              >
                Customer
                <span
                  v-if="store.sort === 'customer_name'"
                  class="text-emerald-700"
                  aria-hidden="true"
                >{{ store.direction === 'asc' ? '↑' : '↓' }}</span>
                <span
                  v-else
                  class="text-[0.65rem] font-normal text-emerald-600/50 opacity-0 transition group-hover:opacity-100"
                  aria-hidden="true"
                >↕</span>
              </button>
            </th>
            <th class="px-4 py-3">
              <button
                type="button"
                class="group inline-flex w-full items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide text-emerald-900/80 hover:text-emerald-950"
                @click="toggleSort('item_name')"
              >
                Item
                <span
                  v-if="store.sort === 'item_name'"
                  class="text-emerald-700"
                  aria-hidden="true"
                >{{ store.direction === 'asc' ? '↑' : '↓' }}</span>
                <span
                  v-else
                  class="text-[0.65rem] font-normal text-emerald-600/50 opacity-0 transition group-hover:opacity-100"
                  aria-hidden="true"
                >↕</span>
              </button>
            </th>
            <th class="px-4 py-3">
              <button
                type="button"
                class="group inline-flex w-full items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide text-emerald-900/80 hover:text-emerald-950"
                @click="toggleSort('price_cents')"
              >
                Price
                <span
                  v-if="store.sort === 'price_cents'"
                  class="text-emerald-700"
                  aria-hidden="true"
                >{{ store.direction === 'asc' ? '↑' : '↓' }}</span>
                <span
                  v-else
                  class="text-[0.65rem] font-normal text-emerald-600/50 opacity-0 transition group-hover:opacity-100"
                  aria-hidden="true"
                >↕</span>
              </button>
            </th>
            <th class="px-4 py-3">
              <button
                type="button"
                class="group inline-flex w-full items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide text-emerald-900/80 hover:text-emerald-950"
                @click="toggleSort('status')"
              >
                Order status
                <span
                  v-if="store.sort === 'status'"
                  class="text-emerald-700"
                  aria-hidden="true"
                >{{ store.direction === 'asc' ? '↑' : '↓' }}</span>
                <span
                  v-else
                  class="text-[0.65rem] font-normal text-emerald-600/50 opacity-0 transition group-hover:opacity-100"
                  aria-hidden="true"
                >↕</span>
              </button>
            </th>
            <th class="px-4 py-3">
              <button
                type="button"
                class="group inline-flex w-full items-center gap-1 text-left text-xs font-semibold uppercase tracking-wide text-emerald-900/80 hover:text-emerald-950"
                @click="toggleSort('created_at')"
              >
                Created
                <span
                  v-if="store.sort === 'created_at'"
                  class="text-emerald-700"
                  aria-hidden="true"
                >{{ store.direction === 'asc' ? '↑' : '↓' }}</span>
                <span
                  v-else
                  class="text-[0.65rem] font-normal text-emerald-600/50 opacity-0 transition group-hover:opacity-100"
                  aria-hidden="true"
                >↕</span>
              </button>
            </th>
            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-emerald-900/80">
              Actions
            </th>
          </tr>
        </template>
        <template #body>
          <tr v-for="row in store.items" :key="row.id">
            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm font-medium text-slate-900">
              {{ formatOrderRef(row) }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-900">
              {{ row.customer_name }}
            </td>
            <td class="max-w-xs truncate px-4 py-3 text-slate-700">
              {{ row.item_name }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate-800">
              {{ formatCents(row.price_cents) }}
            </td>
            <td class="relative z-10 px-4 py-3 align-middle">
              <UiListbox
                :model-value="row.status"
                :options="orderStatusOptions(row.status)"
                :tone="orderStatusTone(row.status)"
                :disabled="statusUpdatingId === row.id || deleteId !== null"
                open-above
                @update:model-value="(next) => updateOrderStatus(row, next)"
              />
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
              {{ formatDate(row.created_at) }}
            </td>
            <td class="whitespace-nowrap px-4 py-3">
              <div class="flex flex-wrap items-center gap-2">
                <UiButton
                  size="sm"
                  variant="outline"
                  type="button"
                  @click="navigateTo(`/orders/${row.id}/edit`)"
                >
                  Edit
                </UiButton>
                <UiButton
                  size="sm"
                  variant="outline"
                  type="button"
                  class="!border-red-300 !text-red-800 hover:!bg-red-50"
                  :disabled="statusUpdatingId !== null || deleteId !== null"
                  @click="openDeleteDialog(row)"
                >
                  Delete
                </UiButton>
              </div>
            </td>
          </tr>
        </template>
      </UiDataTable>
    </template>

    <UiConfirmDialog
      :open="deleteDialogOpen"
      title="Delete this order?"
      :description="deleteDescription"
      :loading="deleteId !== null"
      confirm-label="Delete"
      @close="closeDeleteDialog"
      @confirm="executeDelete"
    />
  </div>
</template>

<script setup lang="ts">
import type { FetchError } from 'ofetch'
import { watchDebounced } from '@vueuse/core'
import type { DataTablePagination } from '~/components/ui/DataTable.vue'
import type { OrderRow } from '~/stores/orders'

definePageMeta({
  layout: 'default',
  middleware: 'auth'
})

useHead({ title: 'Orders' })

type SortColumn = 'order_code' | 'customer_name' | 'item_name' | 'price_cents' | 'status' | 'created_at'

const selectClass =
  'w-full rounded-lg border border-emerald-200/80 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500/60 focus:outline-none focus:ring-2 focus:ring-emerald-500/35'

const store = useOrdersStore()
const { apiFetch } = useApi()
const toast = useIziToast()
const { formatCents } = useMoney()

const searchInput = ref('')
const statusUpdatingId = ref<number | null>(null)

const tablePagination = computed<DataTablePagination | null>(() => {
  const p = store.pagination
  if (!p) { return null }
  return {
    currentPage: store.page,
    lastPage: p.last_page,
    from: p.from,
    to: p.to,
    total: p.total
  }
})
const deleteId = ref<number | null>(null)
const deleteDialogOpen = ref(false)
const deleteTarget = ref<OrderRow | null>(null)

const deleteDescription = computed(() => {
  const t = deleteTarget.value
  if (!t) { return '' }
  const refLabel = t.order_code ? `#${t.order_code}` : `#${t.id}`
  return `Order ${refLabel} — ${t.customer_name}. This cannot be undone.`
})

watchDebounced(
  searchInput,
  (v) => {
    store.searchQ = v
    store.page = 1
    store.fetchOrders()
  },
  { debounce: 350 }
)

function onFilterChange () {
  store.page = 1
  store.fetchOrders()
}

function formatOrderRef (row: OrderRow) {
  if (row.order_code) {
    return `#${row.order_code}`
  }
  return `#${row.id}`
}

function orderStatusLabel (status: string): string {
  const map: Record<string, string> = {
    pending: 'Pending',
    paid: 'Paid',
    cancelled: 'Cancelled'
  }
  return map[status] ?? status
}

function orderStatusOptions (current: string): { value: string; label: string }[] {
  if (current === 'cancelled') {
    return [
      { value: 'cancelled', label: 'Cancelled' },
      { value: 'pending', label: 'Pending' },
      { value: 'paid', label: 'Paid' }
    ]
  }
  if (current === 'paid') {
    return [
      { value: 'paid', label: 'Paid' },
      { value: 'cancelled', label: 'Cancelled' }
    ]
  }
  return [
    { value: 'pending', label: 'Pending' },
    { value: 'paid', label: 'Paid' },
    { value: 'cancelled', label: 'Cancelled' }
  ]
}

function orderStatusTone (status: string): 'amber' | 'emerald' | 'slate' {
  if (status === 'paid') { return 'emerald' }
  if (status === 'cancelled') { return 'slate' }
  return 'amber'
}

function defaultDirection (col: SortColumn): 'asc' | 'desc' {
  if (col === 'order_code' || col === 'customer_name' || col === 'item_name') {
    return 'asc'
  }
  return 'desc'
}

function toggleSort (col: SortColumn) {
  if (store.sort === col) {
    store.direction = store.direction === 'asc' ? 'desc' : 'asc'
  } else {
    store.sort = col
    store.direction = defaultDirection(col)
  }
  store.page = 1
  store.fetchOrders()
}

async function updateOrderStatus (row: OrderRow, next: string) {
  if (next === row.status) { return }
  statusUpdatingId.value = row.id
  try {
    await apiFetch(`/api/orders/${row.id}`, {
      method: 'PUT',
      body: {
        customer_name: row.customer_name,
        item_name: row.item_name,
        price_cents: row.price_cents,
        status: next
      }
    })
    await store.fetchOrders()
    toast.success(
      `Order ${formatOrderRef(row)} is now ${orderStatusLabel(next)}.`,
      'Status updated'
    )
  } catch (e: unknown) {
    const fe = e as FetchError<{ message?: string }>
    toast.error(fe.data?.message || fe.message || 'Could not update status.')
  } finally {
    statusUpdatingId.value = null
  }
}

function formatDate (iso: string | null) {
  if (!iso) { return '—' }
  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(new Date(iso))
}

function prevPage () {
  if (store.page > 1) {
    store.page -= 1
    store.fetchOrders()
  }
}

function nextPage () {
  if (store.pagination && store.page < store.pagination.last_page) {
    store.page += 1
    store.fetchOrders()
  }
}

function goToPage (p: number) {
  if (p === store.page || p < 1 || !store.pagination) { return }
  if (p > store.pagination.last_page) { return }
  store.page = p
  store.fetchOrders()
}

function openDeleteDialog (row: OrderRow) {
  deleteTarget.value = row
  deleteDialogOpen.value = true
}

function closeDeleteDialog () {
  if (deleteId.value !== null) { return }
  deleteDialogOpen.value = false
  deleteTarget.value = null
}

async function executeDelete () {
  const row = deleteTarget.value
  if (!row) { return }
  deleteId.value = row.id
  try {
    await apiFetch(`/api/orders/${row.id}`, { method: 'DELETE' })
    deleteDialogOpen.value = false
    deleteTarget.value = null
    await store.fetchOrders()
  } catch (e: unknown) {
    const fe = e as FetchError<{ message?: string }>
    alert(fe.data?.message || fe.message || 'Could not delete order.')
  } finally {
    deleteId.value = null
  }
}

onMounted(() => {
  store.fetchOrders()
})
</script>
