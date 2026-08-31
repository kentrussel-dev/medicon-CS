import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'

export const useAppointmentStore = defineStore('appointments', () => {
  const appointments = ref([])
  const currentAppointment = ref(null)
  const loading = ref(false)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
  })

  const fetchAppointments = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/appointments', { params })
      appointments.value = response.data.data
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          total: response.data.meta.total,
        }
      }
      return appointments.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchAppointment = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/appointments/${id}`)
      currentAppointment.value = response.data.appointment
      return currentAppointment.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const bookAppointment = async (payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/appointments', payload)
      notifications.success('Appointment booked successfully! Confirmation sent.')
      await fetchAppointments()
      return response.data.appointment
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const rescheduleAppointment = async (id, payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post(`/appointments/${id}/reschedule`, payload)
      notifications.success('Appointment rescheduled successfully.')
      await fetchAppointments()
      return response.data.appointment
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const cancelAppointment = async (id, reason) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post(`/appointments/${id}/cancel`, { cancellation_reason: reason })
      notifications.info('Appointment has been cancelled.')
      await fetchAppointments()
      return response.data.appointment
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateStatus = async (id, status, notes = null) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.patch(`/appointments/${id}/status`, { status, notes })
      notifications.success(`Appointment status updated to ${status}.`)
      await fetchAppointments()
      return response.data.appointment
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    appointments,
    currentAppointment,
    loading,
    pagination,
    fetchAppointments,
    fetchAppointment,
    bookAppointment,
    rescheduleAppointment,
    cancelAppointment,
    updateStatus,
  }
})
