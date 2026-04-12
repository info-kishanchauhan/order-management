<template>
  <div class="mx-auto max-w-md px-4">
    <div class="mb-8 text-center">
      <h1 class="text-2xl font-bold text-slate-900">
        Welcome back
      </h1>
      <p class="mt-2 text-slate-600">
        Sign in to manage your orders
      </p>
    </div>
    <div class="rounded-2xl border border-emerald-200/70 bg-white p-8 shadow-lg shadow-emerald-900/5">
      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <UiLabel for-id="login-email">Email</UiLabel>
          <UiInputText
            id="login-email"
            v-model="email"
            type="email"
            placeholder="you@example.com"
            :error="!!errors.email"
          />
          <p v-if="errors.email" class="mt-1 text-xs text-red-600">
            {{ errors.email }}
          </p>
        </div>
        <div>
          <UiLabel for-id="login-password">Password</UiLabel>
          <UiInputText
            id="login-password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            :error="!!errors.password"
          />
          <p v-if="errors.password" class="mt-1 text-xs text-red-600">
            {{ errors.password }}
          </p>
        </div>
        <p v-if="formError" class="text-sm text-red-600">
          {{ formError }}
        </p>
        <UiButton
          type="submit"
          class="w-full"
          size="lg"
          :loading="loading"
        >
          Sign in
        </UiButton>
      </form>
      <p class="mt-6 text-center text-sm text-slate-600">
        No account?
        <NuxtLink to="/register" class="font-medium text-emerald-700 hover:text-emerald-800">
          Register
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { FetchError } from 'ofetch'
import { firstMessagePerField, loginSchema, validateWith } from '~/validation'

definePageMeta({
  layout: 'default',
  middleware: 'guest'
})

useHead({ title: 'Sign in' })

const email = ref('')
const password = ref('')
const loading = ref(false)
const formError = ref('')
const errors = ref<Record<string, string>>({})

const auth = useAuthStore()
const { apiFetch } = useApi()

async function submit () {
  formError.value = ''
  errors.value = {}

  const checked = validateWith(loginSchema, {
    email: email.value,
    password: password.value
  })
  if (!checked.ok) {
    errors.value = firstMessagePerField(checked.errors)
    formError.value = checked.message
    return
  }

  loading.value = true
  try {
    const res = await apiFetch<{
      status: number
      message: string
      data: { token: string, user: { id: number, name: string, email: string } }
    }>('/api/login', {
      method: 'POST',
      body: checked.value
    })
    auth.setSession(res.data.token, res.data.user)
    await navigateTo('/orders')
  } catch (e: unknown) {
    const fe = e as FetchError<{ message?: string, errors?: Record<string, string[]> }>
    formError.value = fe.data?.message || 'Login failed'
    if (fe.data?.errors) {
      for (const [k, v] of Object.entries(fe.data.errors)) {
        errors.value[k] = v[0] || ''
      }
    }
  } finally {
    loading.value = false
  }
}
</script>
