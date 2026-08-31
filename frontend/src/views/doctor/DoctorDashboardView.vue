<template>
  <div class="space-y-6">
    <!-- Header Banner -->
    <div class="p-5 bg-white border border-slate-300 shadow-crisp flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-brand-600 bg-slate-100 px-2 py-0.5 border border-slate-300">
            Attending Physician Workspace &bull; Clinical EHR
          </span>
        </div>
        <h2 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-1">{{ auth.user?.name }}</h2>
        <p class="text-xs text-slate-600 font-mono mt-0.5">
          Specialty: {{ auth.user?.doctor?.specialty || 'General Practice' }} &bull; License: {{ auth.user?.doctor?.license_number || 'MD-REG-001' }}
        </p>
      </div>

      <div class="flex items-center space-x-2">
        <button
          @click="showCreateRecordModal = true"
          class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider border border-brand-700 transition-colors flex items-center space-x-1.5"
        >
          <FilePlus class="w-4 h-4" />
          <span>New Encounter Record</span>
        </button>
        <button
          @click="showCreateRxModal = true"
          class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider border border-slate-950 transition-colors flex items-center space-x-1.5"
        >
          <Pill class="w-4 h-4" />
          <span>Issue Prescription</span>
        </button>
      </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <StatCard
        title="Pending Consultations"
        :value="upcomingCount"
        subtitle="Scheduled patient encounters"
        :icon="Calendar"
        color="blue"
      />
      <StatCard
        title="Documented Encounters"
        :value="completedCount"
        subtitle="Completed diagnostic visits"
        :icon="CheckCircle2"
        color="emerald"
      />
      <StatCard
        title="Licensing & Rating"
        :value="(auth.user?.doctor?.rating || 4.95) + ' / 5.0'"
        subtitle="Verified state clinical score"
        :icon="Star"
        color="purple"
      />
    </div>

    <!-- Active Schedule & Operational Settings -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Appointments -->
      <div class="lg:col-span-2 bg-white border border-slate-300 shadow-crisp">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Today's Patient Schedule</h3>
          <router-link to="/doctor/appointments" class="text-xs font-mono font-bold text-brand-600 hover:underline uppercase">
            Full Schedule &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="p-8">
          <LoadingSpinner />
        </div>

        <div v-else-if="appointments.length === 0" class="p-8 text-center text-slate-500 text-xs font-mono">
          No patients scheduled on today's calendar.
        </div>

        <div v-else class="divide-y divide-slate-200">
          <div
            v-for="appt in appointments.slice(0, 5)"
            :key="appt.id"
            class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs hover:bg-slate-50 transition-colors"
          >
            <div>
              <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-900 uppercase">{{ appt.patient_name }}</span>
                <Badge :variant="appt.status">{{ appt.status }}</Badge>
                <RiskBadge
                  v-if="appt.no_show_risk_level"
                  :level="appt.no_show_risk_level"
                  :score="appt.no_show_risk_score"
                />
              </div>
              <p class="text-slate-600 mt-1">{{ appt.reason }}</p>
              <span class="text-slate-500 text-[11px] font-mono">{{ formatDate(appt.scheduled_start) }}</span>
            </div>

            <div class="flex items-center space-x-2">
              <a
                v-if="appt.type === 'TELEHEALTH' && appt.meeting_link"
                :href="appt.meeting_link"
                target="_blank"
                class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold uppercase text-[11px] border border-emerald-800 flex items-center space-x-1"
              >
                <Video class="w-3.5 h-3.5" />
                <span>Enter Room</span>
              </a>
              <button
                @click="openQuickDocument(appt)"
                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 font-bold uppercase text-[11px]"
              >
                Document
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Working Hours Widget -->
      <div class="bg-white border border-slate-300 shadow-crisp">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Clinic Availability Parameters</h3>
        </div>
        <div class="p-4 space-y-3 text-xs font-mono">
          <div class="flex justify-between border-b border-slate-100 pb-2">
            <span class="text-slate-500 uppercase">Operational Days:</span>
            <span class="text-slate-900 font-bold">Mon &ndash; Fri (09:00 - 17:00)</span>
          </div>
          <div class="flex justify-between border-b border-slate-100 pb-2">
            <span class="text-slate-500 uppercase">Slot Block Duration:</span>
            <span class="text-slate-900 font-bold">30 Minutes</span>
          </div>
          <div class="flex justify-between border-b border-slate-100 pb-2">
            <span class="text-slate-500 uppercase">Authorized Standard Fee:</span>
            <span class="text-slate-900 font-bold">${{ auth.user?.doctor?.consultation_fee || 75 }}.00</span>
          </div>

          <div class="pt-2">
            <router-link
              to="/doctor/schedule"
              class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase text-center block border border-slate-950"
            >
              Modify Working Availability
            </router-link>
          </div>
        </div>
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
