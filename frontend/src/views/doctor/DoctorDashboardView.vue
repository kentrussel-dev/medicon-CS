<template>
  <div class="space-y-8">
    <!-- Header Banner -->
    <div class="p-6 sm:p-8 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl text-white shadow-lg flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-indigo-500/20 border border-indigo-400/30 rounded-full text-xs font-bold uppercase tracking-wider text-indigo-300">
          Clinical Practitioner Workspace
        </span>
        <h2 class="text-2xl sm:text-3xl font-black mt-2">Welcome, {{ auth.user?.name }}</h2>
        <p class="text-xs sm:text-sm text-slate-300 mt-1">
          Specialty: <span class="text-white font-bold">{{ auth.user?.doctor?.specialty || 'Physician' }}</span> &bull;
          License: <span class="font-mono text-indigo-300">{{ auth.user?.doctor?.license_number }}</span>
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button
          @click="showCreateRecordModal = true"
          class="px-5 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-md transition-all flex items-center space-x-2"
        >
          <FilePlus class="w-4 h-4" />
          <span>New Encounter Record</span>
        </button>
        <button
          @click="showCreateRxModal = true"
          class="px-5 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-md transition-all flex items-center space-x-2"
        >
          <Pill class="w-4 h-4" />
          <span>Issue Prescription</span>
        </button>
      </div>
    </div>

    <!-- Doctor Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <StatCard
        title="Pending & Upcoming Visits"
        :value="upcomingCount"
        subtitle="Scheduled patient encounters"
        :icon="Calendar"
        color="emerald"
      />
      <StatCard
        title="Completed Encounters"
        :value="completedCount"
        subtitle="Past documented consultations"
        :icon="CheckCircle2"
        color="blue"
      />
      <StatCard
        title="Patient Rating"
        :value="(auth.user?.doctor?.rating || 4.90) + ' / 5.0'"
        subtitle="Clinical satisfaction score"
        :icon="Star"
        color="amber"
      />
    </div>

    <!-- Today's Schedule & High-Risk Triage -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Appointments -->
      <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-black text-base text-slate-900">Upcoming Patient Schedule</h3>
          <router-link to="/doctor/appointments" class="text-xs font-bold text-brand-600 hover:text-brand-700">
            View All Schedule &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="py-8">
          <LoadingSpinner />
        </div>

        <div v-else-if="appointments.length === 0" class="text-center py-10 text-slate-400 text-xs font-medium">
          No appointments scheduled on your calendar currently.
        </div>

        <div v-else class="divide-y divide-slate-100">
          <div
            v-for="appt in appointments.slice(0, 5)"
            :key="appt.id"
            class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
          >
            <div>
              <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-900 text-sm">{{ appt.patient_name }}</span>
                <Badge :variant="appt.status">{{ appt.status }}</Badge>
                <RiskBadge
                  v-if="appt.no_show_risk_level"
                  :level="appt.no_show_risk_level"
                  :score="appt.no_show_risk_score"
                />
              </div>
              <p class="text-slate-600 mt-0.5">{{ appt.reason }}</p>
              <span class="text-slate-400 text-[11px] font-semibold">{{ formatDate(appt.scheduled_start) }}</span>
            </div>

            <div class="flex items-center space-x-2">
              <a
                v-if="appt.type === 'TELEHEALTH' && appt.meeting_link"
                :href="appt.meeting_link"
                target="_blank"
                class="px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold flex items-center space-x-1"
              >
                <Video class="w-3.5 h-3.5" />
                <span>Join Room</span>
              </a>
              <button
                @click="openQuickDocument(appt)"
                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold"
              >
                Document
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Working Hours Widget -->
      <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
        <h3 class="font-black text-base text-slate-900">Working Availability</h3>
        <p class="text-xs text-slate-500">Configure your daily slots and break hours</p>
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs space-y-2">
          <div class="flex justify-between font-semibold">
            <span class="text-slate-600">Mon - Fri:</span>
            <span class="text-slate-900 font-bold">09:00 - 17:00</span>
          </div>
          <div class="flex justify-between font-semibold">
            <span class="text-slate-600">Slot Length:</span>
            <span class="text-slate-900 font-bold">30 minutes</span>
          </div>
          <div class="flex justify-between font-semibold">
            <span class="text-slate-600">Consultation Fee:</span>
            <span class="text-slate-900 font-bold">${{ auth.user?.doctor?.consultation_fee }}</span>
          </div>
        </div>
        <router-link
          to="/doctor/schedule"
          class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center space-x-1.5 transition-colors block text-center"
        >
          <span>Modify Schedule</span>
        </router-link>
      </div>
    </div>

    <!-- Modals -->
    <CreateRecordModal
      :is-open="showCreateRecordModal"
      :prefill-patient-id="selectedAppt?.patient_id"
      :prefill-appointment-id="selectedAppt?.id"
      @close="showCreateRecordModal = false"
      @created="loadData"
    />

    <CreatePrescriptionModal
      :is-open="showCreateRxModal"
      :prefill-patient-id="selectedAppt?.patient_id"
      :prefill-appointment-id="selectedAppt?.id"
      @close="showCreateRxModal = false"
      @created="loadData"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAppointmentStore } from '@/stores/appointments'
import StatCard from '@/components/common/StatCard.vue'
import Badge from '@/components/common/Badge.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import CreateRecordModal from '@/components/doctor/CreateRecordModal.vue'
import CreatePrescriptionModal from '@/components/doctor/CreatePrescriptionModal.vue'
import {
  Calendar,
  CheckCircle2,
  Star,
  FilePlus,
  Pill,
  Video,
} from 'lucide-vue-next'

const auth = useAuthStore()
const appointmentStore = useAppointmentStore()

const loading = ref(false)
const showCreateRecordModal = ref(false)
const showCreateRxModal = ref(false)
const selectedAppt = ref(null)

const appointments = computed(() => appointmentStore.appointments)
const upcomingCount = computed(() =>
  appointments.value.filter((a) => a.status === 'CONFIRMED' || a.status === 'PENDING').length
)
const completedCount = computed(() =>
  appointments.value.filter((a) => a.status === 'COMPLETED').length
)

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const openQuickDocument = (appt) => {
  selectedAppt.value = appt
  showCreateRecordModal.value = true
}

const loadData = async () => {
  loading.value = true
  try {
    await appointmentStore.fetchAppointments()
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
