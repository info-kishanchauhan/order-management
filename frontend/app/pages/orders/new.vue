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
        New order
      </h1>
      <p class="mt-1 text-slate-600">
        Enter customer, item, and price in cents. You will return to the list after a successful create.
      </p>
    </div>

    <CreateOrderForm @saved="onSaved" @cancel="onCancel" />
  </div>
</template>

<script setup lang="ts">
import CreateOrderForm from '~/components/orders/CreateOrderForm.vue'

definePageMeta({
  layout: 'default',
  middleware: 'auth'
})

useHead({ title: 'New order' })

async function onSaved () {
  await navigateTo('/orders')
}

function onCancel () {
  navigateTo('/orders')
}
</script>
