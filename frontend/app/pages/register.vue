<template>
  <div class="mx-auto max-w-md">
    <div class="mb-8 text-center">
      <h1 class="text-2xl font-bold text-slate-900">
        Create account
      </h1>
      <p class="mt-2 text-slate-600">
        Register to track your orders
      </p>
    </div>
    <div class="rounded-2xl border border-emerald-200/70 bg-white p-8 shadow-lg shadow-emerald-900/5">
      <form class="space-y-5" @submit.prevent="submit">
        <div>
          <UiLabel for-id="reg-name">Name</UiLabel>
          <UiInputText
            id="reg-name"
            v-model="name"
            placeholder="Your name"
            :error="!!fieldErrors.name"
          />
          <p v-if="fieldErrors.name" class="mt-1 text-xs text-red-600">
            {{ fieldErrors.name[0] }}
          </p>
        </div>
        <div>
          <UiLabel for-id="reg-email">Email</UiLabel>
          <UiInputText
            id="reg-email"
            v-model="email"
            type="email"
            placeholder="you@example.com"
            :error="!!fieldErrors.email"
          />
          <p v-if="fieldErrors.email" class="mt-1 text-xs text-red-600">
            {{ fieldErrors.email[0] }}
          </p>
        </div>
        <div>
          <UiLabel for-id="reg-password">Password</UiLabel>
          <UiInputText
            id="reg-password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            :error="!!fieldErrors.password"
          />
          <p v-if="fieldErrors.password" class="mt-1 text-xs text-red-600">
            {{ fieldErrors.password[0] }}
          </p>
        </div>
        <div>
          <UiLabel for-id="reg-password2">Confirm password</UiLabel>
          <UiInputText
            id="reg-password2"
            v-model="password_confirmation"
            type="password"
            placeholder="••••••••"
            :error="!!fieldErrors.password_confirmation"
          />
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
          Register
        </UiButton>
      </form>
      <p class="mt-6 text-center text-sm text-slate-600">
        Already have an account?
        <NuxtLink to="/login" class="font-medium text-emerald-700 hover:text-emerald-800">
          Sign in
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { FetchError } from 'ofetch'
import { registerSchema, validateWith } from '~/validation'

definePageMeta({
  layout: 'default',
  middleware: 'guest'
})

useHead({ title: 'Register' })

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const loading = ref(false)
const formError = ref('')
const fieldErrors = ref<Record<string, string[]>>({})

const auth = useAuthStore()
const { apiFetch } = useApi()

async function submit () {
  formError.value = ''
  fieldErrors.value = {}

  const checked = validateWith(registerSchema, {
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: password_confirmation.value
  })
  if (!checked.ok) {
    fieldErrors.value = checked.errors
    formError.value = checked.message
    return
  }

  loading.value = true
  try {
    const res = await apiFetch<{
      status: number
      message: string
      data: { token: string, user: { id: number, name: string, email: string } }
    }>('/api/register', {
      method: 'POST',
      body: checked.value
    })
    auth.setSession(res.data.token, res.data.user)
    await navigateTo('/orders')
  } catch (e: unknown) {
    const fe = e as FetchError<{ message?: string, errors?: Record<string, string[]> }>
    formError.value = fe.data?.message || 'Registration failed'
    if (fe.data?.errors) {
      fieldErrors.value = fe.data.errors
    }
  } finally {
    loading.value = false
  }
}
</script>
