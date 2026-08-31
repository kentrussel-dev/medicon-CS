<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Patient Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Medication Orders</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">Authorized Prescriptions</h1>
      </div>

      <div class="flex items-center space-x-2">
        <select
          v-model="statusFilter"
          @change="loadPrescriptions"
          class="px-2.5 py-1.5 border border-slate-300 text-xs font-mono focus:border-slate-800 bg-white rounded-none uppercase"
        >
          <option value="">All Prescriptions</option>
          <option value="ACTIVE">Active Courses</option>
          <option value="COMPLETED">Completed</option>
          <option value="CANCELLED">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Prescriptions List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Fetching active medication orders..." />
    </div>

    <div v-else-if="prescriptions.length === 0" class="bg-white border border-slate-300 p-12 text-center">
      <p class="text-xs font-mono text-slate-500">No prescriptions found on record.</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="rx in prescriptions"
        :key="rx.id"
        class="bg-white border border-slate-300 p-4 hover:border-brand-600 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex items-start space-x-3">
          <div class="p-2.5 bg-slate-100 border border-slate-200 text-slate-700 flex-shrink-0">
            <Pill class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center space-x-2">
              <h3 class="font-bold text-slate-950 text-sm uppercase">{{ rx.medication_name }} {{ rx.dosage }}</h3>
              <Badge :variant="rx.status">{{ rx.status }}</Badge>
            </div>
            <span class="text-[11px] font-mono font-bold text-slate-600 uppercase block mt-0.5">
              Prescribed by {{ rx.doctor_name }} ({{ rx.doctor_specialty }})
            </span>
            <p class="text-xs text-slate-700 mt-1.5">{{ rx.instructions }}</p>

            <div class="flex flex-wrap items-center gap-4 mt-2 text-xs font-mono text-slate-500">
              <span>Frequency: {{ rx.frequency }}</span>
              <span>Duration: {{ rx.duration }}</span>
              <span>Refills Remaining: {{ rx.refills_remaining }}</span>
            </div>
          </div>
        </div>

        <button
          @click="openDetails(rx)"
          class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase tracking-wider border border-slate-950 transition-colors self-start md:self-auto"
        >
          View Details
        </button>
      </div>
    </div>

    <!-- Details Modal -->
    <PrescriptionDetailsModal
      :is-open="showDetailsModal"
      :prescription="selectedRx"
      @close="showDetailsModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePrescriptionStore } from '@/stores/prescriptions'
import Badge from '@/components/common/Badge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import PrescriptionDetailsModal from '@/components/patient/PrescriptionDetailsModal.vue'
import { Pill } from 'lucide-vue-next'

const prescriptionStore = usePrescriptionStore()

const prescriptions = ref([])
const statusFilter = ref('')
const loading = ref(false)
const showDetailsModal = ref(false)
const selectedRx = ref(null)

const openDetails = (rx) => {
  selectedRx.value = rx
  showDetailsModal.value = true
}

const loadPrescriptions = async () => {
  loading.value = true
  try {
    const data = await prescriptionStore.fetchPrescriptions({
      status: statusFilter.value || undefined,
    })
    prescriptions.value = data
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadPrescriptions()
})
</script>
