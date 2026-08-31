<template>
  <div class="space-y-5">
    <!-- Top Minimalist Header Bar -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-up animate-enter-1">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Clinical Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">{{ auth.user?.doctor?.specialty || 'General Practice' }}</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">{{ auth.user?.name }}</h1>
        <p class="text-xs text-slate-500 font-mono mt-0.5">License: {{ auth.user?.doctor?.license_number || 'MD-99281-STATE' }}</p>
      </div>

      <div class="flex items-center space-x-2">
        <button
          @click="showCreateRecordModal = true"
          class="px-3.5 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold uppercase tracking-wider border border-brand-800 transition-colors flex items-center space-x-1.5"
        >
          <FilePlus class="w-3.5 h-3.5" />
          <span>New Clinical Record</span>
        </button>
        <button
          @click="showCreateRxModal = true"
          class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors flex items-center space-x-1.5"
        >
          <Pill class="w-3.5 h-3.5 text-brand-700" />
          <span>Issue Prescription</span>
        </button>
      </div>
    </div>

    <!-- Instant Telehealth Consultation Banner -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs animate-fade-up animate-enter-2">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg flex items-center justify-center shrink-0">
          <Video class="w-5 h-5" />
        </div>
        <div>
          <h4 class="font-bold text-sm text-slate-900 uppercase">Instant Doctor Consultation Room</h4>
          <p class="text-xs text-slate-600 font-sans">Start an ad-hoc room or join an ongoing patient session by unique room code (e.g. <code class="font-mono text-brand-700 font-bold">sdf-sdyy-125</code>).</p>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <div class="flex items-center border border-slate-300 rounded-lg bg-slate-50 overflow-hidden">
          <input
            type="text"
            v-model="joinRoomCode"
            placeholder="e.g. sdf-sdyy-125"
            class="px-3 py-2 text-xs font-mono bg-transparent focus:outline-none w-36 sm:w-44 text-slate-900"
            @keyup.enter="joinByCode"
          />
          <button
            @click="joinByCode"
            :disabled="!joinRoomCode.trim()"
            class="px-3.5 py-2 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase transition-colors disabled:opacity-40"
          >
            Join
          </button>
        </div>

        <button
          @click="createInstantMeeting"
          class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-mono text-xs font-bold uppercase border border-slate-300 rounded-lg transition-colors whitespace-nowrap"
        >
          New Room
        </button>
      </div>
    </div>

    <!-- Key Metrics (Crisp Clean Stats) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 animate-fade-up animate-enter-3">
      <div class="bg-white border border-slate-300 p-4 hover:border-slate-400 transition-colors">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Pending Consultations</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ upcomingCount }}</span>
      </div>
      <div class="bg-white border border-slate-300 p-4 hover:border-slate-400 transition-colors">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Completed Encounters</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ completedCount }}</span>
      </div>
      <div class="bg-white border border-slate-300 p-4 hover:border-slate-400 transition-colors">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Clinical Score</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ auth.user?.doctor?.rating || 4.95 }} / 5.0</span>
      </div>
    </div>

    <!-- Active Schedule & Operational Settings -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 animate-fade-up animate-enter-4">
      <!-- Appointments List -->
      <div class="lg:col-span-2 bg-white border border-slate-300">
        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
          <span class="font-bold uppercase tracking-wider text-slate-800">Today's Patient Schedule</span>
          <router-link to="/doctor/appointments" class="font-mono font-bold text-brand-600 hover:underline uppercase">
            Full Schedule &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="p-8">
          <LoadingSpinner />
        </div>

        <div v-else-if="appointments.length === 0" class="p-6 text-center text-slate-500 text-xs font-mono">
          No patients scheduled for today.
        </div>

        <div v-else class="divide-y divide-slate-200">
          <div
            v-for="appt in appointments.slice(0, 5)"
            :key="appt.id"
            class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs hover:bg-slate-50 transition-colors"
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
              <p class="text-slate-600 mt-0.5">{{ appt.reason }}</p>
              <span class="text-slate-400 text-[11px] font-mono">{{ formatDate(appt.scheduled_start) }}</span>
            </div>

            <div class="flex items-center space-x-2">
              <router-link
                v-if="appt.type === 'TELEHEALTH'"
                :to="'/telehealth/room/' + appt.id"
                class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white font-mono font-bold uppercase text-[11px] border border-emerald-800 flex items-center space-x-1"
              >
                <Video class="w-3 h-3" />
                <span>Join</span>
              </router-link>
              <button
                @click="openQuickDocument(appt)"
                class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 font-mono font-bold uppercase text-[11px]"
              >
                Document
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Working Parameters -->
      <div class="bg-white border border-slate-300">
        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-800">
          Clinic Availability
        </div>
        <div class="p-3.5 space-y-2 text-xs font-mono">
          <div class="flex justify-between border-b border-slate-100 pb-1.5">
            <span class="text-slate-400 uppercase">Days:</span>
            <span class="text-slate-900 font-bold">Mon &ndash; Fri</span>
          </div>
          <div class="flex justify-between border-b border-slate-100 pb-1.5">
            <span class="text-slate-400 uppercase">Hours:</span>
            <span class="text-slate-900 font-bold">09:00 &ndash; 17:00</span>
          </div>
          <div class="flex justify-between border-b border-slate-100 pb-1.5">
            <span class="text-slate-400 uppercase">Slot Length:</span>
            <span class="text-slate-900 font-bold">30 Mins</span>
          </div>
          <div class="flex justify-between border-b border-slate-100 pb-1.5">
            <span class="text-slate-400 uppercase">Standard Fee:</span>
            <span class="text-slate-900 font-bold">₱{{ (auth.user?.doctor?.consultation_fee_cents ? auth.user?.doctor?.consultation_fee_cents / 100 : (auth.user?.doctor?.consultation_fee || 120)).toFixed(2) }}</span>
          </div>

          <div class="pt-2">
            <router-link
              to="/doctor/schedule"
              class="w-full py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase text-center block border border-slate-950"
            >
              Modify Working Schedule
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
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import CreateRecordModal from '@/components/doctor/CreateRecordModal.vue'
import CreatePrescriptionModal from '@/components/doctor/CreatePrescriptionModal.vue'
import {
  FilePlus,
  Pill,
  Video,
} from 'lucide-vue-next'
import { generateUniqueRoomCode } from '@/services/mockData'

const router = useRouter()
const auth = useAuthStore()
const appointmentStore = useAppointmentStore()

const loading = ref(false)
const showCreateRecordModal = ref(false)
const showCreateRxModal = ref(false)
const selectedAppt = ref(null)
const joinRoomCode = ref('')

const joinByCode = () => {
  if (!joinRoomCode.value.trim()) return
  const clean = joinRoomCode.value.trim().replace(/^#/, '')
  router.push(`/telehealth/room/${clean}`)
}

const createInstantMeeting = () => {
  const code = generateUniqueRoomCode()
  router.push(`/telehealth/room/${code}`)
}

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
