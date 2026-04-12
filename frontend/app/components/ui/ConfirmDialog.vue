<template>
  <TransitionRoot appear :show="open" as="template">
    <Dialog class="relative z-[200]" @close="onDialogClose">
      <TransitionChild
        as="template"
        enter="duration-200 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-150 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <DialogOverlay class="fixed inset-0 bg-slate-900/40 backdrop-blur-[1px]" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto p-4">
        <div class="flex min-h-full items-center justify-center">
          <TransitionChild
            as="template"
            enter="duration-200 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-150 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md rounded-2xl border border-emerald-200/80 bg-white p-6 shadow-xl shadow-emerald-900/10"
            >
              <DialogTitle class="text-lg font-semibold text-slate-900">
                {{ title }}
              </DialogTitle>
              <p v-if="description" class="mt-2 text-sm text-slate-600">
                {{ description }}
              </p>
              <div class="mt-6 flex flex-wrap justify-end gap-3">
                <UiButton
                  variant="outline"
                  type="button"
                  :disabled="loading"
                  @click="emit('close')"
                >
                  {{ cancelLabel }}
                </UiButton>
                <UiButton
                  type="button"
                  class="!border-red-600/30 !bg-red-600 !text-white hover:!bg-red-700"
                  :loading="loading"
                  @click="emit('confirm')"
                >
                  {{ confirmLabel }}
                </UiButton>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import {
  Dialog,
  DialogOverlay,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot
} from '@headlessui/vue'

const props = withDefaults(
  defineProps<{
    open: boolean
    title: string
    description?: string
    confirmLabel?: string
    cancelLabel?: string
    loading?: boolean
  }>(),
  {
    description: '',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
    loading: false
  }
)

const emit = defineEmits<{
  close: []
  confirm: []
}>()

function onDialogClose () {
  if (props.loading) { return }
  emit('close')
}
</script>
