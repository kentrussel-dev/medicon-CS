import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'

export const useDoctorStore = defineStore('doctors', () => {
  const doctors = ref([])
  const specialties = ref([])
  const selectedDoctor = ref(null)
  const availabilities = ref([])
  const loading = ref(false)

  const fetchDoctors = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/doctors', { params })
      doctors.value = response.data.data
      return doctors.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchSpecialties = async () => {
    try {
      const response = await api.get('/doctors/specialties')
      specialties.value = response.data.specialties
      return specialties.value
    } catch (err) {
      specialties.value = [
        'Cardiology',
        'Dermatology',
        'Neurology',
        'Pediatrics',
        'Psychiatry',
        'General Practice',
      ]
    }
  }

  const fetchDoctor = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/doctors/${id}`)
      selectedDoctor.value = response.data.doctor
      return selectedDoctor.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchAvailabilities = async (doctorId = null) => {
    try {
      const response = await api.get('/doctor-availabilities', {
        params: doctorId ? { doctor_id: doctorId } : {},
      })
      availabilities.value = response.data.availabilities
      return availabilities.value
    } catch (err) {
      throw err
    }
  }

  const saveAvailabilities = async (slots) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/doctor-availabilities', { slots })
      availabilities.value = response.data.availabilities
      notifications.success('Working hours and availability updated.')
      return availabilities.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    doctors,
    specialties,
    selectedDoctor,
    availabilities,
    loading,
    fetchDoctors,
    fetchSpecialties,
    fetchDoctor,
    fetchAvailabilities,
    saveAvailabilities,
  }
})
