<template>
  <button
    :type="type"
    :class="[
      base,
      variants[variant],
      sizes[size],
      loading ? 'opacity-70 cursor-not-allowed' : ''
    ]"
    :disabled="loading || disabled"
    @click="$emit('click', $event)"
  >
    <span v-if="loading" class="mr-2 inline-block size-4 animate-spin rounded-full border-2 border-white/40 border-t-white" aria-hidden="true" />
    <slot />
  </button>
</template>

<script setup>
defineEmits(['click'])

defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary'
  },
  size: {
    type: String,
    default: 'md'
  },
  loading: Boolean,
  disabled: Boolean
})

const base =
  'inline-flex cursor-pointer items-center justify-center rounded-lg font-medium transition shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/45 focus-visible:ring-offset-2 focus-visible:ring-offset-white'

const variants = {
  primary:
    'bg-gradient-to-r from-emerald-500 to-green-600 text-white hover:from-emerald-600 hover:to-green-700 border border-emerald-600/20',
  secondary: 'bg-slate-100 text-slate-800 hover:bg-slate-200 border border-slate-200/80',
  success: 'bg-emerald-600 text-white hover:bg-emerald-700 border border-emerald-700/30',
  danger: 'bg-red-600 text-white hover:bg-red-700 border border-red-700/30',
  outline:
    'border-2 border-emerald-600/35 bg-white text-emerald-800 hover:bg-emerald-50/80 hover:border-emerald-600/50 focus-visible:ring-emerald-500/40 shadow-none'
}

const sizes = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-5 py-3 text-base'
}
</script>
