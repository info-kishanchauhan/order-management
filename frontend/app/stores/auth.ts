import { defineStore } from 'pinia'

const STORAGE_KEY = 'oms_auth_token'

export interface AuthUser {
  id: number
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<AuthUser | null>(null)
  const initialized = ref(false)

  function loadFromStorage () {
    if (!import.meta.client) { return }
    const t = localStorage.getItem(STORAGE_KEY)
    token.value = t
  }

  function persistToken (t: string | null) {
    token.value = t
    if (!import.meta.client) { return }
    if (t) {
      localStorage.setItem(STORAGE_KEY, t)
    } else {
      localStorage.removeItem(STORAGE_KEY)
    }
  }

  function setSession (t: string, u: AuthUser) {
    persistToken(t)
    user.value = u
  }

  function clearSession () {
    persistToken(null)
    user.value = null
  }

  async function initialize () {
    if (initialized.value) { return }
    loadFromStorage()
    initialized.value = true
  }

  return {
    token,
    user,
    initialized,
    initialize,
    setSession,
    clearSession,
    persistToken
  }
})
