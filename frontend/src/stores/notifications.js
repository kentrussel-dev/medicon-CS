import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notifications', () => {
  const toasts = ref([])

  const addToast = (message, type = 'info', duration = 4000) => {
    const id = Date.now() + Math.random().toString(36).substring(2, 9)
    toasts.value.push({ id, message, type })

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }
  }

  const removeToast = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  const success = (msg, duration = 4000) => addToast(msg, 'success', duration)
  const error = (msg, duration = 5000) => addToast(msg, 'error', duration)
  const warning = (msg, duration = 4500) => addToast(msg, 'warning', duration)
  const info = (msg, duration = 4000) => addToast(msg, 'info', duration)

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
  }
})
