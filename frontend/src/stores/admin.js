import { defineStore } from 'pinia'
import { adminHttp, ADMIN_TOKEN_KEY } from '@/lib/http'

export const useAdminAuthStore = defineStore('adminAuth', {
  state: () => ({
    admin: null,
    token: localStorage.getItem(ADMIN_TOKEN_KEY) || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setSession(admin, token) {
      this.admin = admin
      this.token = token
      localStorage.setItem(ADMIN_TOKEN_KEY, token)
    },

    clearSession() {
      this.admin = null
      this.token = null
      localStorage.removeItem(ADMIN_TOKEN_KEY)
    },

    async login(payload) {
      const { data } = await adminHttp.post('/admin/login', payload)
      this.setSession(data.data.admin, data.data.token)
      return data
    },

    logout() {
      this.clearSession()
    },

    async fetchProfile() {
      const { data } = await adminHttp.get('/admin/profile')
      this.admin = data.data
      return this.admin
    },

    async changePassword(payload) {
      const { data } = await adminHttp.put('/admin/profile/password', payload)
      return data
    },
  },
})
