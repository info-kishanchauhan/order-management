<template>
  <div class="min-h-screen bg-gradient-to-b from-emerald-50/40 via-white to-slate-50 text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
      <div class="absolute -left-32 top-0 h-80 w-80 rounded-full bg-emerald-400/15 blur-3xl" />
      <div class="absolute -right-32 bottom-0 h-80 w-80 rounded-full bg-green-400/10 blur-3xl" />
    </div>
    <header
      class="site-header relative z-10 border-b border-emerald-200/60 bg-white/90 shadow-sm backdrop-blur-md"
    >
      <NuxtLoadingIndicator
        :height="4"
        color="repeating-linear-gradient(90deg, #34d399 0%, #10b981 45%, #059669 100%)"
      />
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <NuxtLink
          to="/orders"
          class="text-lg font-semibold tracking-tight text-emerald-900"
        >
          Order Hub
        </NuxtLink>
        <nav class="flex items-center gap-4 text-sm">
          <template v-if="auth.token">
            <span class="hidden text-slate-600 sm:inline">{{ auth.user?.email }}</span>
            <UiButton
              variant="outline"
              size="sm"
              @click="logout"
            >
              Log out
            </UiButton>
          </template>
          <template v-else>
            <NuxtLink
              to="/login"
              class="text-slate-600 hover:text-emerald-800"
            >
              Log in
            </NuxtLink>
            <NuxtLink to="/register">
              <UiButton size="sm">
                Register
              </UiButton>
            </NuxtLink>
          </template>
        </nav>
      </div>
    </header>
    <main class="relative z-10 mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
const auth = useAuthStore()
const router = useRouter()

async function logout () {
  const { apiFetch } = useApi()
  try {
    await apiFetch('/api/logout', { method: 'POST' })
  } catch {
    //
  }
  auth.clearSession()
  await router.push('/login')
}
</script>
