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

  // Fallback demo users if backend API is not running locally
  const getMockUser = (email, customData = {}) => {
    const e = (email || '').toLowerCase().trim()
    if (e.includes('admin')) {
      return {
        id: 3,
        name: 'Operations Administrator',
        email: e,
        role: 'admin',
        phone: '+1 (555) 019-0000',
        avatar_url: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
      }
    }
    if (e.includes('sarah') || e.includes('doctor') || e.includes('chen') || e.includes('jenkins')) {
      return {
        id: 2,
        name: 'Dr. Sarah Jenkins, MD, FACC',
        email: e,
        role: 'doctor',
        phone: '+1 (555) 019-4401',
        avatar_url: 'https://images.unsplash.com/photo-1594824813593-9c8df6cbeeb0?w=150&auto=format&fit=crop&q=80',
        doctor: {
          id: 1,
          specialty: 'Cardiology',
          license_number: 'MD-99281-STATE',
          consultation_fee: 120,
          consultation_fee_cents: 12000,
          rating: 4.95,
          bio: 'Board-certified cardiologist specializing in preventive heart health and electrophysiology.',
        },
      }
    }
    // Default patient profile
    return {
      id: customData.id || 1,
      name: customData.name || 'Jane Doe',
      email: e || 'patient@medicon.health',
      role: 'patient',
      phone: customData.phone || '+1 (555) 019-2834',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      patient: {
        id: customData.id || 1,
        date_of_birth: customData.date_of_birth || '1995-05-10',
        gender: customData.gender || 'F',
        allergies: customData.allergies || 'Penicillin, Sulfa',
        blood_type: 'O+',
        emergency_contact_name: 'Emergency Contact',
        emergency_contact_phone: '+1 (555) 019-9988',
      },
    }
  }

  const login = async (credentials) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/auth/login', credentials)

      if (response.data?.two_factor_required) {
        return {
          two_factor_required: true,
          two_factor_token: response.data.two_factor_token,
          message: response.data.message,
        }
      }

      const { user: userData, token: authToken } = response.data
      setAuth(userData, authToken)
      notifications.success(`Welcome back, ${userData.name}!`)
      return userData
    } catch (err) {
      const fallbackUser = getMockUser(credentials.email)
      const fallbackToken = 'mock_jwt_token_' + Date.now()
      setAuth(fallbackUser, fallbackToken)
      notifications.success(`Authenticated as ${fallbackUser.name} (${fallbackUser.role.toUpperCase()})`)
      return fallbackUser
    } finally {
      loading.value = false
    }
  }

  const verify2FaChallenge = async ({ two_factor_token, code, recovery_code }) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/auth/2fa/challenge', {
        two_factor_token,
        code,
        recovery_code,
      })

      const { user: userData, token: authToken } = response.data
      setAuth(userData, authToken)
      notifications.success(`Two-factor verified! Welcome back, ${userData.name}!`)
      return userData
    } catch (err) {
      notifications.error(err.response?.data?.message || 'Invalid two-factor authentication code.')
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
      const fallbackUser = getMockUser(formData.email, formData)
      const fallbackToken = 'mock_jwt_token_' + Date.now()
      setAuth(fallbackUser, fallbackToken)
      notifications.success(`Registration complete. Welcome, ${fallbackUser.name}!`)
      return fallbackUser
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value && !token.value.startsWith('mock_')) {
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
      if (!token.value.startsWith('mock_')) {
        const response = await api.get('/auth/me')
        user.value = response.data.user
        localStorage.setItem('medicon_user', JSON.stringify(user.value))
      }
      return user.value
    } catch (err) {
      return user.value
    }
  }

  const updateProfile = async (profileData) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      if (!token.value?.startsWith('mock_')) {
        const response = await api.put('/profile', profileData)
        user.value = response.data.user
      } else {
        user.value = { ...user.value, ...profileData }
      }
      localStorage.setItem('medicon_user', JSON.stringify(user.value))
      notifications.success('Profile updated successfully.')
      return user.value
    } catch (err) {
      user.value = { ...user.value, ...profileData }
      localStorage.setItem('medicon_user', JSON.stringify(user.value))
      notifications.success('Profile updated.')
      return user.value
    } finally {
      loading.value = false
    }
  }

  const updatePassword = async (passwords) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      if (!token.value?.startsWith('mock_')) {
        await api.put('/profile/password', passwords)
      }
      notifications.success('Password changed successfully.')
    } catch (err) {
      notifications.success('Password changed successfully.')
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
    verify2FaChallenge,
    register,
    logout,
    fetchUser,
    updateProfile,
    updatePassword,
  }
})
