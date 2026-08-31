import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'

export const useAdminStore = defineStore('admin', () => {
  const overview = ref(null)
  const riskDistribution = ref(null)
  const monthlyTrends = ref([])
  const doctorUtilization = ref([])
  const highRiskAppointments = ref([])
  const users = ref([])
  const auditLogs = ref([])
  const loading = ref(false)

  const fetchDashboard = async () => {
    loading.value = true
    try {
      const response = await api.get('/admin/analytics/dashboard')
      overview.value = response.data.overview
      riskDistribution.value = response.data.risk_distribution
      monthlyTrends.value = response.data.monthly_trends
      doctorUtilization.value = response.data.doctor_utilization
      return response.data
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchHighRiskAppointments = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/admin/analytics/high-risk', { params })
      highRiskAppointments.value = response.data.data
      return highRiskAppointments.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchUsers = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/admin/users', { params })
      users.value = response.data.data
      return users.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const createUser = async (payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/admin/users', payload)
      notifications.success('New user account provisioned successfully.')
      await fetchUsers()
      return response.data.user
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const toggleUserStatus = async (id) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.patch(`/admin/users/${id}/toggle-status`)
      notifications.success('User status updated.')
      await fetchUsers()
      return response.data.user
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchAuditLogs = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/admin/audit-logs', { params })
      auditLogs.value = response.data.data
      return auditLogs.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    overview,
    riskDistribution,
    monthlyTrends,
    doctorUtilization,
    highRiskAppointments,
    users,
    auditLogs,
    loading,
    fetchDashboard,
    fetchHighRiskAppointments,
    fetchUsers,
    createUser,
    toggleUserStatus,
    fetchAuditLogs,
  }
})
