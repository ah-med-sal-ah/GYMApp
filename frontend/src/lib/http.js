import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'

export const GYM_TOKEN_KEY = 'yourgym_gym_token'
export const ADMIN_TOKEN_KEY = 'yourgym_admin_token'

function createHttp({ tokenKey, loginPath }) {
  const instance = axios.create({
    baseURL,
    headers: { Accept: 'application/json' },
  })

  instance.interceptors.request.use((config) => {
    const token = localStorage.getItem(tokenKey)
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  })

  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        localStorage.removeItem(tokenKey)
        if (window.location.pathname !== loginPath) {
          window.location.href = loginPath
        }
      }
      return Promise.reject(error)
    },
  )

  return instance
}

export const gymHttp = createHttp({ tokenKey: GYM_TOKEN_KEY, loginPath: '/login' })
export const adminHttp = createHttp({ tokenKey: ADMIN_TOKEN_KEY, loginPath: '/admin/login' })
