<template>
  <div class="space-y-5">
    <!-- Header Bar -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Patient Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Appointments Schedule</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">Medical Appointments</h1>
      </div>

      <div class="flex items-center space-x-2">
        <select
          v-model="statusFilter"
          @change="loadAppointments"
          class="px-2.5 py-1.5 border border-slate-300 text-xs font-mono focus:border-slate-800 bg-white rounded-none uppercase"
        >
          <option value="">All Statuses</option>
          <option value="CONFIRMED">Confirmed</option>
          <option value="COMPLETED">Completed</option>
          <option value="CANCELLED">Cancelled</option>
        </select>

        <button
          @click="showBookModal = true"
          class="px-3.5 py-1.5 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold uppercase tracking-wider border border-brand-800 transition-colors flex items-center space-x-1.5"
        >
          <CalendarPlus class="w-3.5 h-3.5" />
          <span>New Appointment</span>
        </button>
      </div>
    </div>

    <!-- Appointments List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Loading appointment schedule..." />
    </div>

    <div v-else-if="appointments.length === 0" class="bg-white border border-slate-300 p-12 text-center">
      <p class="text-xs font-mono text-slate-500">No scheduled appointments found on record.</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="appt in appointments"
        :key="appt.id"
        class="bg-white border border-slate-300 p-4 hover:border-brand-600 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex items-start space-x-3">
          <div class="p-2.5 bg-slate-100 border border-slate-200 text-slate-700 flex-shrink-0">
            <component :is="appt.type === 'TELEHEALTH' ? Video : Building2" class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="font-bold text-slate-950 text-sm uppercase">{{ appt.doctor_name }}</h3>
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
            </div>
            <span class="text-[11px] font-mono font-bold text-brand-600 uppercase block mt-0.5">{{ appt.doctor_specialty }}</span>
            <p class="text-xs text-slate-700 mt-1.5">{{ appt.reason }}</p>

            <div class="flex items-center space-x-4 mt-2 text-xs font-mono text-slate-500">
              <span class="flex items-center">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                {{ formatDate(appt.scheduled_start) }}
              </span>
              <span class="uppercase">Type: {{ appt.type }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start md:self-auto border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
          <router-link
            v-if="appt.type === 'TELEHEALTH' && appt.status === 'CONFIRMED'"
            :to="'/telehealth/room/' + appt.id"
            class="px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider border border-emerald-800 transition-colors flex items-center space-x-1"
          >
            <Video class="w-3.5 h-3.5" />
            <span>Join Room</span>
          </router-link>

          <button
            v-if="appt.status === 'CONFIRMED' || appt.status === 'PENDING'"
            @click="openReschedule(appt)"
            class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-bold uppercase tracking-wider"
          >
            Reschedule
          </button>

          <button
            v-if="appt.status === 'CONFIRMED' || appt.status === 'PENDING'"
            @click="openCancel(appt)"
            class="px-3 py-1.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 text-xs font-bold uppercase tracking-wider"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <BookAppointmentModal
      :is-open="showBookModal"
      @close="showBookModal = false"
      @booked="loadAppointments"
    />

    <RescheduleModal
      :is-open="showRescheduleModal"
      :appointment="selectedAppointment"
      @close="showRescheduleModal = false"
      @updated="loadAppointments"
    />

    <CancelModal
      :is-open="showCancelModal"
      :appointment="selectedAppointment"
      @close="showCancelModal = false"
      @cancelled="loadAppointments"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import RescheduleModal from '@/components/patient/RescheduleModal.vue'
import CancelModal from '@/components/patient/CancelModal.vue'
import {
  Calendar,
  CalendarPlus,
  Video,
  Building2,
} from 'lucide-vue-next'

const appointmentStore = useAppointmentStore()

const appointments = ref([])
const statusFilter = ref('')
const loading = ref(false)

const showBookModal = ref(false)
const showRescheduleModal = ref(false)
const showCancelModal = ref(false)
const selectedAppointment = ref(null)

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

const openCancel = (appt) => {
  selectedAppointment.value = appt
  showCancelModal.value = true
}

const loadAppointments = async () => {
  loading.value = true
  try {
    const data = await appointmentStore.fetchAppointments({
      status: statusFilter.value || undefined,
    })
    appointments.value = data
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAppointments()
})
</script>
