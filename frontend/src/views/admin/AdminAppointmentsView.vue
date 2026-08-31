<template>
  <div class="space-y-5">
    <!-- Header & Filter Controls -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Admin Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Encounter Records</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">System Appointments Directory</h1>
      </div>

      <div class="flex items-center space-x-2">
        <select
          v-model="riskFilter"
          @change="loadAppointments"
          class="px-2.5 py-1.5 border border-slate-300 text-xs font-mono focus:border-slate-800 bg-white rounded-none uppercase"
        >
          <option value="">All Risk Tiers</option>
          <option value="HIGH">High Risk (&ge; 65%)</option>
          <option value="MEDIUM">Medium Risk</option>
          <option value="LOW">Low Risk</option>
        </select>
      </div>
    </div>

    <!-- Appointments Table -->
    <div class="bg-white border border-slate-300 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-slate-100 text-slate-600 uppercase font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-2.5">ID</th>
              <th class="px-4 py-2.5">Patient</th>
              <th class="px-4 py-2.5">Physician</th>
              <th class="px-4 py-2.5">Scheduled Slot</th>
              <th class="px-4 py-2.5">Status</th>
              <th class="px-4 py-2.5">Attendance Risk</th>
              <th class="px-4 py-2.5 text-right">Mode</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="appt in appointments" :key="appt.id" class="hover:bg-slate-50">
              <td class="px-4 py-3 font-bold text-slate-400">#{{ appt.id }}</td>
              <td class="px-4 py-3 font-sans font-bold text-slate-900 uppercase">{{ appt.patient_name }}</td>
              <td class="px-4 py-3 font-sans">{{ appt.doctor_name }} ({{ appt.doctor_specialty }})</td>
              <td class="px-4 py-3 text-slate-600">{{ formatDate(appt.scheduled_start) }}</td>
              <td class="px-4 py-3">
                <Badge :variant="appt.status">{{ appt.status }}</Badge>
              </td>
              <td class="px-4 py-3">
                <RiskBadge
                  v-if="appt.no_show_risk_level"
                  :level="appt.no_show_risk_level"
                  :score="appt.no_show_risk_score"
                />
                <span v-else class="text-slate-400">&mdash;</span>
              </td>
              <td class="px-4 py-3 text-right">
                <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 text-slate-700 text-[10px] uppercase font-bold">
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
import { ref, onMounted } from 'vue'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'

const appointmentStore = useAppointmentStore()
const appointments = ref([])
const riskFilter = ref('')

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const loadAppointments = async () => {
  const data = await appointmentStore.fetchAppointments()
  if (riskFilter.value) {
    appointments.value = data.filter((a) => a.no_show_risk_level === riskFilter.value)
  } else {
    appointments.value = data
  }
}

onMounted(() => {
  loadAppointments()
})
</script>
