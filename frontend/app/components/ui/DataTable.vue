<template>
  <div class="space-y-4">
    <div class="rounded-xl border border-emerald-200/70 bg-white shadow-sm">
      <table class="min-w-full divide-y divide-emerald-100 text-left text-sm">
        <thead class="bg-emerald-50/80 text-xs uppercase tracking-wide text-emerald-900/80">
          <slot name="head" />
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-800">
          <slot name="body" />
        </tbody>
      </table>
    </div>

    <div
      v-if="showPagination && pagination && pagination.lastPage >= 1"
      class="flex w-full flex-wrap items-center justify-between gap-4 rounded-full border border-emerald-200/70 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm"
    >
      <div class="flex flex-wrap items-center gap-1 sm:gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 font-medium text-slate-700 transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="pagination.currentPage <= 1 || loading"
          @click="emit('paginationPrev')"
        >
          <span class="text-base leading-none" aria-hidden="true">‹</span>
          Previous
        </button>

        <div class="flex flex-wrap items-center justify-center gap-1">
          <template v-for="(item, idx) in pageItems" :key="`p-${idx}-${item}`">
            <span
              v-if="item === 'ellipsis'"
              class="px-1.5 text-slate-400"
            >…</span>
            <button
              v-else
              type="button"
              class="flex h-9 min-w-9 items-center justify-center rounded-full text-sm font-medium transition"
              :class="
                item === pagination.currentPage
                  ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-200/90'
                  : 'text-slate-700 hover:bg-emerald-50'
              "
              :disabled="loading"
              @click="emit('paginationGoToPage', item)"
            >
              {{ item }}
            </button>
          </template>
        </div>

        <button
          type="button"
          class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 font-medium text-slate-700 transition hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40"
          :disabled="pagination.currentPage >= pagination.lastPage || loading"
          @click="emit('paginationNext')"
        >
          Next
          <span class="text-base leading-none" aria-hidden="true">›</span>
        </button>
      </div>

      <p
        v-if="pagination.total > 0 && pagination.from != null && pagination.to != null"
        class="text-right text-sm tabular-nums text-slate-600"
      >
        Showing
        <span class="font-medium text-slate-800">{{ pagination.from.toLocaleString() }}</span>
        –
        <span class="font-medium text-slate-800">{{ pagination.to.toLocaleString() }}</span>
        of
        <span class="font-medium text-slate-800">{{ pagination.total.toLocaleString() }}</span>
        results
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { visiblePages } from '~/utils/pagination'

export interface DataTablePagination {
  currentPage: number
  lastPage: number
  from: number | null
  to: number | null
  total: number
}

const props = withDefaults(
  defineProps<{
    /** When true and `pagination` is set, the pill bar is shown below the table. */
    showPagination?: boolean
    pagination?: DataTablePagination | null
    loading?: boolean
  }>(),
  {
    showPagination: false,
    pagination: null,
    loading: false
  }
)

const emit = defineEmits<{
  paginationPrev: []
  paginationNext: []
  paginationGoToPage: [page: number]
}>()

const pageItems = computed(() => {
  if (!props.pagination) { return [] as Array<number | 'ellipsis'> }
  return visiblePages(props.pagination.currentPage, props.pagination.lastPage)
})
</script>
