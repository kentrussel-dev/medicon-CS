<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Patient Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">EHR Diagnostic Records</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">Clinical Encounters & Notes</h1>
      </div>

      <div class="flex items-center space-x-1.5 text-xs font-mono text-emerald-800 bg-emerald-50 px-2.5 py-1 border border-emerald-300">
        <Lock class="w-3.5 h-3.5 text-emerald-700" />
        <span>AES-256 ENCRYPTED</span>
      </div>
    </div>

    <!-- Records List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Decrypting and loading health records..." />
    </div>

    <div v-else-if="records.length === 0" class="bg-white border border-slate-300 p-12 text-center">
      <p class="text-xs font-mono text-slate-500">No clinical records documented on record.</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="record in records"
        :key="record.id"
        class="bg-white border border-slate-300 p-4 hover:border-brand-600 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
      >
        <div class="flex items-start space-x-3">
          <div class="p-2.5 bg-slate-100 border border-slate-200 text-slate-700 flex-shrink-0">
            <Stethoscope class="w-5 h-5" />
          </div>
          <div>
            <h3 class="font-bold text-slate-950 text-sm uppercase">{{ record.diagnosis }}</h3>
            <span class="text-[11px] font-mono font-bold text-brand-600 uppercase block mt-0.5">
              {{ record.doctor_name }} &bull; {{ record.doctor_specialty }}
            </span>

            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs font-mono text-slate-600">
              <span class="flex items-center text-slate-500">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                {{ record.created_at ? new Date(record.created_at).toLocaleDateString('en-US', { dateStyle: 'medium' }) : 'Recent Encounter' }}
              </span>
              <span v-if="record.vital_signs?.blood_pressure" class="p-1 bg-slate-50 border border-slate-200 text-[10px]">
                BP: {{ record.vital_signs.blood_pressure }}
              </span>
              <span v-if="record.vital_signs?.heart_rate" class="p-1 bg-slate-50 border border-slate-200 text-[10px]">
                HR: {{ record.vital_signs.heart_rate }}
              </span>
            </div>
          </div>
        </div>

        <button
          @click="openRecordDetails(record)"
          class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-mono font-bold text-xs uppercase tracking-wider border border-slate-950 transition-colors self-start sm:self-auto"
        >
          View EHR Record
        </button>
      </div>
    </div>

    <!-- Record Details Modal -->
    <RecordDetailsModal
      :is-open="showDetailsModal"
      :record="selectedRecord"
      @close="showDetailsModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRecordStore } from '@/stores/records'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import RecordDetailsModal from '@/components/patient/RecordDetailsModal.vue'
import {
  Lock,
  Stethoscope,
  Calendar,
} from 'lucide-vue-next'

const recordStore = useRecordStore()

const records = ref([])
const loading = ref(false)
const showDetailsModal = ref(false)
const selectedRecord = ref(null)

const openRecordDetails = (record) => {
  selectedRecord.value = record
  showDetailsModal.value = true
}

const loadRecords = async () => {
  loading.value = true
  try {
    const data = await recordStore.fetchRecords()
    records.value = data
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadRecords()
})
</script>
