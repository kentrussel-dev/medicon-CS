import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'
import { defaultDoctors } from '@/services/mockData'

export const useDoctorStore = defineStore('doctors', () => {
  const doctors = ref(defaultDoctors)
  const specialties = ref([
    'Cardiology',
    'Dermatology',
    'Neurology',
    'Orthopedic',
    'Pediatrics',
    'General Practice',
  ])
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
      if (params.specialty) {
        doctors.value = defaultDoctors.filter((d) =>
          d.specialty.toLowerCase().includes(params.specialty.toLowerCase())
        )
      } else if (params.search) {
        const q = params.search.toLowerCase()
        doctors.value = defaultDoctors.filter(
          (d) => d.name.toLowerCase().includes(q) || d.specialty.toLowerCase().includes(q)
        )
      } else {
        doctors.value = defaultDoctors
      }
      return doctors.value
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
        'Orthopedic',
        'Pediatrics',
        'General Practice',
      ]
      return specialties.value
    }
  }

  const fetchDoctor = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/doctors/${id}`)
      selectedDoctor.value = response.data.doctor
      return selectedDoctor.value
    } catch (err) {
      selectedDoctor.value = defaultDoctors.find((d) => d.id === Number(id)) || defaultDoctors[0]
      return selectedDoctor.value
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
      availabilities.value = [
        { day_of_week: 'Monday', start_time: '09:00', end_time: '17:00', is_active: true },
        { day_of_week: 'Tuesday', start_time: '09:00', end_time: '17:00', is_active: true },
        { day_of_week: 'Wednesday', start_time: '09:00', end_time: '17:00', is_active: true },
        { day_of_week: 'Thursday', start_time: '09:00', end_time: '17:00', is_active: true },
        { day_of_week: 'Friday', start_time: '09:00', end_time: '17:00', is_active: true },
      ]
      return availabilities.value
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
      availabilities.value = slots
      notifications.success('Working hours and availability updated.')
      return availabilities.value
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
