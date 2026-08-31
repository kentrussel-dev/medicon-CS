<template>
  <Modal :is-open="isOpen" title="Prescription Details" subtitle="Official electronic medical prescription" size="lg" @close="$emit('close')">
    <div v-if="prescription" class="space-y-6">
      <!-- Prescription Header -->
      <div class="p-4 bg-emerald-50/70 border border-emerald-200/80 rounded-2xl flex items-center justify-between">
        <div>
          <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Prescribing Physician</span>
          <h4 class="text-base font-extrabold text-slate-900 mt-0.5">{{ prescription.doctor_name }}</h4>
          <p class="text-xs text-slate-500">License: {{ prescription.doctor_license }}</p>
        </div>
        <div class="text-right">
          <Badge :variant="prescription.is_dispensed ? 'success' : 'pending'">
            {{ prescription.is_dispensed ? 'Dispensed' : 'Active / Valid' }}
          </Badge>
          <p class="text-xs text-slate-500 mt-1">Valid Until: {{ prescription.valid_until }}</p>
        </div>
      </div>

      <!-- Prescribed Medications Table -->
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2.5">Medications & Dosage Instructions</h4>
        <div class="overflow-x-auto border border-slate-200 rounded-2xl bg-white shadow-xs">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
              <tr>
                <th class="px-4 py-3">Medication Name</th>
                <th class="px-4 py-3">Dosage</th>
                <th class="px-4 py-3">Frequency</th>
                <th class="px-4 py-3">Duration</th>
                <th class="px-4 py-3">Instructions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
              <tr v-for="item in prescription.items" :key="item.id" class="hover:bg-slate-50/50">
                <td class="px-4 py-3.5 font-bold text-slate-900">{{ item.medication_name }}</td>
                <td class="px-4 py-3.5">{{ item.dosage }}</td>
                <td class="px-4 py-3.5">{{ item.frequency }}</td>
                <td class="px-4 py-3.5">{{ item.duration_days }} days</td>
                <td class="px-4 py-3.5 text-slate-600">{{ item.instructions || 'As directed' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pharmacist & Patient Notes -->
      <div v-if="prescription.notes">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Doctor's Clinical Notes</h4>
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 whitespace-pre-line">
          {{ prescription.notes }}
        </div>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-slate-900 hover:bg-slate-800 text-white transition-colors"
      >
        Close
      </button>
    </template>
  </Modal>
</template>

<script setup>
import Modal from '@/components/common/Modal.vue'
import Badge from '@/components/common/Badge.vue'

defineProps({
  isOpen: Boolean,
  prescription: Object,
})

defineEmits(['close'])
</script>
