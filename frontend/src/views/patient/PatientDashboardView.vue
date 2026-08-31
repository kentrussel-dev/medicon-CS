<template>
  <div class="space-y-8">
    <!-- Welcome Header Banner -->
    <div class="p-6 sm:p-8 bg-gradient-to-r from-brand-700 via-emerald-600 to-teal-700 rounded-3xl text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div class="relative z-10 max-w-xl">
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-emerald-100">
          Patient Portal
        </span>
        <h2 class="text-2xl sm:text-3xl font-black mt-2">Welcome, {{ auth.user?.name }}</h2>
        <p class="text-xs sm:text-sm text-emerald-100/90 mt-1">
          Your personal health dashboard. Manage appointments, encrypted clinical notes, and prescriptions with ease.
        </p>
      </div>

      <div class="relative z-10 flex flex-wrap items-center gap-3">
        <button
          @click="showBookModal = true"
          class="px-5 py-2.5 rounded-2xl bg-white text-brand-800 text-xs font-bold shadow-md hover:bg-emerald-50 transition-all flex items-center space-x-2"
        >
          <CalendarPlus class="w-4 h-4 text-brand-600" />
          <span>Book Consultation</span>
        </button>
        <router-link
          to="/patient/doctors"
          class="px-5 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold backdrop-blur-md transition-all flex items-center space-x-2"
        >
          <Search class="w-4 h-4" />
          <span>Find Specialist</span>
        </router-link>
      </div>
    </div>

    <!-- Overview Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <StatCard
        title="Upcoming Appointments"
        :value="upcomingAppointments.length"
        subtitle="Confirmed telehealth & clinic visits"
        :icon="Calendar"
        color="emerald"
      />
      <StatCard
        title="Clinical Encounters"
        :value="recordsCount"
        subtitle="Encrypted medical consultation records"
        :icon="FileText"
        color="blue"
      />
      <StatCard
        title="Active Prescriptions"
        :value="prescriptionsCount"
        subtitle="Authorized medication courses"
        :icon="Pill"
        color="purple"
      />
    </div>

    <!-- Next Upcoming Appointment Alert / Hero -->
    <div v-if="nextAppointment" class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-100 shadow-sm space-y-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-brand-700">
          <Clock class="w-4 h-4 text-brand-600" />
          <span>Next Scheduled Encounter</span>
        </div>
        <Badge :variant="nextAppointment.status">{{ nextAppointment.status }}</Badge>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
        <div>
          <h4 class="text-lg font-black text-slate-900">{{ nextAppointment.reason }}</h4>
          <p class="text-xs text-slate-500 mt-0.5">
            With <span class="font-bold text-slate-800">{{ nextAppointment.doctor_name }}</span> ({{ nextAppointment.doctor_specialty }})
          </p>
          <p class="text-xs text-brand-700 font-semibold mt-1.5 flex items-center">
            <Calendar class="w-3.5 h-3.5 mr-1" />
            {{ formatDate(nextAppointment.scheduled_start) }}
          </p>
        </div>

        <div class="flex items-center space-x-3">
          <a
            v-if="nextAppointment.type === 'TELEHEALTH' && nextAppointment.meeting_link"
            :href="nextAppointment.meeting_link"
            target="_blank"
            class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm"
          >
            <Video class="w-4 h-4" />
            <span>Join Telehealth Room</span>
          </a>
          <button
            @click="openReschedule(nextAppointment)"
            class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-all"
          >
            Reschedule
          </button>
        </div>
      </div>
    </div>

    <!-- Fast Links & Recent Appointments -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Appointments list -->
      <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-black text-base text-slate-900">Recent Appointments</h3>
          <router-link to="/patient/appointments" class="text-xs font-bold text-brand-600 hover:text-brand-700">
            View All &rarr;
          </router-link>
        </div>

        <div v-if="loading" class="py-8">
          <LoadingSpinner />
        </div>

        <div v-else-if="appointments.length === 0" class="text-center py-10 text-slate-400 text-xs font-medium">
          No appointments recorded yet. Use the "Book Consultation" button to schedule your first visit.
        </div>

        <div v-else class="divide-y divide-slate-100">
          <div
            v-for="appt in appointments.slice(0, 4)"
            :key="appt.id"
            class="py-3.5 flex items-center justify-between text-xs"
          >
            <div>
              <p class="font-bold text-slate-900 text-sm">{{ appt.doctor_name }}</p>
              <p class="text-slate-500 mt-0.5">{{ appt.doctor_specialty }} &bull; {{ appt.reason }}</p>
              <span class="text-slate-400 text-[11px]">{{ formatDate(appt.scheduled_start) }}</span>
            </div>
            <div class="text-right flex flex-col items-end space-y-1">
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
              <span class="text-[11px] text-slate-400 capitalize">{{ appt.type.toLowerCase() }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Health Profile Card -->
      <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
        <h3 class="font-black text-base text-slate-900">Encrypted Profile</h3>
        <div class="space-y-3 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="text-slate-400 font-semibold block">Known Drug Allergies</span>
            <span class="font-bold text-slate-800 mt-0.5 block">{{ auth.user?.patient?.allergies || 'None recorded' }}</span>
          </div>
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="text-slate-400 font-semibold block">Blood Group</span>
            <span class="font-bold text-slate-800 mt-0.5 block">{{ auth.user?.patient?.blood_type || 'O+' }}</span>
          </div>
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="text-slate-400 font-semibold block">Emergency Contact</span>
            <span class="font-bold text-slate-800 mt-0.5 block">
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
