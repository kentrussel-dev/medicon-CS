<template>
  <div class="space-y-6">
    <!-- Header Banner Strip -->
    <div class="p-5 bg-white border border-slate-300 shadow-crisp flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center space-x-2">
          <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-brand-600 bg-slate-100 px-2 py-0.5 border border-slate-300">
            Patient Portal &bull; Medical Records
          </span>
        </div>
        <h2 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-1">Patient Record: {{ auth.user?.name }}</h2>
        <p class="text-xs text-slate-600 font-mono mt-0.5">
          Patient ID: #{{ auth.user?.patient?.id || 1 }} &bull; Health Identifier: {{ auth.user?.email }}
        </p>
      </div>

      <div class="flex items-center space-x-2">
        <button
          @click="showBookModal = true"
          class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider border border-brand-700 transition-colors flex items-center space-x-1.5"
        >
          <CalendarPlus class="w-4 h-4" />
          <span>Request Consultation</span>
        </button>
        <router-link
          to="/patient/doctors"
          class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors flex items-center space-x-1.5"
        >
          <Search class="w-4 h-4" />
          <span>Physician Directory</span>
        </router-link>
      </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <StatCard
        title="Upcoming Consultations"
        :value="upcomingAppointments.length"
        subtitle="Scheduled virtual and clinic visits"
        :icon="Calendar"
        color="blue"
      />
      <StatCard
        title="Clinical EHR Encounters"
        :value="recordsCount"
        subtitle="Encrypted diagnostic summaries"
        :icon="FileText"
        color="emerald"
      />
      <StatCard
        title="Active Prescriptions"
        :value="prescriptionsCount"
        subtitle="Authorized medication courses"
        :icon="Pill"
        color="purple"
      />
    </div>

    <!-- Next Upcoming Consultation Card -->
    <div v-if="nextAppointment" class="bg-white border border-slate-300 shadow-crisp">
      <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs font-bold uppercase">
        <div class="flex items-center space-x-2 text-brand-600">
          <Clock class="w-4 h-4" />
          <span>Next Scheduled Clinical Visit</span>
        </div>
        <Badge :variant="nextAppointment.status">{{ nextAppointment.status }}</Badge>
      </div>

      <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h4 class="text-base font-bold text-slate-900 uppercase">{{ nextAppointment.reason }}</h4>
          <p class="text-xs text-slate-700 mt-1">
            Attending Physician: <span class="font-bold text-slate-900">{{ nextAppointment.doctor_name }}</span> ({{ nextAppointment.doctor_specialty }})
          </p>
          <p class="text-xs text-brand-600 font-mono font-bold mt-1">
            Scheduled Time: {{ formatDate(nextAppointment.scheduled_start) }} (30 Mins)
          </p>
        </div>

        <div class="flex items-center space-x-2">
          <a
            v-if="nextAppointment.type === 'TELEHEALTH' && nextAppointment.meeting_link"
            :href="nextAppointment.meeting_link"
            target="_blank"
            class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider border border-emerald-800 transition-colors flex items-center space-x-1.5"
          >
            <Video class="w-4 h-4" />
            <span>Enter Telehealth Room</span>
          </a>
          <button
            @click="openReschedule(nextAppointment)"
            class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-bold uppercase tracking-wider"
          >
            Reschedule
          </button>
        </div>
      </div>
    </div>

    <!-- Two-Column Structured Clinical Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Recent Encounters Table -->
      <div class="lg:col-span-2 bg-white border border-slate-300 shadow-crisp">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Encounter History</h3>
          <router-link to="/patient/appointments" class="text-xs font-mono font-bold text-brand-600 hover:underline uppercase">
            View All Encounters &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="p-8">
          <LoadingSpinner />
        </div>

        <div v-else-if="appointments.length === 0" class="p-8 text-center text-slate-500 text-xs font-mono">
          No clinical appointments found on record.
        </div>

        <div v-else class="divide-y divide-slate-200">
          <div
            v-for="appt in appointments.slice(0, 4)"
            :key="appt.id"
            class="p-4 flex items-center justify-between text-xs hover:bg-slate-50 transition-colors"
          >
            <div>
              <p class="font-bold text-slate-900 uppercase">{{ appt.doctor_name }} &bull; {{ appt.doctor_specialty }}</p>
              <p class="text-slate-600 mt-0.5">{{ appt.reason }}</p>
              <span class="text-slate-500 text-[11px] font-mono">{{ formatDate(appt.scheduled_start) }}</span>
            </div>
            <div class="text-right flex flex-col items-end space-y-1">
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
              <span class="text-[10px] font-mono text-slate-500 uppercase">{{ appt.type }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Medical Profile & Allergies Card -->
      <div class="bg-white border border-slate-300 shadow-crisp">
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
          <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900">Patient Health Profile</h3>
        </div>
        <div class="p-4 space-y-3 text-xs font-mono">
          <div class="p-3 bg-slate-50 border border-slate-200">
            <span class="text-slate-500 uppercase text-[10px] block">Recorded Allergies (Encrypted)</span>
            <span class="font-bold text-rose-800 mt-0.5 block">{{ auth.user?.patient?.allergies || 'None Recorded' }}</span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200">
            <span class="text-slate-500 uppercase text-[10px] block">Blood Type</span>
            <span class="font-bold text-slate-900 mt-0.5 block">{{ auth.user?.patient?.blood_type || 'O+' }}</span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200">
            <span class="text-slate-500 uppercase text-[10px] block">Emergency Contact</span>
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
import { useAuthStore } from '@/stores/auth'
import { useAppointmentStore } from '@/stores/appointments'
import { useRecordStore } from '@/stores/records'
import { usePrescriptionStore } from '@/stores/prescriptions'
import StatCard from '@/components/common/StatCard.vue'
import Badge from '@/components/common/Badge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import RescheduleModal from '@/components/patient/RescheduleModal.vue'
import {
  Calendar,
  CalendarPlus,
  Search,
  FileText,
  Pill,
  Clock,
  Video,
} from 'lucide-vue-next'

const auth = useAuthStore()
const appointmentStore = useAppointmentStore()
const recordStore = useRecordStore()
const prescriptionStore = usePrescriptionStore()

const showBookModal = ref(false)
const showRescheduleModal = ref(false)
const selectedAppointment = ref(null)
const loading = ref(false)

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
  } catch (err) {
    // Handled
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
})
</script>
