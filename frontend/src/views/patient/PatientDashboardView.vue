<template>
  <div class="space-y-5">
    <!-- Top Minimalist Header Bar -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-up animate-enter-1">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Patient Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Health Record #{{ auth.user?.patient?.id || 1 }}</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">{{ auth.user?.name }}</h1>
      </div>

      <div class="flex items-center space-x-2">
        <button
          @click="showBookModal = true"
          class="px-3.5 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold uppercase tracking-wider border border-brand-800 transition-colors flex items-center space-x-1.5"
        >
          <CalendarPlus class="w-3.5 h-3.5" />
          <span>Book Appointment</span>
        </button>
        <router-link
          to="/patient/doctors"
          class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors flex items-center space-x-1.5"
        >
          <Search class="w-3.5 h-3.5" />
          <span>Doctors Directory</span>
        </router-link>
      </div>
    </div>

    <!-- Quick Join / Instant Telehealth Consultation Banner -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs animate-fade-up animate-enter-2">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-brand-50 border border-brand-200 text-brand-700 rounded-lg flex items-center justify-center shrink-0">
          <Video class="w-5 h-5" />
        </div>
        <div>
          <h4 class="font-bold text-sm text-slate-900 uppercase">Instant Telehealth Consultation</h4>
          <p class="text-xs text-slate-600 font-sans">Start an instant room or enter a unique code (e.g. <code class="font-mono text-brand-700 font-bold">sdf-sdyy-125</code>) to join immediately.</p>
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
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Upcoming Consultations</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ upcomingAppointments.length }}</span>
      </div>
      <div class="bg-white border border-slate-300 p-4 hover:border-slate-400 transition-colors">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Clinical Records</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ recordsCount }}</span>
      </div>
      <div class="bg-white border border-slate-300 p-4 hover:border-slate-400 transition-colors">
        <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">Active Prescriptions</span>
        <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ prescriptionsCount }}</span>
      </div>
    </div>

    <!-- Next Scheduled Appointment -->
    <div v-if="nextAppointment" class="bg-white border border-slate-300 animate-fade-up animate-enter-4">
      <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs font-bold uppercase">
        <span class="text-slate-800">Next Scheduled Visit</span>
        <Badge :variant="nextAppointment.status">{{ nextAppointment.status }}</Badge>
      </div>

      <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div>
          <h4 class="font-bold text-sm text-slate-950 uppercase">{{ nextAppointment.reason }}</h4>
          <p class="text-slate-600 mt-0.5">
            Doctor: <span class="font-bold text-slate-900">{{ nextAppointment.doctor_name }}</span> ({{ nextAppointment.doctor_specialty }})
          </p>
          <p class="text-slate-500 font-mono mt-1">
            Time: {{ formatDate(nextAppointment.scheduled_start) }}
          </p>
        </div>

        <div class="flex items-center space-x-2">
          <router-link
            v-if="nextAppointment.type === 'TELEHEALTH'"
            :to="'/telehealth/room/' + nextAppointment.id"
            class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold uppercase text-[11px] border border-emerald-800 flex items-center space-x-1"
          >
            <Video class="w-3.5 h-3.5" />
            <span>Join Room</span>
          </router-link>
          <button
            @click="openReschedule(nextAppointment)"
            class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-bold uppercase text-[11px]"
          >
            Reschedule
          </button>
        </div>
      </div>
    </div>

    <!-- Two-Column Structured Clinical Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 animate-fade-up animate-enter-5">
      <!-- Recent Appointments Table -->
      <div class="lg:col-span-2 bg-white border border-slate-300">
        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
          <span class="font-bold uppercase tracking-wider text-slate-800">Recent Appointments</span>
          <router-link to="/patient/appointments" class="font-mono font-bold text-brand-600 hover:underline uppercase">
            View All &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="p-8">
          <LoadingSpinner />
        </div>

        <div v-else-if="appointments.length === 0" class="p-6 text-center text-slate-500 text-xs font-mono">
          No appointments recorded.
        </div>

        <div v-else class="divide-y divide-slate-200">
          <div
            v-for="appt in appointments.slice(0, 4)"
            :key="appt.id"
            class="p-3.5 flex items-center justify-between text-xs hover:bg-slate-50 transition-colors"
          >
            <div>
              <p class="font-bold text-slate-900 uppercase">{{ appt.doctor_name }} &bull; {{ appt.doctor_specialty }}</p>
              <p class="text-slate-600 mt-0.5">{{ appt.reason }}</p>
              <span class="text-slate-400 text-[11px] font-mono">{{ formatDate(appt.scheduled_start) }}</span>
            </div>
            <div class="text-right flex flex-col items-end space-y-1">
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
              <span class="text-[10px] font-mono text-slate-400 uppercase">{{ appt.type }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Medical Profile Card -->
      <div class="bg-white border border-slate-300">
        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-800">
          Clinical Profile
        </div>
        <div class="p-3.5 space-y-2.5 text-xs font-mono">
          <div class="p-2.5 bg-slate-50 border border-slate-200">
            <span class="text-slate-400 text-[10px] uppercase block">Drug Allergies</span>
            <span class="font-bold text-slate-900 mt-0.5 block">{{ auth.user?.patient?.allergies || 'None recorded' }}</span>
          </div>
          <div class="p-2.5 bg-slate-50 border border-slate-200">
            <span class="text-slate-400 text-[10px] uppercase block">Blood Group</span>
            <span class="font-bold text-slate-900 mt-0.5 block">{{ auth.user?.patient?.blood_type || 'O+' }}</span>
          </div>
          <div class="p-2.5 bg-slate-50 border border-slate-200">
            <span class="text-slate-400 text-[10px] uppercase block">Emergency Contact</span>
            <span class="font-bold text-slate-900 mt-0.5 block">
              {{ auth.user?.patient?.emergency_contact_name || 'Primary Contact' }} ({{ auth.user?.patient?.emergency_contact_phone || '+1 555-0199' }})
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <BookAppointmentModal
      :is-open="showBookModal"
      @close="showBookModal = false"
      @booked="loadData"
    />

    <!-- Reschedule Modal -->
    <RescheduleModal
      :is-open="showRescheduleModal"
      :appointment="selectedAppointment"
      @close="showRescheduleModal = false"
      @updated="loadData"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppointmentStore } from '@/stores/appointments'
import { useRecordStore } from '@/stores/records'
import { usePrescriptionStore } from '@/stores/prescriptions'
import Badge from '@/components/common/Badge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import RescheduleModal from '@/components/patient/RescheduleModal.vue'
import {
  CalendarPlus,
  Search,
  Video,
} from 'lucide-vue-next'

import { generateUniqueRoomCode } from '@/services/mockData'

const router = useRouter()
const auth = useAuthStore()
const appointmentStore = useAppointmentStore()
const recordStore = useRecordStore()
const prescriptionStore = usePrescriptionStore()

const showBookModal = ref(false)
const showRescheduleModal = ref(false)
const selectedAppointment = ref(null)
const loading = ref(false)
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
const upcomingAppointments = computed(() =>
  appointments.value.filter((a) => a.status === 'CONFIRMED' || a.status === 'PENDING')
)

const nextAppointment = computed(() => {
  return upcomingAppointments.value[0] || null
})

const recordsCount = ref(2)
const prescriptionsCount = ref(1)

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const openReschedule = (appt) => {
  selectedAppointment.value = appt
  showRescheduleModal.value = true
}

const loadData = async () => {
  loading.value = true
  try {
    await appointmentStore.fetchAppointments()
    const recs = await recordStore.fetchRecords()
    recordsCount.value = recs.length
    const rx = await prescriptionStore.fetchPrescriptions()
    prescriptionsCount.value = rx.length
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
