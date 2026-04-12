<script setup lang="ts">
const auth = useAuthStore()
await auth.initialize()

if (auth.token) {
  await navigateTo('/orders')
} else {
  await navigateTo('/login')
}
</script>

<template>
  <div class="text-slate-600">
    Redirecting…
  </div>
</template>
