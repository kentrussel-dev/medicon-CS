import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('medicon_user') || 'null'))
  const token = ref(localStorage.getItem('medicon_auth_token') || null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const role = computed(() => user.value?.role || null)
  const isPatient = computed(() => role.value === 'patient')
  const isDoctor = computed(() => role.value === 'doctor')
  const isAdmin = computed(() => role.value === 'admin')

  const setAuth = (newUser, newToken) => {
    user.value = newUser
    token.value = newToken
    if (newToken) {
      localStorage.setItem('medicon_auth_token', newToken)
    }
    if (newUser) {
      localStorage.setItem('medicon_user', JSON.stringify(newUser))
    }
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('medicon_auth_token')
    localStorage.removeItem('medicon_user')
  }

  const login = async (credentials) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/auth/login', credentials)
      const { user: userData, token: authToken } = response.data
      setAuth(userData, authToken)
      notifications.success(`Welcome back, ${userData.name}!`)
      return userData
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const register = async (formData) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/auth/register', formData)
      const { user: userData, token: authToken } = response.data
      setAuth(userData, authToken)
      notifications.success('Registration successful! Welcome to Medicon.')
      return userData
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      // Ignore API logout failures during cleanup
    } finally {
      clearAuth()
      useNotificationStore().info('You have been signed out.')
      window.location.href = '/login'
    }
  }

  const fetchUser = async () => {
    if (!token.value) return null
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
      localStorage.setItem('medicon_user', JSON.stringify(user.value))
      return user.value
    } catch (err) {
      clearAuth()
      return null
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.put('/profile', profileData)
      user.value = response.data.user
      localStorage.setItem('medicon_user', JSON.stringify(user.value))
      notifications.success('Profile updated successfully.')
      return user.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const updatePassword = async (passwords) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      await api.put('/profile/password', passwords)
      notifications.success('Password changed successfully.')
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    role,
    isPatient,
    isDoctor,
    isAdmin,
    login,
    register,
    logout,
    fetchUser,
    updateProfile,
    updatePassword,
  }
})
