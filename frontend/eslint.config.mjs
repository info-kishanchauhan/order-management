import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import globals from 'globals'
import tseslint from 'typescript-eslint'
import vueParser from 'vue-eslint-parser'

/** Nuxt + Vue auto-imports (no explicit import in SFCs / app code) */
const nuxtVueGlobals = {
  // Vue
  ref: 'readonly',
  computed: 'readonly',
  reactive: 'readonly',
  watch: 'readonly',
  watchEffect: 'readonly',
  onMounted: 'readonly',
  onUnmounted: 'readonly',
  nextTick: 'readonly',
  // SFC macros
  defineProps: 'readonly',
  defineEmits: 'readonly',
  defineExpose: 'readonly',
  defineModel: 'readonly',
  defineSlots: 'readonly',
  withDefaults: 'readonly',
  // Nuxt
  useHead: 'readonly',
  useSeoMeta: 'readonly',
  useRouter: 'readonly',
  useRoute: 'readonly',
  navigateTo: 'readonly',
  useRuntimeConfig: 'readonly',
  useNuxtApp: 'readonly',
  defineNuxtPlugin: 'readonly',
  definePageMeta: 'readonly',
  defineNuxtRouteMiddleware: 'readonly',
  // Pinia (auto-imported stores)
  useAuthStore: 'readonly',
  useOrdersStore: 'readonly',
  // App composables
  useApi: 'readonly',
  useMoney: 'readonly'
}

export default tseslint.config(
  { ignores: ['**/.nuxt/**', '**/node_modules/**', '**/dist/**'] },
  js.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
        ...nuxtVueGlobals
      }
    }
  },
  {
    files: ['**/*.vue'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tseslint.parser,
        extraFileExtensions: ['.vue']
      }
    },
    rules: {
      'vue/multi-word-component-names': 'off',
      'vue/max-attributes-per-line': 'off',
      'vue/singleline-html-element-content-newline': 'off',
      'vue/require-explicit-emits': 'off'
    }
  }
)
