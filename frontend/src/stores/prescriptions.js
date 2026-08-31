import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'

export const usePrescriptionStore = defineStore('prescriptions', () => {
  const prescriptions = ref([])
  const currentPrescription = ref(null)
  const loading = ref(false)

  const fetchPrescriptions = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/prescriptions', { params })
      prescriptions.value = response.data.data
      return prescriptions.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchPrescription = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/prescriptions/${id}`)
      currentPrescription.value = response.data.prescription
      return currentPrescription.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const createPrescription = async (payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/prescriptions', payload)
      notifications.success('Prescription formulated and issued successfully.')
      await fetchPrescriptions()
      return response.data.prescription
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const markDispensed = async (id) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.patch(`/prescriptions/${id}/dispense`)
      notifications.success('Prescription marked as dispensed.')
      await fetchPrescriptions()
      return response.data.prescription
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    prescriptions,
    currentPrescription,
    loading,
    fetchPrescriptions,
    fetchPrescription,
    createPrescription,
    markDispensed,
  }
})
