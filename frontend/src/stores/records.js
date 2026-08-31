import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'

export const useRecordStore = defineStore('records', () => {
  const records = ref([])
  const currentRecord = ref(null)
  const patientHistory = ref(null)
  const loading = ref(false)

  const fetchRecords = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/medical-records', { params })
      records.value = response.data.data
      return records.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchRecord = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/medical-records/${id}`)
      currentRecord.value = response.data.record
      return currentRecord.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const createRecord = async (payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/medical-records', payload)
      notifications.success('Clinical medical record saved successfully.')
      await fetchRecords()
      return response.data.record
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateRecord = async (id, payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.put(`/medical-records/${id}`, payload)
      notifications.success('Medical record updated.')
      await fetchRecords()
      return response.data.record
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchPatientHistory = async (patientId) => {
    loading.value = true
    try {
      const response = await api.get(`/patients/${patientId}/history`)
      patientHistory.value = response.data
      return patientHistory.value
    } catch (err) {
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    records,
    currentRecord,
    patientHistory,
    loading,
    fetchRecords,
    fetchRecord,
    createRecord,
    updateRecord,
    fetchPatientHistory,
  }
})
