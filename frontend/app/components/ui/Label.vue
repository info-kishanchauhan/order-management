<template>
  <label
    :for="forId"
    class="mb-1.5 block text-sm font-medium text-slate-700"
  >
    <slot />
  </label>
</template>

<script setup lang="ts">
defineProps<{
  forId?: string
}>()
</script>
