<template>
  <div class="rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-white to-emerald-50/50 p-6 shadow-lg shadow-emerald-900/5">
    <h2 class="mb-4 text-lg font-semibold text-emerald-950">
      {{ formHeading }}
    </h2>

    <div
      v-if="isEdit && loadPending"
      class="py-8 text-center text-sm text-slate-600"
    >
      Loading order…
    </div>

    <div
      v-else-if="isEdit && loadError"
      class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"
    >
      {{ loadError }}
      <UiButton class="ml-3" size="sm" type="button" @click="loadOrder">
        Retry
      </UiButton>
    </div>

    <form
      v-else
      class="space-y-4"
      @submit.prevent="submit"
    >
      <div>
        <UiLabel for-id="co-customer">Customer name</UiLabel>
        <UiInputText
          id="co-customer"
          v-model="form.customer_name"
          placeholder="Jane Doe"
          :error="!!fieldErrors.customer_name"
        />
        <p v-if="fieldErrors.customer_name" class="mt-1 text-xs text-red-600">
          {{ fieldErrors.customer_name[0] }}
        </p>
      </div>
      <div>
        <UiLabel for-id="co-item">Item name</UiLabel>
        <UiInputText
          id="co-item"
          v-model="form.item_name"
          placeholder="Wireless keyboard"
          :error="!!fieldErrors.item_name"
        />
        <p v-if="fieldErrors.item_name" class="mt-1 text-xs text-red-600">
          {{ fieldErrors.item_name[0] }}
        </p>
      </div>
      <div>
        <UiLabel for-id="co-price">Price (cents)</UiLabel>
        <UiInputText
          id="co-price"
          v-model="priceInput"
          type="number"
          min="0"
          placeholder="1999"
          :error="!!fieldErrors.price_cents"
        />
        <p v-if="fieldErrors.price_cents" class="mt-1 text-xs text-red-600">
          {{ fieldErrors.price_cents[0] }}
        </p>
        <p class="mt-1 text-xs text-slate-500">
          Stored as integer cents (e.g. 1999 = $19.99)
        </p>
      </div>

      <div v-if="isEdit">
        <UiLabel for-id="co-status">Status</UiLabel>
        <select
          id="co-status"
          v-model="form.status"
          class="w-full rounded-lg border border-emerald-200/80 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-emerald-500/60 focus:outline-none focus:ring-2 focus:ring-emerald-500/35"
        >
          <option
            v-for="opt in statusOptions"
            :key="opt"
            :value="opt"
          >
            {{ opt }}
          </option>
        </select>
        <p v-if="fieldErrors.status?.[0]" class="mt-1 text-xs text-red-600">
          {{ fieldErrors.status[0] }}
        </p>
      </div>

      <p v-if="formError" class="text-sm text-red-600">
        {{ formError }}
      </p>
      <div class="flex flex-wrap gap-3">
        <UiButton type="submit" :loading="submitting">
          {{ isEdit ? 'Save changes' : 'Create' }}
        </UiButton>
        <UiButton variant="outline" type="button" @click="$emit('cancel')">
          Cancel
        </UiButton>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { FetchError } from 'ofetch'
import { createOrderSchema, updateOrderSchema, validateWith } from '~/validation'
import type { OrderRow } from '~/stores/orders'

const props = defineProps<{
  /** When set, form loads this order and submits PUT /api/orders/:id */
  orderId?: number
}>()

const emit = defineEmits<{
  saved: []
  cancel: []
}>()

const { apiFetch } = useApi()
const toast = useIziToast()

const isEdit = computed(() => typeof props.orderId === 'number' && props.orderId > 0 && Number.isFinite(props.orderId))

const form = reactive({
  customer_name: '',
  item_name: '',
  price_cents: 0 as number,
  status: 'pending' as 'pending' | 'paid' | 'cancelled'
})

const priceInput = computed({
  get: () => (form.price_cents === 0 ? '' : String(form.price_cents)),
  set: (v: string) => {
    const n = parseInt(v, 10)
    form.price_cents = Number.isNaN(n) ? 0 : Math.max(0, n)
  }
})

const submitting = ref(false)
const formError = ref('')
const fieldErrors = ref<Record<string, string[]>>({})
const loadPending = ref(false)
const loadError = ref('')
const loadedOrderCode = ref<string | null>(null)

const formHeading = computed(() => {
  if (!isEdit.value) { return 'Create order' }
  if (loadedOrderCode.value) {
    return `Edit order #${loadedOrderCode.value}`
  }
  return 'Edit order'
})

const statusOptions = computed(() => {
  if (!isEdit.value) { return [] as const }
  const s = form.status
  if (s === 'cancelled') { return ['cancelled', 'pending', 'paid'] as const }
  if (s === 'paid') { return ['paid', 'cancelled'] as const }
  return ['pending', 'paid', 'cancelled'] as const
})

interface OrderShowResponse {
  status: number
  message: string
  data: OrderRow
}

async function loadOrder () {
  if (!isEdit.value) { return }
  loadError.value = ''
  loadPending.value = true
  try {
    const res = await apiFetch<OrderShowResponse>(`/api/orders/${props.orderId}`)
    const row = res.data
    loadedOrderCode.value = row.order_code
    form.customer_name = row.customer_name
    form.item_name = row.item_name
    form.price_cents = row.price_cents
    form.status = row.status as typeof form.status
    formError.value = ''
    fieldErrors.value = {}
  } catch (err: unknown) {
    const fe = err as FetchError<{ message?: string }>
    loadError.value = fe.data?.message || fe.message || 'Could not load order.'
  } finally {
    loadPending.value = false
  }
}

watch(
  () => props.orderId,
  () => {
    if (isEdit.value) {
      loadOrder()
    }
  },
  { immediate: true }
)

async function submit () {
  formError.value = ''
  fieldErrors.value = {}

  if (isEdit.value) {
    const payload = {
      customer_name: form.customer_name.trim(),
      item_name: form.item_name.trim(),
      price_cents: form.price_cents,
      status: form.status
    }
    const checked = validateWith(updateOrderSchema, payload)
    if (!checked.ok) {
      fieldErrors.value = checked.errors
      formError.value = checked.message
      return
    }

    submitting.value = true
    try {
      await apiFetch(`/api/orders/${props.orderId}`, {
        method: 'PUT',
        body: checked.value
      })
      toast.success('Your order was updated successfully.', 'Order updated')
      emit('saved')
    } catch (err: unknown) {
      const fe = err as FetchError<{ errors?: Record<string, string[]>, message?: string }>
      if (fe.data?.errors) {
        fieldErrors.value = fe.data.errors
      }
      formError.value = fe.data?.message || fe.message || 'Could not update order.'
    } finally {
      submitting.value = false
    }
    return
  }

  const payload = {
    customer_name: form.customer_name.trim(),
    item_name: form.item_name.trim(),
    price_cents: form.price_cents
  }
  const checked = validateWith(createOrderSchema, payload)
  if (!checked.ok) {
    fieldErrors.value = checked.errors
    formError.value = checked.message
    return
  }

  submitting.value = true
  try {
    await apiFetch('/api/orders', {
      method: 'POST',
      body: checked.value
    })
    form.customer_name = ''
    form.item_name = ''
    form.price_cents = 0
    toast.success('Your new order was created successfully.', 'Order created')
    emit('saved')
  } catch (err: unknown) {
    const fe = err as FetchError<{ errors?: Record<string, string[]>, message?: string }>
    if (fe.data?.errors) {
      fieldErrors.value = fe.data.errors
    }
    formError.value = fe.data?.message || fe.message || 'Could not create order.'
  } finally {
    submitting.value = false
  }
}
</script>
