<template>
  <Modal :is-open="isOpen" title="Patient Clinical History" :subtitle="patient ? `${patient.name} (DOB: ${patient.dob})` : ''" size="xl" @close="$emit('close')">
    <div v-if="patient" class="space-y-6">
      <!-- Demographic & Health Snapshot -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
        <div>
          <span class="text-slate-400 font-semibold">Allergies:</span>
          <p class="font-bold text-rose-700 mt-0.5">{{ patient.allergies || 'None Known' }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-semibold">Hypertension:</span>
          <p class="font-bold text-slate-800 mt-0.5">{{ patient.hypertension ? 'Diagnosed' : 'Negative' }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-semibold">Diabetes:</span>
          <p class="font-bold text-slate-800 mt-0.5">{{ patient.diabetes ? 'Diagnosed' : 'Negative' }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-semibold">Audit Status:</span>
          <p class="font-bold text-emerald-600 mt-0.5 flex items-center">
            <ShieldCheck class="w-3 h-3 mr-1 inline" /> Logged
          </p>
        </div>
      </div>

      <!-- Historical Consultations -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Encounter Timeline & Diagnoses</h4>
        <div class="space-y-3">
          <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-xs text-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-black text-slate-900 text-sm">Essential (primary) hypertension, stage 1</span>
              <span class="text-slate-400 font-semibold">14 days ago</span>
            </div>
            <p class="text-slate-600 leading-relaxed font-sans">
              Patient presented for routine cardiovascular evaluation. Heart sounds regular S1/S2. Advised on low sodium diet and regular aerobic exercise. Prescribed Lisinopril 10mg.
            </p>
            <div class="flex items-center space-x-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500">
              <span class="font-bold text-brand-700">BP: 134/86 mmHg</span>
              <span>&bull;</span>
              <span>ICD-10: I10, Z71.3</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Historical Prescriptions -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Medication Formulation History</h4>
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs flex items-center justify-between">
          <div>
            <span class="font-bold text-slate-900">Lisinopril 10mg + Hydrochlorothiazide 12.5mg</span>
            <p class="text-slate-500 mt-0.5">Take once daily in the morning with water. 90-day supply.</p>
          </div>
          <Badge variant="success">Active</Badge>
        </div>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white transition-colors"
      >
        Close Record
      </button>
    </template>
  </Modal>
</template>

<script setup>
import Modal from '@/components/common/Modal.vue'
import Badge from '@/components/common/Badge.vue'
import { ShieldCheck } from 'lucide-vue-next'

defineProps({
  isOpen: Boolean,
  patient: Object,
})

defineEmits(['close'])
</script>
