<template>
  <Modal :is-open="isOpen" title="Document Patient Encounter" subtitle="Record clinical findings, encrypted notes, and vital signs" size="xl" @close="$emit('close')">
    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Patient & Appointment Meta -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Patient ID / Target</label>
          <input
            type="number"
            v-model.number="form.patient_id"
            required
            placeholder="e.g. 1"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Record Date</label>
          <input
            type="date"
            v-model="form.record_date"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>

      <!-- Vital Signs Grid -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Patient Vital Parameters</label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div>
            <span class="text-[11px] text-slate-500">Blood Pressure</span>
            <input
              type="text"
              v-model="form.vital_signs.blood_pressure"
              placeholder="120/80"
              class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div>
            <span class="text-[11px] text-slate-500">Heart Rate (BPM)</span>
            <input
              type="number"
              v-model.number="form.vital_signs.heart_rate"
              placeholder="72"
              class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div>
            <span class="text-[11px] text-slate-500">Oxygen Saturation (%)</span>
            <input
              type="number"
              v-model.number="form.vital_signs.oxygen_saturation"
              placeholder="98"
              class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div>
            <span class="text-[11px] text-slate-500">Weight (kg)</span>
            <input
              type="number"
              step="0.1"
              v-model.number="form.vital_signs.weight_kg"
              placeholder="75.0"
              class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"
            />
          </div>
        </div>
      </div>

      <!-- Diagnosis -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Primary Diagnosis (Encrypted)</label>
        <input
          type="text"
          v-model="form.diagnosis"
          required
          placeholder="e.g. Essential (primary) hypertension, stage 1"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        />
      </div>

      <!-- ICD-10 Code Input -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">ICD-10 Diagnostic Codes (Comma separated)</label>
        <input
          type="text"
          v-model="icdRaw"
          placeholder="I10, Z71.3, G44.209"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        />
      </div>

      <!-- Clinical Notes -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Clinical Examination Notes (Encrypted)</label>
        <textarea
          v-model="form.clinical_notes"
          required
          rows="4"
          placeholder="Subjective complaints, objective physical examination, assessment..."
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none font-sans"
        ></textarea>
      </div>

      <!-- Treatment Plan -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Care & Treatment Plan (Encrypted)</label>
        <textarea
          v-model="form.treatment_plan"
          rows="3"
          placeholder="Pharmacotherapy instructions, lifestyle modifications, follow-up timeline..."
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"
        ></textarea>
      </div>
    </form>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors"
      >
        Discard
      </button>
      <button
        type="button"
        :disabled="submitting"
        @click="handleSubmit"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white transition-colors shadow-sm disabled:opacity-50"
      >
        <span v-if="submitting">Encrypting & Saving...</span>
        <span v-else>Save Clinical Record</span>
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import Modal from '@/components/common/Modal.vue'
import { useRecordStore } from '@/stores/records'

const props = defineProps({
  isOpen: Boolean,
  prefillPatientId: Number,
  prefillAppointmentId: Number,
})

const emit = defineEmits(['close', 'created'])

const recordStore = useRecordStore()
const submitting = ref(false)
const icdRaw = ref('I10')

const form = ref({
  patient_id: '',
  appointment_id: null,
  record_date: new Date().toISOString().split('T')[0],
  diagnosis: '',
  clinical_notes: '',
  treatment_plan: '',
  vital_signs: {
    blood_pressure: '120/80',
    heart_rate: 72,
    oxygen_saturation: 99,
    weight_kg: 70,
  },
})

watch(() => props.prefillPatientId, (id) => {
  if (id) form.value.patient_id = id
}, { immediate: true })

watch(() => props.prefillAppointmentId, (id) => {
  if (id) form.value.appointment_id = id
}, { immediate: true })

const handleSubmit = async () => {
  if (!form.value.patient_id || !form.value.diagnosis || !form.value.clinical_notes) return

  submitting.value = true
  try {
    const icdArray = icdRaw.value.split(',').map((s) => s.trim()).filter(Boolean)

    await recordStore.createRecord({
      ...form.value,
      icd_10_codes: icdArray,
    })

    emit('created')
    emit('close')
  } catch (err) {
    // Handled
  } finally {
    submitting.value = false
  }
}
</script>
