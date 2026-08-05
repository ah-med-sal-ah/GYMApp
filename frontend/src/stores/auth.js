import { defineStore } from 'pinia'
import { gymHttp, GYM_TOKEN_KEY } from '@/lib/http'

export const useAuthStore = defineStore('gymAuth', {
  state: () => ({
    gym: null,
    token: localStorage.getItem(GYM_TOKEN_KEY) || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setSession(gym, token) {
      this.gym = gym
      this.token = token
      localStorage.setItem(GYM_TOKEN_KEY, token)
    },

    clearSession() {
      this.gym = null
      this.token = null
      localStorage.removeItem(GYM_TOKEN_KEY)
    },

    async register(payload) {
      const { data } = await gymHttp.post('/auth/register', payload)
      this.setSession(data.data.gym, data.data.token)
      return data
    },

    async login(payload) {
      const { data } = await gymHttp.post('/auth/login', payload)
      this.setSession(data.data.gym, data.data.token)
      return data
    },

    async logout() {
      try {
        await gymHttp.post('/auth/logout')
      } finally {
        this.clearSession()
      }
    },

    async fetchMe() {
      const { data } = await gymHttp.get('/auth/me')
      this.gym = data.data
      return this.gym
    },

    async changePassword(payload) {
      const { data } = await gymHttp.put('/auth/change-password', payload)
      return data
    },

    async updateProfile(payload) {
      const { data } = await gymHttp.put('/profile', payload)
      this.gym = data.data
      return data
    },
  },
})
