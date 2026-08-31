<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Doctor Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Clinical Calendar</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">Patient Appointments & Encounters</h1>
      </div>

      <div class="flex items-center space-x-2">
        <select
          v-model="statusFilter"
          @change="loadAppointments"
          class="px-2.5 py-1.5 border border-slate-300 text-xs font-mono focus:border-slate-800 bg-white rounded-none uppercase"
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

    <div v-else-if="appointments.length === 0" class="bg-white border border-slate-300 p-12 text-center">
      <p class="text-xs font-mono text-slate-500">No appointments match the selected filter criteria.</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="appt in appointments"
        :key="appt.id"
        class="bg-white border border-slate-300 p-4 hover:border-brand-600 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-4"
      >
        <div class="flex items-start space-x-3">
          <div class="p-2.5 bg-slate-100 border border-slate-200 text-slate-700 flex-shrink-0">
            <component :is="appt.type === 'TELEHEALTH' ? Video : Building2" class="w-5 h-5" />
          </div>
          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="font-bold text-slate-950 text-sm uppercase">{{ appt.patient_name }}</h3>
              <Badge :variant="appt.status">{{ appt.status }}</Badge>
              <RiskBadge
                v-if="appt.no_show_risk_level"
                :level="appt.no_show_risk_level"
                :score="appt.no_show_risk_score"
              />
            </div>
            <p class="text-xs text-slate-700 mt-1">{{ appt.reason }}</p>

            <div class="flex flex-wrap items-center gap-4 mt-2 text-xs font-mono text-slate-500">
              <span class="flex items-center">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                {{ formatDate(appt.scheduled_start) }}
              </span>
              <span class="uppercase">Type: {{ appt.type }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-t lg:border-t-0 pt-3 lg:pt-0 border-slate-100 font-mono text-xs">
          <router-link
            v-if="appt.type === 'TELEHEALTH'"
            :to="'/telehealth/room/' + appt.id"
            class="px-2.5 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold uppercase text-[11px] border border-emerald-800 flex items-center space-x-1"
          >
            <Video class="w-3 h-3" />
            <span>Join Video</span>
          </router-link>

          <select
            :value="appt.status"
            @change="handleStatusChange(appt, $event.target.value)"
            class="px-2 py-1 border border-slate-300 bg-white text-xs uppercase font-mono"
          >
            <option value="PENDING">PENDING</option>
            <option value="CONFIRMED">CONFIRMED</option>
            <option value="IN_PROGRESS">IN_PROGRESS</option>
            <option value="COMPLETED">COMPLETED</option>
            <option value="NO_SHOW">NO_SHOW</option>
            <option value="CANCELLED">CANCELLED</option>
          </select>

          <button
            @click="openCreateRecord(appt)"
            class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold uppercase text-[11px] border border-slate-950 flex items-center space-x-1"
          >
            <FilePlus class="w-3 h-3" />
            <span>Record EHR</span>
          </button>

          <button
            @click="openCreateRx(appt)"
            class="px-2.5 py-1.5 bg-brand-700 hover:bg-brand-800 text-white font-bold uppercase text-[11px] border border-brand-800 flex items-center space-x-1"
          >
            <Pill class="w-3 h-3" />
            <span>Prescribe</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CreateRecordModal
      :is-open="showCreateRecordModal"
      :prefill-patient-id="selectedAppt?.patient_id"
      :prefill-appointment-id="selectedAppt?.id"
      @close="showCreateRecordModal = false"
      @created="loadAppointments"
    />

    <CreatePrescriptionModal
      :is-open="showCreateRxModal"
      :prefill-patient-id="selectedAppt?.patient_id"
      :prefill-appointment-id="selectedAppt?.id"
      @close="showCreateRxModal = false"
      @created="loadAppointments"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAppointmentStore } from '@/stores/appointments'
import Badge from '@/components/common/Badge.vue'
import RiskBadge from '@/components/common/RiskBadge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import CreateRecordModal from '@/components/doctor/CreateRecordModal.vue'
import CreatePrescriptionModal from '@/components/doctor/CreatePrescriptionModal.vue'
import {
  Calendar,
  Video,
  Building2,
  FilePlus,
  Pill,
} from 'lucide-vue-next'

const appointmentStore = useAppointmentStore()

const appointments = ref([])
const statusFilter = ref('')
const loading = ref(false)

const showCreateRecordModal = ref(false)
const showCreateRxModal = ref(false)
const selectedAppt = ref(null)

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const openCreateRecord = (appt) => {
  selectedAppt.value = appt
  showCreateRecordModal.value = true
}

const openCreateRx = (appt) => {
  selectedAppt.value = appt
  showCreateRxModal.value = true
}

const handleStatusChange = async (appt, newStatus) => {
  await appointmentStore.updateStatus(appt.id, newStatus)
  await loadAppointments()
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
