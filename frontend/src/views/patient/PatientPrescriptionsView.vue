<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">My Prescriptions</h2>
        <p class="text-xs text-slate-500 mt-0.5">Active medication regimens, dosage instructions, and dispensing history</p>
      </div>
    </div>

    <!-- Prescriptions List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Loading electronic prescriptions..." />
    </div>

    <div v-else-if="prescriptions.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-100">
      <Pill class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <h4 class="font-bold text-slate-700 text-sm">No Active Prescriptions</h4>
      <p class="text-xs text-slate-400 mt-1">Prescriptions issued by your consulting doctor will be listed here.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="rx in prescriptions"
        :key="rx.id"
        class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-6"
      >
        <div class="flex items-start space-x-4">
          <div class="p-3.5 rounded-2xl bg-purple-50 text-purple-700 flex-shrink-0">
            <Pill class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center space-x-2.5">
              <h4 class="font-bold text-slate-900 text-base">
                {{ rx.items?.[0]?.medication_name || 'Prescription Course' }}
              </h4>
              <Badge :variant="rx.is_dispensed ? 'success' : 'pending'">
                {{ rx.is_dispensed ? 'Dispensed' : 'Active' }}
              </Badge>
            </div>
            <p class="text-xs font-semibold text-brand-600 mt-0.5">Prescribed by Dr. {{ rx.doctor_name }}</p>

            <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-slate-500">
              <span class="flex items-center">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                Valid Until: {{ rx.valid_until }}
              </span>
              <span class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded font-semibold text-[11px]">
                {{ rx.items?.length || 1 }} Medication(s)
              </span>
            </div>
          </div>
        </div>

        <button
          @click="openPrescription(rx)"
          class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5 self-start sm:self-center"
        >
          <Eye class="w-4 h-4" />
          <span>View Prescription Form</span>
        </button>
      </div>
    </div>

    <!-- Modal -->
    <PrescriptionDetailsModal
      :is-open="showRxModal"
      :prescription="selectedPrescription"
      @close="showRxModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePrescriptionStore } from '@/stores/prescriptions'
import Badge from '@/components/common/Badge.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import PrescriptionDetailsModal from '@/components/patient/PrescriptionDetailsModal.vue'
import { Pill, Calendar, Eye } from 'lucide-vue-next'

const prescriptionStore = usePrescriptionStore()
const loading = ref(false)
const showRxModal = ref(false)
const selectedPrescription = ref(null)

const prescriptions = computed(() => prescriptionStore.prescriptions)

const openPrescription = (rx) => {
  selectedPrescription.value = rx
  showRxModal.value = true
}

onMounted(async () => {
  loading.value = true
  try {
    await prescriptionStore.fetchPrescriptions()
  } finally {
    loading.value = false
  }
})
</script>
