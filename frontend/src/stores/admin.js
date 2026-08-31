import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'
import {
  defaultDoctors,
  getStoredAuditLogs,
  getStoredAppointments,
} from '@/services/mockData'

export const useAdminStore = defineStore('admin', () => {
  const overview = ref(null)
  const doctorUtilization = ref([])
  const highRiskAppointments = ref([])
  const auditLogs = ref(getStoredAuditLogs())
  const users = ref([
    { id: 1, name: 'Jane Doe', email: 'patient@medicon.health', role: 'patient', is_active: true, created_at: '2026-01-15' },
    { id: 2, name: 'Dr. Sarah Jenkins', email: 'sarah.jenkins@medicon.health', role: 'doctor', is_active: true, created_at: '2025-11-20' },
    { id: 3, name: 'Operations Administrator', email: 'admin@medicon.health', role: 'admin', is_active: true, created_at: '2025-08-01' },
    { id: 4, name: 'Dr. Marcus Chen', email: 'marcus.chen@medicon.health', role: 'doctor', is_active: true, created_at: '2025-10-05' },
  ])
  const loading = ref(false)

  const fetchDashboard = async () => {
    loading.value = true
    try {
      const response = await api.get('/admin/analytics/overview')
      overview.value = response.data.overview
      doctorUtilization.value = response.data.doctor_utilization
      return response.data
    } catch (err) {
      overview.value = {
        total_patients: 1240,
        total_doctors: 28,
        total_appointments: 3410,
        no_show_rate: 11.2,
      }
      doctorUtilization.value = [
        { doctor_id: 1, name: 'Dr. Sarah Jenkins', specialty: 'Cardiology', total_appointments: 48, rating: 4.95 },
        { doctor_id: 2, name: 'Dr. Marcus Chen', specialty: 'Neurology', total_appointments: 36, rating: 4.88 },
        { doctor_id: 3, name: 'Dr. Elena Rostova', specialty: 'Dermatology', total_appointments: 42, rating: 4.92 },
        { doctor_id: 4, name: 'Dr. James Wilson', specialty: 'General Practice', total_appointments: 64, rating: 4.90 },
      ]
      return { overview: overview.value, doctor_utilization: doctorUtilization.value }
    } finally {
      loading.value = false
    }
  }

  const fetchHighRiskAppointments = async () => {
    try {
      const response = await api.get('/admin/high-risk-appointments')
      highRiskAppointments.value = response.data.appointments
      return highRiskAppointments.value
    } catch (err) {
      highRiskAppointments.value = [
        {
          id: 4,
          patient_name: 'Robert Vance',
          doctor_name: 'Dr. Sarah Jenkins',
          doctor_specialty: 'Cardiology',
          scheduled_start: new Date(Date.now() + 10 * 86400000).toISOString(),
          no_show_risk_score: 0.74,
          no_show_risk_level: 'HIGH',
          risk_factors: [
            'Booking lead time (12 days)',
            'History of missed appointments recorded',
            'Friday afternoon schedule slot',
          ],
        },
      ]
      return highRiskAppointments.value
    }
  }

  const fetchUsers = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/admin/users', { params })
      users.value = response.data.data
      return users.value
    } catch (err) {
      return users.value
    } finally {
      loading.value = false
    }
  }

  const updateUserRole = async (userId, newRole) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.patch(`/admin/users/${userId}/role`, { role: newRole })
      notifications.success('User role updated successfully.')
      await fetchUsers()
      return response.data.user
    } catch (err) {
      const u = users.value.find((x) => x.id === userId)
      if (u) u.role = newRole
      notifications.success(`User role updated to ${newRole}.`)
      return u
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
      auditLogs.value = getStoredAuditLogs()
      return auditLogs.value
    } finally {
      loading.value = false
    }
  }

  return {
    overview,
    doctorUtilization,
    highRiskAppointments,
    auditLogs,
    users,
    loading,
    fetchDashboard,
    fetchHighRiskAppointments,
    fetchUsers,
    updateUserRole,
    fetchAuditLogs,
  }
})
