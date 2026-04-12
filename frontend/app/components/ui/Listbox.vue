<template>
  <div class="relative w-full min-w-[9.5rem]">
    <Listbox
      v-slot="{ open }"
      :model-value="modelValue"
      :disabled="disabled"
      @update:model-value="onUpdate"
    >
      <UiSyncBoolean :model-value="open" @update:model-value="setPanelOpen" />

      <div class="relative">
        <ListboxButton
          ref="anchorRef"
          type="button"
          class="inline-flex w-full items-center justify-between gap-2 rounded-md border border-emerald-200/90 bg-white px-3 py-1.5 text-left text-xs font-medium shadow-sm transition hover:border-emerald-400/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 disabled:cursor-not-allowed disabled:opacity-50"
          :class="toneClass"
        >
          <span class="truncate">{{ selectedLabel }}</span>
          <span class="text-slate-500" aria-hidden="true">▾</span>
        </ListboxButton>

        <Teleport to="body">
          <ListboxOptions
            as="ul"
            :class="teleportedPanelClass"
            :style="panelStyle"
          >
            <ListboxOption
              v-for="opt in options"
              :key="optionKey(opt)"
              v-slot="{ active, selected }"
              :value="opt.value"
              as="template"
            >
              <li
                class="cursor-pointer px-3 py-2"
                :class="[
                  active ? 'bg-emerald-50 text-emerald-950' : 'text-slate-800',
                  selected ? 'font-semibold' : 'font-normal'
                ]"
              >
                {{ opt.label }}
              </li>
            </ListboxOption>
          </ListboxOptions>
        </Teleport>
      </div>
    </Listbox>
  </div>
</template>

<script setup lang="ts" generic="T extends string | number = string">
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue'
import { useEventListener } from '@vueuse/core'

export interface UiListboxOption<T extends string | number = string> {
  value: T
  label: string
}

const props = withDefaults(
  defineProps<{
    modelValue: T
    options: readonly UiListboxOption<T>[]
    disabled?: boolean
    /** Visual style for the closed button (ring + text). */
    tone?: 'amber' | 'emerald' | 'slate'
    /** Prefer opening the menu above the button (table rows). */
    openAbove?: boolean
  }>(),
  {
    disabled: false,
    tone: 'amber',
    openAbove: true
  }
)

const emit = defineEmits<{
  'update:modelValue': [value: T]
}>()

const panelOpen = ref(false)
const anchorRef = ref<InstanceType<typeof ListboxButton> | null>(null)
const panelStyle = ref<Record<string, string>>({})

const teleportedPanelClass =
  'max-h-48 min-w-[10rem] overflow-auto rounded-md border border-emerald-200/80 bg-white py-1 text-sm shadow-lg shadow-emerald-900/10 focus:outline-none'

const selectedLabel = computed(() => {
  const found = props.options.find(o => o.value === props.modelValue)
  return found?.label ?? String(props.modelValue)
})

const toneClass = computed(() => {
  switch (props.tone) {
    case 'emerald':
      return 'text-emerald-900 ring-1 ring-emerald-200/80'
    case 'slate':
      return 'text-slate-800 ring-1 ring-slate-300/80'
    case 'amber':
    default:
      return 'text-amber-900 ring-1 ring-amber-200/80'
  }
})

function setPanelOpen (v: boolean) {
  panelOpen.value = v
}

function getAnchorEl (): HTMLElement | null {
  const raw = anchorRef.value as unknown as { $el?: HTMLElement } | HTMLElement | null
  if (!raw) { return null }
  if (typeof raw === 'object' && '$el' in raw && raw.$el) {
    return raw.$el
  }
  return raw as HTMLElement
}

function updatePanelPosition () {
  const el = getAnchorEl()
  if (!el) { return }
  const rect = el.getBoundingClientRect()
  const width = Math.max(rect.width, 160)
  const margin = 4
  const z = '200'

  if (props.openAbove) {
    panelStyle.value = {
      position: 'fixed',
      left: `${rect.left}px`,
      width: `${width}px`,
      top: `${rect.top - margin}px`,
      transform: 'translateY(-100%)',
      zIndex: z
    }
  } else {
    panelStyle.value = {
      position: 'fixed',
      left: `${rect.left}px`,
      width: `${width}px`,
      top: `${rect.bottom + margin}px`,
      transform: 'none',
      zIndex: z
    }
  }
}

watchEffect((onCleanup) => {
  if (!panelOpen.value) {
    panelStyle.value = {}
    return
  }
  nextTick(() => {
    updatePanelPosition()
    requestAnimationFrame(() => updatePanelPosition())
  })
  const stopScroll = useEventListener(window, 'scroll', updatePanelPosition, { capture: true })
  const stopResize = useEventListener(window, 'resize', updatePanelPosition)
  onCleanup(() => {
    stopScroll()
    stopResize()
  })
})

function optionKey (opt: UiListboxOption<T>) {
  return String(opt.value)
}

function onUpdate (next: T) {
  if (next === props.modelValue) { return }
  emit('update:modelValue', next)
}
</script>
