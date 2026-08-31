import axios from 'axios'
import { useNotificationStore } from '@/stores/notifications'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Request Interceptor: Attach Auth Bearer Token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('medicon_auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response Interceptor: Unified Error Handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const notificationStore = useNotificationStore()

    if (error.response) {
      const status = error.response.status
      const data = error.response.data

      if (status === 401) {
        localStorage.removeItem('medicon_auth_token')
        localStorage.removeItem('medicon_user')
        if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
          notificationStore.error('Session expired. Please log in again.')
          window.location.href = '/login'
        }
      } else if (status === 403) {
        notificationStore.error(data.message || 'Access forbidden: Insufficient permissions.')
      } else if (status === 422) {
        const errorMsg = data.message || 'Validation failed. Please check the inputs.'
        notificationStore.warning(errorMsg)
      } else if (status === 429) {
        notificationStore.warning('Too many requests. Please slow down and try again shortly.')
      } else if (status >= 500) {
        notificationStore.error('A server error occurred. Please try again later.')
      }
    } else if (error.request) {
      notificationStore.error('Network error. Unable to connect to Medicon server.')
    }

    return Promise.reject(error)
  }
)

export default api
