<template>
  <div class="space-y-5">
    <!-- Top Minimalist Header Bar -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Executive Administration</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Hospital Operations</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">Clinical Operations & Utilization</h1>
      </div>

      <div class="flex items-center space-x-2">
        <router-link
          to="/admin/audit-logs"
          class="px-3.5 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-mono font-bold uppercase tracking-wider border border-brand-800 transition-colors flex items-center space-x-1.5"
        >
          <History class="w-3.5 h-3.5" />
          <span>Audit Log Trail</span>
        </router-link>
      </div>
    </div>

    <!-- Key Metrics (Crisp Clean Stats) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <div class="bg-white border border-slate-300 p-4">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Total Patients</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ overview?.total_patients || 1240 }}</span>
      </div>
      <div class="bg-white border border-slate-300 p-4">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Active Clinicians</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ overview?.total_doctors || 28 }}</span>
      </div>
      <div class="bg-white border border-slate-300 p-4">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Absence Rate</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ overview?.no_show_rate || 11.2 }}%</span>
      </div>
      <div class="bg-white border border-slate-300 p-4">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Flagged Attendance Risk</span>
        <span class="text-2xl font-bold font-mono text-rose-800 mt-1 block">{{ highRiskList.length || 1 }}</span>
      </div>
    </div>

    <!-- Attendance Risk Triage Section -->
    <div class="bg-white border border-slate-300">
      <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
        <span class="font-bold uppercase tracking-wider text-slate-800">Attendance Risk Stratification</span>
        <span class="text-[10px] font-mono font-bold uppercase bg-rose-50 text-rose-800 px-2 py-0.5 border border-rose-300 self-start sm:self-auto">
          Active Triage Queue
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100 text-slate-600 uppercase font-mono font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-2">Patient</th>
              <th class="px-4 py-2">Attending Doctor</th>
              <th class="px-4 py-2">Scheduled Slot</th>
              <th class="px-4 py-2">Risk Score</th>
              <th class="px-4 py-2 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 font-mono">
            <tr v-for="item in highRiskList" :key="item.id" class="hover:bg-slate-50">
              <td class="px-4 py-2.5 font-sans font-bold text-slate-900">{{ item.patient_name }}</td>
              <td class="px-4 py-2.5 font-sans">{{ item.doctor_name }} ({{ item.doctor_specialty }})</td>
              <td class="px-4 py-2.5 text-slate-600">{{ formatDate(item.scheduled_start) }}</td>
              <td class="px-4 py-2.5">
                <RiskBadge
                  :level="item.no_show_risk_level || 'HIGH'"
                  :score="item.no_show_risk_score || 0.74"
                />
              </td>
              <td class="px-4 py-2.5 text-right">
                <button
                  @click="openTriage(item)"
                  class="px-2 py-1 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-[10px] uppercase"
                >
                  Review
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Doctor Utilization Registry -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="bg-white border border-slate-300">
        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-800">
          Physician Utilization Registry
        </div>
        <div class="divide-y divide-slate-200 text-xs">
          <div
            v-for="doc in doctorUtilizationList"
            :key="doc.doctor_id"
            class="p-3 flex items-center justify-between hover:bg-slate-50"
          >
            <div>
              <span class="font-bold text-slate-900 block uppercase">{{ doc.name }}</span>
              <span class="text-slate-400 font-mono text-[11px]">{{ doc.specialty }}</span>
            </div>
            <div class="text-right font-mono">
              <span class="font-bold text-slate-900 block">{{ doc.total_appointments }} Encounters</span>
              <span class="text-slate-400 text-[11px]">{{ doc.rating }} &starf;</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Risk Tier Stratification -->
      <div class="bg-white border border-slate-300">
        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-800">
          Patient Risk Distribution
        </div>
        <div class="p-4 space-y-3 font-mono text-xs">
          <div>
            <div class="flex justify-between font-bold mb-1">
              <span class="text-emerald-800 uppercase">Low Risk (&lt; 35%)</span>
              <span class="text-slate-800">65% of Scheduled Volume</span>
            </div>
            <div class="w-full bg-slate-100 h-2 border border-slate-300">
              <div class="bg-emerald-600 h-full" style="width: 65%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between font-bold mb-1">
              <span class="text-amber-800 uppercase">Moderate Risk (35% - 65%)</span>
              <span class="text-slate-800">22% of Scheduled Volume</span>
            </div>
            <div class="w-full bg-slate-100 h-2 border border-slate-300">
              <div class="bg-amber-600 h-full" style="width: 22%"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between font-bold mb-1">
              <span class="text-rose-800 uppercase">High Risk (&ge; 65%)</span>
              <span class="text-slate-800">13% Flagged</span>
            </div>
            <div class="w-full bg-slate-100 h-2 border border-slate-300">
              <div class="bg-rose-600 h-full" style="width: 13%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Triage Review Modal -->
    <HighRiskReviewModal
      :is-open="showTriageModal"
      :appointment="selectedAppointment"
      @close="showTriageModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import RiskBadge from '@/components/common/RiskBadge.vue'
import HighRiskReviewModal from '@/components/admin/HighRiskReviewModal.vue'
import {
  History,
} from 'lucide-vue-next'

const adminStore = useAdminStore()
const loading = ref(false)
const showTriageModal = ref(false)
const selectedAppointment = ref(null)

const overview = computed(() => adminStore.overview)
const doctorUtilizationList = computed(() => {
  if (adminStore.doctorUtilization?.length) return adminStore.doctorUtilization
  return [
    { doctor_id: 1, name: 'Dr. Sarah Jenkins', specialty: 'Cardiology', total_appointments: 48, rating: 4.95 },
    { doctor_id: 2, name: 'Dr. Marcus Chen', specialty: 'Neurology', total_appointments: 36, rating: 4.88 },
    { doctor_id: 3, name: 'Dr. Elena Rostova', specialty: 'Dermatology', total_appointments: 42, rating: 4.92 },
    { doctor_id: 4, name: 'Dr. James Wilson', specialty: 'General Practice', total_appointments: 64, rating: 4.90 },
  ]
})

const highRiskList = computed(() => {
  if (adminStore.highRiskAppointments?.length) return adminStore.highRiskAppointments
  return [
    {
      id: 4,
      patient_name: 'Robert Vance',
      doctor_name: 'Dr. Sarah Jenkins',
      doctor_specialty: 'Cardiology',
      scheduled_start: new Date(Date.now() + 10 * 86400000).toISOString(),
      no_show_risk_score: 0.74,
      no_show_risk_level: 'HIGH',
      risk_factors: [
        'Booking lead time (12 days)',
        'History of missed appointments recorded',
        'Friday afternoon schedule slot',
      ],
    },
  ]
})

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const openTriage = (item) => {
  selectedAppointment.value = item
  showTriageModal.value = true
}

onMounted(async () => {
  loading.value = true
  try {
    await adminStore.fetchDashboard()
    await adminStore.fetchHighRiskAppointments()
  } finally {
    loading.value = false
  }
})
</script>
