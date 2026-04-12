<template>
  <div class="space-y-8">
    <div>
      <NuxtLink
        to="/orders"
        class="text-sm font-medium text-emerald-800 hover:text-emerald-950 hover:underline"
      >
        ← Back to orders
      </NuxtLink>
      <h1 class="mt-4 text-2xl font-bold text-slate-900">
        Edit order
      </h1>
      <p class="mt-1 text-slate-600">
        Update customer, item, price, and status. Invalid status changes are rejected by the server.
      </p>
    </div>

    <CreateOrderForm
      v-if="numericId !== null"
      :order-id="numericId"
      @saved="onSaved"
      @cancel="onCancel"
    />
    <div
      v-else
      class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-900"
    >
      Invalid order link.
      <NuxtLink to="/orders" class="ml-2 font-medium underline">
        Back to orders
      </NuxtLink>
    </div>
  </div>
</template>

<script setup lang="ts">
import CreateOrderForm from '~/components/orders/CreateOrderForm.vue'

definePageMeta({
  layout: 'default',
  middleware: 'auth'
})

const route = useRoute()

const numericId = computed(() => {
  const n = Number(route.params.id)
  return Number.isFinite(n) && n > 0 ? n : null
})

useHead({
  title: computed(() =>
    numericId.value !== null ? `Edit order #${numericId.value}` : 'Edit order'
  )
})

async function onSaved () {
  await navigateTo('/orders')
}

function onCancel () {
  navigateTo('/orders')
}
</script>
