<template>
  <div class="space-y-6">
    <!-- Header & Filter Controls -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">System Appointments Directory</h2>
        <p class="text-xs text-slate-500 mt-0.5">Comprehensive audit and status overview across all clinical schedules</p>
      </div>

      <div class="flex items-center space-x-3">
        <select
          v-model="riskFilter"
          @change="loadAppointments"
          class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-500 bg-white"
        >
          <option value="">All Risk Tiers</option>
          <option value="HIGH">High Risk Only (&ge; 65%)</option>
          <option value="MEDIUM">Medium Risk</option>
          <option value="LOW">Low Risk</option>
        </select>
      </div>
    </div>

    <!-- Appointments Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-100">
            <tr>
              <th class="px-6 py-4">ID</th>
              <th class="px-6 py-4">Patient</th>
              <th class="px-6 py-4">Physician</th>
              <th class="px-6 py-4">Date / Slot</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">ML No-Show Risk</th>
              <th class="px-6 py-4 text-right">Format</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr v-for="appt in appointments" :key="appt.id" class="hover:bg-slate-50/50">
              <td class="px-6 py-4 font-mono font-bold text-slate-400">#{{ appt.id }}</td>
              <td class="px-6 py-4 font-bold text-slate-900">{{ appt.patient_name }}</td>
              <td class="px-6 py-4">{{ appt.doctor_name }} ({{ appt.doctor_specialty }})</td>
              <td class="px-6 py-4 text-slate-500 font-semibold">{{ formatDate(appt.scheduled_start) }}</td>
              <td class="px-6 py-4">
                <Badge :variant="appt.status">{{ appt.status }}</Badge>
              </td>
              <td class="px-6 py-4">
                <RiskBadge
                  v-if="appt.no_show_risk_level"
                  :level="appt.no_show_risk_level"
                  :score="appt.no_show_risk_score"
                />
                <span v-else class="text-slate-400 font-mono text-[11px]">&mdash;</span>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[10px] uppercase">
                  {{ appt.type }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'

const appointmentStore = useAppointmentStore()
const riskFilter = ref('')

const appointments = computed(() => appointmentStore.appointments)

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const loadAppointments = async () => {
  await appointmentStore.fetchAppointments({
    risk_level: riskFilter.value || undefined,
  })
}

onMounted(() => {
  loadAppointments()
})
</script>
