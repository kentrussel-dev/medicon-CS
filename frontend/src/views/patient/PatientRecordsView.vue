<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">Encrypted Clinical Records</h2>
        <p class="text-xs text-slate-500 mt-0.5">Consultation summaries, vital sign history, and lab attachments</p>
      </div>

      <div class="flex items-center space-x-2 text-xs font-bold text-emerald-700 bg-emerald-50 px-3.5 py-2 rounded-xl border border-emerald-200">
        <Lock class="w-4 h-4 text-emerald-600" />
        <span>HIPAA Encrypted Storage</span>
      </div>
    </div>

    <!-- Records List -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Decrypting and loading health records..." />
    </div>

    <div v-else-if="records.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-100">
      <FileText class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <h4 class="font-bold text-slate-700 text-sm">No Clinical Records Found</h4>
      <p class="text-xs text-slate-400 mt-1">Medical records will appear here following your completed physician consultations.</p>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="record in records"
        :key="record.id"
        class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-6"
      >
        <div class="flex items-start space-x-4">
          <div class="p-3.5 rounded-2xl bg-sky-50 text-sky-700 flex-shrink-0">
            <Stethoscope class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center space-x-2.5">
              <h4 class="font-bold text-slate-900 text-base">{{ record.diagnosis }}</h4>
            </div>
            <p class="text-xs font-semibold text-brand-600 mt-0.5">
              Dr. {{ record.doctor_name }} ({{ record.doctor_specialty }})
            </p>

            <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-slate-500">
              <span class="flex items-center font-bold text-slate-700">
                <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                {{ record.record_date }}
              </span>
              <span v-if="record.vital_signs?.blood_pressure" class="flex items-center bg-slate-100 px-2 py-0.5 rounded text-[11px] font-semibold text-slate-700">
                BP: {{ record.vital_signs.blood_pressure }}
              </span>
              <span v-if="record.icd_10_codes?.length" class="text-[11px] text-slate-400">
                ICD-10: {{ record.icd_10_codes.join(', ') }}
              </span>
            </div>
          </div>
        </div>

        <button
          @click="openRecord(record)"
          class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5 self-start sm:self-center"
        >
          <Eye class="w-4 h-4" />
          <span>View Encounter Details</span>
        </button>
      </div>
    </div>

    <!-- Record Modal -->
    <RecordDetailsModal
      :is-open="showRecordModal"
      :record="selectedRecord"
      @close="showRecordModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRecordStore } from '@/stores/records'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import RecordDetailsModal from '@/components/patient/RecordDetailsModal.vue'
import { Stethoscope, FileText, Lock, Calendar, Eye } from 'lucide-vue-next'

const recordStore = useRecordStore()
const loading = ref(false)
const showRecordModal = ref(false)
const selectedRecord = ref(null)

const records = computed(() => recordStore.records)

const openRecord = (rec) => {
  selectedRecord.value = rec
  showRecordModal.value = true
}

onMounted(async () => {
  loading.value = true
  try {
    await recordStore.fetchRecords()
  } finally {
    loading.value = false
  }
})
</script>
