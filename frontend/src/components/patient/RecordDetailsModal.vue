<template>
  <Modal :is-open="isOpen" title="Clinical Encounter Record" subtitle="Encrypted consultation summary & medical observations" size="lg" @close="$emit('close')">
    <div v-if="record" class="space-y-6">
      <!-- Top Meta Bar -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
        <div>
          <span class="text-slate-400 font-medium">Record Date:</span>
          <p class="font-bold text-slate-900 mt-0.5">{{ record.record_date }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-medium">Attending Physician:</span>
          <p class="font-bold text-slate-900 mt-0.5">{{ record.doctor_name }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-medium">Specialty:</span>
          <p class="font-bold text-slate-900 mt-0.5">{{ record.doctor_specialty }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-medium">Security Cast:</span>
          <p class="font-bold text-emerald-600 mt-0.5 flex items-center">
            <Lock class="w-3 h-3 mr-1 inline" /> AES-256
          </p>
        </div>
      </div>

      <!-- Vital Signs Grid -->
      <div v-if="record.vital_signs && Object.keys(record.vital_signs).length > 0">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Physiological Vital Signs</h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div v-if="record.vital_signs.blood_pressure" class="p-3 bg-white rounded-xl border border-slate-200">
            <span class="text-[11px] text-slate-400 font-semibold uppercase">Blood Pressure</span>
            <p class="text-base font-extrabold text-slate-900 mt-0.5">{{ record.vital_signs.blood_pressure }} <span class="text-xs font-normal text-slate-500">mmHg</span></p>
          </div>
          <div v-if="record.vital_signs.heart_rate" class="p-3 bg-white rounded-xl border border-slate-200">
            <span class="text-[11px] text-slate-400 font-semibold uppercase">Heart Rate</span>
            <p class="text-base font-extrabold text-slate-900 mt-0.5">{{ record.vital_signs.heart_rate }} <span class="text-xs font-normal text-slate-500">BPM</span></p>
          </div>
          <div v-if="record.vital_signs.oxygen_saturation" class="p-3 bg-white rounded-xl border border-slate-200">
            <span class="text-[11px] text-slate-400 font-semibold uppercase">Oxygen (SpO2)</span>
            <p class="text-base font-extrabold text-slate-900 mt-0.5">{{ record.vital_signs.oxygen_saturation }} <span class="text-xs font-normal text-slate-500">%</span></p>
          </div>
          <div v-if="record.vital_signs.weight_kg" class="p-3 bg-white rounded-xl border border-slate-200">
            <span class="text-[11px] text-slate-400 font-semibold uppercase">Weight</span>
            <p class="text-base font-extrabold text-slate-900 mt-0.5">{{ record.vital_signs.weight_kg }} <span class="text-xs font-normal text-slate-500">kg</span></p>
          </div>
        </div>
      </div>

      <!-- Primary Diagnosis -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Primary Clinical Diagnosis</h4>
        <div class="p-3.5 bg-brand-50/70 rounded-xl border border-brand-200/80 text-brand-950 font-semibold text-sm">
          {{ record.diagnosis }}
        </div>
      </div>

      <!-- ICD-10 Codes -->
      <div v-if="record.icd_10_codes && record.icd_10_codes.length > 0">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">ICD-10 Diagnostic Codes</h4>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="code in record.icd_10_codes"
            :key="code"
            class="px-2.5 py-1 bg-slate-100 border border-slate-300 text-slate-800 rounded-lg text-xs font-mono font-bold"
          >
            {{ code }}
          </span>
        </div>
      </div>

      <!-- Clinical Examination Notes -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Subjective & Objective Notes</h4>
        <div class="p-4 bg-white rounded-xl border border-slate-200 text-sm text-slate-700 whitespace-pre-line leading-relaxed font-sans">
          {{ record.clinical_notes }}
        </div>
      </div>

      <!-- Treatment & Care Plan -->
      <div v-if="record.treatment_plan">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Treatment & Management Plan</h4>
        <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200/70 text-sm text-emerald-950 whitespace-pre-line leading-relaxed">
          {{ record.treatment_plan }}
        </div>
      </div>

      <!-- Attachments / Lab Results -->
      <div v-if="record.attachments && record.attachments.length > 0">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Signed Lab Reports & Attachments</h4>
        <div class="space-y-2">
          <div
            v-for="att in record.attachments"
            :key="att.id"
            class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs"
          >
            <div class="flex items-center space-x-2.5 min-w-0">
              <FileSpreadsheet class="w-5 h-5 text-brand-600 flex-shrink-0" />
              <span class="font-bold text-slate-800 truncate">{{ att.file_name }}</span>
              <span class="text-slate-400">({{ att.file_size_formatted }})</span>
            </div>
            <a
              :href="att.download_url"
              target="_blank"
              class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 font-semibold text-brand-700 hover:bg-brand-50 flex items-center space-x-1"
            >
              <Download class="w-3.5 h-3.5" />
              <span>Download</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white transition-colors"
      >
        Close Record
      </button>
    </template>
  </Modal>
</template>

<script setup>
import Modal from '@/components/common/Modal.vue'
import { Lock, FileSpreadsheet, Download } from 'lucide-vue-next'

defineProps({
  isOpen: Boolean,
  record: Object,
})

defineEmits(['close'])
</script>
