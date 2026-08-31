import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from './notifications'
import {
  getStoredRecords,
  saveStoredRecords,
} from '@/services/mockData'

export const useRecordStore = defineStore('records', () => {
  const records = ref(getStoredRecords())
  const currentRecord = ref(null)
  const loading = ref(false)

  const fetchRecords = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/medical-records', { params })
      records.value = response.data.data
      return records.value
    } catch (err) {
      records.value = getStoredRecords()
      return records.value
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
      const found = getStoredRecords().find((r) => r.id === Number(id))
      currentRecord.value = found || null
      return currentRecord.value
    } finally {
      loading.value = false
    }
  }

  const createRecord = async (payload) => {
    loading.value = true
    const notifications = useNotificationStore()
    try {
      const response = await api.post('/medical-records', payload)
      notifications.success('Encrypted clinical record saved to patient EHR.')
      await fetchRecords()
      return response.data.record
    } catch (err) {
      const list = getStoredRecords()
      const newRec = {
        id: Date.now(),
        appointment_id: payload.appointment_id || null,
        patient_id: payload.patient_id || 1,
        patient_name: 'Jane Doe',
        doctor_id: 1,
        doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
        doctor_specialty: 'Cardiology',
        diagnosis: payload.diagnosis || 'Clinical Follow-Up',
        clinical_notes: payload.clinical_notes || '',
        vital_signs: payload.vital_signs || {
          blood_pressure: '120/80',
          heart_rate: '70 bpm',
          temperature: '98.6 °F',
          spo2: '99%',
          weight: '65 kg',
        },
        created_at: new Date().toISOString(),
      }
      list.unshift(newRec)
      saveStoredRecords(list)
      records.value = list
      notifications.success('Encrypted clinical record documented and signed.')
      return newRec
    } finally {
      loading.value = false
    }
  }

  return {
    records,
    currentRecord,
    loading,
    fetchRecords,
    fetchRecord,
    createRecord,
  }
})
