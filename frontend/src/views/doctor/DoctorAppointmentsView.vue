<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">Patient Appointments & Encounters</h2>
        <p class="text-xs text-slate-500 mt-0.5">Manage visit statuses, ML risk predictions, and post-visit documentation</p>
      </div>

      <div class="flex items-center space-x-3">
        <select
          v-model="statusFilter"
          @change="loadAppointments"
          class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-brand-500 bg-white"
        >
          <option value="">All Statuses</option>
          <option value="CONFIRMED">Confirmed</option>
          <option value="IN_PROGRESS">In Progress</option>
          <option value="COMPLETED">Completed</option>
          <option value="NO_SHOW">No-Show</option>
          <option value="CANCELLED">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Appointments List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Fetching clinical schedule..." />
    </div>

    <div v-else-if="appointments.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-100">
      <CalendarX class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <h4 class="font-bold text-slate-700 text-sm">No Appointments Found</h4>
      <p class="text-xs text-slate-400 mt-1">No appointments match the selected filter criteria.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="appt in appointments"
        :key="appt.id"
        class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-6"
      >
        <div class="flex items-start space-x-4">
          <div class="p-3.5 rounded-2xl bg-indigo-50 text-indigo-700 flex-shrink-0">
            <component :is="appt.type === 'TELEHEALTH' ? Video : Building2" class="w-6 h-6" />
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h4 class="font-bold text-slate-900 text-base">{{ appt.patient_name }}</h4>
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
              <RiskBadge
                v-if="appt.no_show_risk_level"
                :level="appt.no_show_risk_level"
                :score="appt.no_show_risk_score"
              />
            </div>
            <p class="text-xs text-slate-600 mt-1 font-medium">{{ appt.reason }}</p>
            <p v-if="appt.notes" class="text-xs text-slate-400 mt-0.5 italic">Patient Note: {{ appt.notes }}</p>

            <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-slate-500">
              <span class="flex items-center font-bold text-slate-800">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                {{ formatDate(appt.scheduled_start) }}
              </span>
              <span class="flex items-center">
                <Clock class="w-3.5 h-3.5 mr-1 text-slate-400" />
                30 Mins
              </span>
              <span class="text-slate-400 font-semibold">
                Patient ID: #{{ appt.patient_id }}
              </span>
            </div>
          </div>
        </div>

        <!-- Doctor Workflow Actions -->
        <div class="flex flex-wrap items-center gap-2 lg:self-center">
          <a
            v-if="appt.type === 'TELEHEALTH' && appt.meeting_link && appt.status !== 'COMPLETED' && appt.status !== 'CANCELLED'"
            :href="appt.meeting_link"
            target="_blank"
            class="px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5"
          >
            <Video class="w-3.5 h-3.5" />
            <span>Join Telehealth</span>
          </a>

          <!-- Status Transition Actions -->
          <button
            v-if="appt.status === 'CONFIRMED'"
            @click="updateStatus(appt.id, 'IN_PROGRESS')"
            class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-xs font-bold transition-all"
          >
            Start Visit
          </button>

          <button
            v-if="appt.status === 'IN_PROGRESS' || appt.status === 'CONFIRMED'"
            @click="updateStatus(appt.id, 'COMPLETED')"
            class="px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition-all"
          >
            Mark Completed
          </button>

          <button
            v-if="appt.status === 'CONFIRMED'"
            @click="updateStatus(appt.id, 'NO_SHOW')"
            class="px-3 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition-all"
          >
            Mark No-Show
          </button>

          <!-- Document Encounter -->
          <button
            @click="openCreateRecord(appt)"
            class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all"
          >
            + Document Record
          </button>

          <button
            @click="openCreateRx(appt)"
            class="px-3 py-2 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-bold transition-all"
          >
            + Rx
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CreateRecordModal
      :is-open="showRecordModal"
      :prefill-patient-id="selectedAppt?.patient_id"
      :prefill-appointment-id="selectedAppt?.id"
      @close="showRecordModal = false"
      @created="loadAppointments"
    />

    <CreatePrescriptionModal
      :is-open="showRxModal"
      :prefill-patient-id="selectedAppt?.patient_id"
      :prefill-appointment-id="selectedAppt?.id"
      @close="showRxModal = false"
      @created="loadAppointments"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import CreateRecordModal from '@/components/doctor/CreateRecordModal.vue'
import CreatePrescriptionModal from '@/components/doctor/CreatePrescriptionModal.vue'
import { Calendar, CalendarX, Clock, Video, Building2 } from 'lucide-vue-next'

const appointmentStore = useAppointmentStore()
const loading = ref(false)
const statusFilter = ref('')

const showRecordModal = ref(false)
const showRxModal = ref(false)
const selectedAppt = ref(null)

const appointments = computed(() => appointmentStore.appointments)

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const updateStatus = async (id, status) => {
  await appointmentStore.updateStatus(id, status)
}

const openCreateRecord = (appt) => {
  selectedAppt.value = appt
  showRecordModal.value = true
}

const openCreateRx = (appt) => {
  selectedAppt.value = appt
  showRxModal.value = true
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
