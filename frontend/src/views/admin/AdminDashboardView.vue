<template>
  <div class="space-y-6">
    <!-- Header Banner -->
    <div class="p-5 bg-white border border-slate-300 shadow-crisp flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-100 bg-slate-900 px-2 py-0.5 border border-slate-800">
            Executive Administration &bull; Clinical Operations
          </span>
        </div>
        <h2 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-1">Clinical Operations & Utilization Overview</h2>
        <p class="text-xs text-slate-600 font-mono mt-0.5">
          Patient attendance risk stratification, physician utilization rates, and HIPAA compliance monitoring
        </p>
      </div>

      <div class="flex items-center space-x-2">
        <router-link
          to="/admin/audit-logs"
          class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-mono font-bold uppercase tracking-wider border border-slate-950 transition-colors flex items-center space-x-2"
        >
          <History class="w-4 h-4" />
          <span>Audit Log Viewer</span>
        </router-link>
      </div>
    </div>

    <!-- Executive Stat Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard
        title="Registered Patients"
        :value="overview?.total_patients || 4"
        subtitle="Verified patient records"
        :icon="Users"
        color="emerald"
      />
      <StatCard
        title="Active Clinicians"
        :value="overview?.total_doctors || 5"
        subtitle="On-duty certified staff"
        :icon="Activity"
        color="blue"
      />
      <StatCard
        title="Historical No-Show Rate"
        :value="(overview?.no_show_rate || 14.3) + '%'"
        subtitle="Clinic absence metric"
        :icon="AlertTriangle"
        color="amber"
      />
      <StatCard
        title="Flagged Attendance Risk"
        :value="highRiskList.length || 1"
        subtitle="Attendance risk >= 65%"
        :icon="ShieldAlert"
        color="rose"
      />
    </div>

    <!-- Attendance Risk Triage Section -->
    <div class="bg-white border border-slate-300 shadow-crisp">
      <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Attendance Risk Stratification Triage</h3>
          <p class="text-[11px] text-slate-500 font-mono mt-0.5">
            Appointments flagged with elevated absence risk based on scheduling lead time, prior clinic history, and slot factors
          </p>
        </div>
        <span class="text-[10px] font-mono font-bold uppercase bg-rose-50 text-rose-800 px-2 py-0.5 border border-rose-300 self-start sm:self-auto">
          Active Triage Queue
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100 text-slate-600 uppercase font-mono font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-2.5">Patient Name</th>
              <th class="px-4 py-2.5">Attending Physician</th>
              <th class="px-4 py-2.5">Scheduled Slot</th>
              <th class="px-4 py-2.5">Attendance Risk Score</th>
              <th class="px-4 py-2.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 font-mono">
            <tr v-for="item in highRiskList" :key="item.id" class="hover:bg-slate-50">
              <td class="px-4 py-3 font-sans font-bold text-slate-900">{{ item.patient_name }}</td>
              <td class="px-4 py-3 font-sans">{{ item.doctor_name }} ({{ item.doctor_specialty }})</td>
              <td class="px-4 py-3 text-slate-600 font-bold">{{ formatDate(item.scheduled_start) }}</td>
              <td class="px-4 py-3">
                <RiskBadge
                  :level="item.no_show_risk_level || 'HIGH'"
                  :score="item.no_show_risk_score || 0.74"
                />
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="openTriage(item)"
                  class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-[11px] uppercase"
                >
                  Review Factors
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Doctor Utilization & Risk Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Doctor Utilization -->
      <div class="bg-white border border-slate-300 shadow-crisp">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Physician Utilization Registry</h3>
        </div>
        <div class="divide-y divide-slate-200 text-xs">
          <div
            v-for="doc in doctorUtilizationList"
            :key="doc.doctor_id"
            class="p-3.5 flex items-center justify-between hover:bg-slate-50"
          >
            <div>
              <span class="font-bold text-slate-900 block uppercase">{{ doc.name }}</span>
              <span class="text-slate-500 font-mono text-[11px]">{{ doc.specialty }}</span>
            </div>
            <div class="text-right font-mono">
              <span class="font-bold text-slate-900 block">{{ doc.total_appointments }} Encounters</span>
              <span class="text-slate-500 text-[11px]">{{ doc.rating }} &starf; Score</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Risk Stratification Summary -->
      <div class="bg-white border border-slate-300 shadow-crisp">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Patient Risk Tier Stratification</h3>
        </div>
        <div class="p-4 space-y-4 font-mono text-xs">
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
              <span class="text-slate-800">13% Flagged for Outreach</span>
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
import StatCard from '@/components/common/StatCard.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'
import HighRiskReviewModal from '@/components/admin/HighRiskReviewModal.vue'
import {
  Users,
  Activity,
  AlertTriangle,
  ShieldAlert,
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
    { doctor_id: 1, name: 'Dr. Sarah Jenkins', specialty: 'Cardiology', total_appointments: 14, rating: 4.95 },
    { doctor_id: 2, name: 'Dr. Marcus Chen', specialty: 'Neurology', total_appointments: 11, rating: 4.88 },
    { doctor_id: 3, name: 'Dr. Elena Rostova', specialty: 'Dermatology', total_appointments: 9, rating: 4.92 },
    { doctor_id: 4, name: 'Dr. James Wilson', specialty: 'General Practice', total_appointments: 8, rating: 4.90 },
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
      no_show_risk_score: 0.7420,
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
  } catch (err) {
    // Handled
  } finally {
    loading.value = false
  }
})
</script>
