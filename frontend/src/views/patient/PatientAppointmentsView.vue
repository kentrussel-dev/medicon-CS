<template>
  <div class="space-y-6">
    <!-- Header Bar -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">My Medical Appointments</h2>
        <p class="text-xs text-slate-500 mt-0.5">Manage scheduled telemedicine consultations and in-clinic visits</p>
      </div>

      <div class="flex items-center space-x-3">
        <select
          v-model="statusFilter"
          @change="loadAppointments"
          class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-500 bg-white"
        >
          <option value="">All Statuses</option>
          <option value="CONFIRMED">Confirmed</option>
          <option value="COMPLETED">Completed</option>
          <option value="CANCELLED">Cancelled</option>
        </select>

        <button
          @click="showBookModal = true"
          class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5"
        >
          <CalendarPlus class="w-4 h-4" />
          <span>New Appointment</span>
        </button>
      </div>
    </div>

    <!-- Appointments List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Loading appointment schedule..." />
    </div>

    <div v-else-if="appointments.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-100">
      <CalendarX class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <h4 class="font-bold text-slate-700 text-sm">No Appointments Found</h4>
      <p class="text-xs text-slate-400 mt-1">You do not have any scheduled appointments matching the current filter.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="appt in appointments"
        :key="appt.id"
        class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-6"
      >
        <div class="flex items-start space-x-4">
          <div class="p-3.5 rounded-2xl bg-brand-50 text-brand-700 flex-shrink-0">
            <component :is="appt.type === 'TELEHEALTH' ? Video : Building2" class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center space-x-2.5">
              <h4 class="font-bold text-slate-900 text-base">{{ appt.doctor_name }}</h4>
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
            </div>
            <p class="text-xs font-semibold text-brand-600 mt-0.5">{{ appt.doctor_specialty }}</p>
            <p class="text-xs text-slate-600 mt-2 font-medium">{{ appt.reason }}</p>

            <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-slate-500">
              <span class="flex items-center font-bold text-slate-800">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                {{ formatDate(appt.scheduled_start) }}
              </span>
              <span class="flex items-center">
                <Clock class="w-3.5 h-3.5 mr-1 text-slate-400" />
                30 Minutes Duration
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center gap-2.5 sm:self-center">
          <a
            v-if="appt.type === 'TELEHEALTH' && appt.status === 'CONFIRMED' && appt.meeting_link"
            :href="appt.meeting_link"
            target="_blank"
            class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5"
          >
            <Video class="w-3.5 h-3.5" />
            <span>Join Call</span>
          </a>

          <button
            v-if="appt.status === 'CONFIRMED' || appt.status === 'PENDING'"
            @click="openReschedule(appt)"
            class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all"
          >
            Reschedule
          </button>

          <button
            v-if="appt.status === 'CONFIRMED' || appt.status === 'PENDING'"
            @click="openCancel(appt)"
            class="px-3 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-all"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <BookAppointmentModal :is-open="showBookModal" @close="showBookModal = false" @booked="loadAppointments" />
    <RescheduleModal :is-open="showRescheduleModal" :appointment="selectedAppointment" @close="showRescheduleModal = false" @updated="loadAppointments" />
    <CancelModal :is-open="showCancelModal" :appointment="selectedAppointment" @close="showCancelModal = false" @cancelled="loadAppointments" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import RescheduleModal from '@/components/patient/RescheduleModal.vue'
import CancelModal from '@/components/patient/CancelModal.vue'
import { Calendar, CalendarPlus, CalendarX, Clock, Video, Building2 } from 'lucide-vue-next'

const appointmentStore = useAppointmentStore()
const loading = ref(false)
const statusFilter = ref('')

const showBookModal = ref(false)
const showRescheduleModal = ref(false)
const showCancelModal = ref(false)
const selectedAppointment = ref(null)

const appointments = computed(() => appointmentStore.appointments)

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
    await appointmentStore.fetchAppointments({
      status: statusFilter.value || undefined,
    })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAppointments()
})
</script>
