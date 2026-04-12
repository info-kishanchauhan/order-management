<template>
  <input
    :id="id"
    v-model="model"
    :type="type"
    :placeholder="placeholder"
    :disabled="disabled"
    :class="[
      'w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400',
      'border-emerald-200/70 shadow-sm',
      'focus:border-emerald-500/60 focus:outline-none focus:ring-2 focus:ring-emerald-500/35',
      error ? 'border-red-400 focus:ring-red-400/40' : '',
      disabled ? 'cursor-not-allowed bg-slate-50 opacity-70' : ''
    ]"
    autocomplete="off"
  >
</template>

<script setup lang="ts">
const model = defineModel<string>({ default: '' })

defineProps<{
  id?: string
  type?: string
  placeholder?: string
  disabled?: boolean
  error?: boolean
}>()
</script>
