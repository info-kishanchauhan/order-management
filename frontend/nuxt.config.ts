// https://nuxt.com/docs/api/configuration/nuxt-config
// Tailwind v4 + Vite: https://tailwindcss.com/docs/installation/framework-guides/nuxt
import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // Bearer token in localStorage is read client-side; disable SSR for consistent auth + orders UI.
  ssr: false,

  css: ['~/assets/css/main.css', 'izitoast/dist/css/iziToast.min.css'],

  vite: {
    plugins: [tailwindcss()],
    optimizeDeps: {
      include: ['joi']
    }
  },

  modules: [
    '@pinia/nuxt',
    '@vueuse/nuxt',
    'nuxt-icons',
    '@nuxtjs/eslint-module'
  ],

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000'
    }
  }
})
