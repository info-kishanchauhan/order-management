<script setup lang="ts">
/**
 * Mirrors a boolean from template-only scope (e.g. Headless UI v-slot) into script state.
 */
const props = defineProps<{ modelValue: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()

watch(
  () => props.modelValue,
  v => emit('update:modelValue', v),
  { immediate: true }
)
</script>

<template>
  <span hidden aria-hidden="true" />
</template>
