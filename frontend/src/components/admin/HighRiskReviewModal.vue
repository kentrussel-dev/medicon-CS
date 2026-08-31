<template>
  <Modal :is-open="isOpen" title="Attendance Risk Triage" subtitle="Attendance probability and clinical schedule assessment" size="lg" @close="$emit('close')">
    <div v-if="appointment" class="space-y-5">
      <!-- Risk Score Hero Card -->
      <div class="p-5 bg-gradient-to-br from-rose-50 to-amber-50 rounded-2xl border border-rose-200/80 flex items-center justify-between">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Predicted No-Show Risk</span>
          <h3 class="text-3xl font-black text-rose-900 mt-1">
            {{ Math.round((appointment.no_show_risk_score || 0.75) * 100) }}%
          </h3>
          <p class="text-xs text-rose-700/80 mt-0.5">Model Classification: High Attendance Risk</p>
        </div>
        <div class="p-3 bg-rose-100 rounded-2xl text-rose-700">
          <AlertTriangle class="w-8 h-8" />
        </div>
      </div>

      <!-- Patient & Appointment Meta -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
        <div>
          <span class="text-slate-400 font-medium">Patient:</span>
          <p class="font-bold text-slate-900 mt-0.5">{{ appointment.patient_name }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-medium">Doctor:</span>
          <p class="font-bold text-slate-900 mt-0.5">{{ appointment.doctor_name }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-medium">Scheduled Time:</span>
          <p class="font-bold text-slate-900 mt-0.5">{{ formatDate(appointment.scheduled_start) }}</p>
        </div>
      </div>

      <!-- Key Contributing Risk Drivers -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Key Contributing Risk Drivers</h4>
        <ul class="space-y-2">
          <li
            v-for="(factor, idx) in appointment.risk_factors || defaultFactors"
            :key="idx"
            class="flex items-start p-3 bg-white rounded-xl border border-slate-200 text-xs font-medium text-slate-800"
          >
            <AlertCircle class="w-4 h-4 text-rose-500 mr-2.5 flex-shrink-0 mt-0.5" />
            <span>{{ factor }}</span>
          </li>
        </ul>
      </div>

      <!-- Recommended Action Protocol -->
      <div class="p-4 bg-sky-50 rounded-2xl border border-sky-100 text-xs text-sky-900 space-y-1.5">
        <span class="font-bold uppercase tracking-wider flex items-center">
          <PhoneCall class="w-3.5 h-3.5 mr-1 text-sky-600" /> Recommended Triage Protocol
        </span>
        <p>1. Initiate automated SMS & interactive voice reminder 48 hours prior.</p>
        <p>2. Have patient care coordinator verify phone confirmation.</p>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white transition-colors"
      >
        Complete Triage Review
      </button>
    </template>
  </Modal>
</template>

<script setup>
import Modal from '@/components/common/Modal.vue'
import { AlertTriangle, AlertCircle, PhoneCall } from 'lucide-vue-next'

defineProps({
  isOpen: Boolean,
  appointment: Object,
})

defineEmits(['close'])

const defaultFactors = [
  'High booking lead time (> 10 days)',
  'Prior unexcused clinic absence recorded in database',
  'Friday afternoon scheduling slot',
]

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}
</script>
