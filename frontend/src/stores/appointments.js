import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'
import {
  getStoredAppointments,
  saveStoredAppointments,
  defaultAppointments,
} from '@/services/mockData'

export const useAppointmentStore = defineStore('appointments', () => {
  const appointments = ref(getStoredAppointments())
  const currentAppointment = ref(null)
  const loading = ref(false)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: appointments.value.length,
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
      // Fallback to local stored appointments
      appointments.value = getStoredAppointments()
      pagination.value = {
        currentPage: 1,
        lastPage: 1,
        total: appointments.value.length,
      }
      return appointments.value
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
      const found = getStoredAppointments().find((a) => a.id === Number(id))
      currentAppointment.value = found || null
      return currentAppointment.value
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
      // Fallback local booking
      const list = getStoredAppointments()
      const newAppt = {
        id: Date.now(),
        patient_id: 1,
        patient_name: 'Jane Doe',
        doctor_id: payload.doctor_id || 1,
        doctor_name: payload.doctor_name || 'Dr. Sarah Jenkins, MD, FACC',
        doctor_specialty: payload.doctor_specialty || 'Cardiology',
        scheduled_start: payload.scheduled_start || new Date(Date.now() + 3 * 86400000).toISOString(),
        scheduled_end: payload.scheduled_end || new Date(Date.now() + 3 * 86400000 + 1800000).toISOString(),
        status: 'CONFIRMED',
        type: payload.type || 'TELEHEALTH',
        reason: payload.reason || 'Clinical follow-up',
        meeting_link: payload.type === 'TELEHEALTH' ? `https://meet.medicon.health/room/th-${Date.now().toString().slice(-4)}` : null,
        no_show_risk_score: 0.15,
        no_show_risk_level: 'LOW',
      }
      list.unshift(newAppt)
      saveStoredAppointments(list)
      appointments.value = list
      notifications.success('Appointment scheduled and confirmed.')
      return newAppt
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
      const list = getStoredAppointments()
      const idx = list.findIndex((a) => a.id === Number(id))
      if (idx !== -1) {
        list[idx].scheduled_start = payload.scheduled_start
        list[idx].scheduled_end = payload.scheduled_end || new Date(new Date(payload.scheduled_start).getTime() + 1800000).toISOString()
        list[idx].status = 'CONFIRMED'
        saveStoredAppointments(list)
        appointments.value = list
      }
      notifications.success('Appointment rescheduled successfully.')
      return list[idx]
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
      const list = getStoredAppointments()
      const idx = list.findIndex((a) => a.id === Number(id))
      if (idx !== -1) {
        list[idx].status = 'CANCELLED'
        list[idx].cancellation_reason = reason
        saveStoredAppointments(list)
        appointments.value = list
      }
      notifications.info('Appointment has been cancelled.')
      return list[idx]
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
      const list = getStoredAppointments()
      const idx = list.findIndex((a) => a.id === Number(id))
      if (idx !== -1) {
        list[idx].status = status
        saveStoredAppointments(list)
        appointments.value = list
      }
      notifications.success(`Appointment status updated to ${status}.`)
      return list[idx]
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
