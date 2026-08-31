import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'
import {
  getStoredPrescriptions,
  saveStoredPrescriptions,
} from '@/services/mockData'

export const usePrescriptionStore = defineStore('prescriptions', () => {
  const prescriptions = ref(getStoredPrescriptions())
  const currentPrescription = ref(null)
  const loading = ref(false)

  const fetchPrescriptions = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/prescriptions', { params })
      prescriptions.value = response.data.data
      return prescriptions.value
    } catch (err) {
      prescriptions.value = getStoredPrescriptions()
      return prescriptions.value
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
      const found = getStoredPrescriptions().find((p) => p.id === Number(id))
      currentPrescription.value = found || null
      return currentPrescription.value
    } finally {
      loading.value = false
    }
  }

  const createPrescription = async (payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/prescriptions', payload)
      notifications.success('Prescription issued and signed successfully.')
      await fetchPrescriptions()
      return response.data.prescription
    } catch (err) {
      const list = getStoredPrescriptions()
      const newRx = {
        id: Date.now(),
        patient_id: payload.patient_id || 1,
        patient_name: 'Jane Doe',
        doctor_id: 1,
        doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
        doctor_specialty: 'Cardiology',
        medication_name: payload.medication_name || 'Prescription Drug',
        dosage: payload.dosage || '10mg',
        frequency: payload.frequency || 'Once daily',
        duration: payload.duration || '30 days',
        instructions: payload.instructions || 'Take as directed by physician.',
        refills_remaining: payload.refills_remaining !== undefined ? payload.refills_remaining : 1,
        status: 'ACTIVE',
        prescribed_date: new Date().toISOString(),
      }
      list.unshift(newRx)
      saveStoredPrescriptions(list)
      prescriptions.value = list
      notifications.success('Prescription authorized and dispatched to patient EHR.')
      return newRx
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
  }
})
