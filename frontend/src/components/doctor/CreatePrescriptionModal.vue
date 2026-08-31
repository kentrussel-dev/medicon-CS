<template>
  <Modal :is-open="isOpen" title="Formulate Electronic Prescription" subtitle="Issue prescription with dosage, frequency, and duration" size="xl" @close="$emit('close')">
    <form @submit.prevent="handleSubmit" class="space-y-5">
      <!-- Target Patient & Validity -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Patient ID</label>
          <input
            type="number"
            v-model.number="form.patient_id"
            required
            placeholder="e.g. 1"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Valid Until</label>
          <input
            type="date"
            v-model="form.valid_until"
            :min="minDate"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>

      <!-- Prescribed Medication Items -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Prescription Medications</label>
          <button
            type="button"
            @click="addItem"
            class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center space-x-1"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Add Medication</span>
          </button>
        </div>

        <div class="space-y-3">
          <div
            v-for="(item, idx) in form.items"
            :key="idx"
            class="p-4 bg-slate-50 border border-slate-200 rounded-2xl relative space-y-3"
          >
            <button
              v-if="form.items.length > 1"
              type="button"
              @click="removeItem(idx)"
              class="absolute top-3 right-3 text-slate-400 hover:text-rose-500 p-1"
            >
              <Trash2 class="w-4 h-4" />
            </button>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pr-6">
              <div class="sm:col-span-2">
                <span class="text-[11px] text-slate-500 font-semibold">Medication Name</span>
                <input
                  type="text"
                  v-model="item.medication_name"
                  required
                  placeholder="e.g. Amoxicillin 500mg"
                  class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 bg-white"
                />
              </div>
              <div>
                <span class="text-[11px] text-slate-500 font-semibold">Dosage</span>
                <input
                  type="text"
                  v-model="item.dosage"
                  required
                  placeholder="1 tablet (500mg)"
                  class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 bg-white"
                />
              </div>
              <div>
                <span class="text-[11px] text-slate-500 font-semibold">Duration (Days)</span>
                <input
                  type="number"
                  v-model.number="item.duration_days"
                  required
                  min="1"
                  max="365"
                  class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 bg-white"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <span class="text-[11px] text-slate-500 font-semibold">Frequency</span>
                <input
                  type="text"
                  v-model="item.frequency"
                  required
                  placeholder="e.g. Three times daily with food"
                  class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 bg-white"
                />
              </div>
              <div>
                <span class="text-[11px] text-slate-500 font-semibold">Special Instructions</span>
                <input
                  type="text"
                  v-model="item.instructions"
                  placeholder="e.g. Complete full course. Drink plenty of water."
                  class="w-full mt-1 px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 bg-white"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Prescription Remarks -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Pharmacy / Patient Notes</label>
        <textarea
          v-model="form.notes"
          rows="2"
          placeholder="Special compounding instructions, allergy warnings, or refill authorizations..."
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"
        ></textarea>
      </div>
    </form>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors"
      >
        Cancel
      </button>
      <button
        type="button"
        :disabled="submitting"
        @click="handleSubmit"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm disabled:opacity-50"
      >
        <span v-if="submitting">Formulating...</span>
        <span v-else>Issue Prescription</span>
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Modal from '@/components/common/Modal.vue'
import { usePrescriptionStore } from '@/stores/prescriptions'
import { Plus, Trash2 } from 'lucide-vue-next'

const props = defineProps({
  isOpen: Boolean,
  prefillPatientId: Number,
  prefillAppointmentId: Number,
})

const emit = defineEmits(['close', 'created'])

const prescriptionStore = usePrescriptionStore()
const submitting = ref(false)

const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const form = ref({
  patient_id: '',
  appointment_id: null,
  valid_until: '',
  notes: '',
  items: [
    {
      medication_name: '',
      dosage: '',
      frequency: '',
      duration_days: 7,
      instructions: '',
    },
  ],
})

// Set 90 days validity default
const validDate = new Date()
validDate.setDate(validDate.getDate() + 90)
form.value.valid_until = validDate.toISOString().split('T')[0]

watch(() => props.prefillPatientId, (id) => {
  if (id) form.value.patient_id = id
}, { immediate: true })

watch(() => props.prefillAppointmentId, (id) => {
  if (id) form.value.appointment_id = id
}, { immediate: true })

const addItem = () => {
  form.value.items.push({
    medication_name: '',
    dosage: '',
    frequency: '',
    duration_days: 7,
    instructions: '',
  })
}

const removeItem = (idx) => {
  form.value.items.splice(idx, 1)
}

const handleSubmit = async () => {
  if (!form.value.patient_id || !form.value.valid_until || form.value.items.length === 0) return

  submitting.value = true
  try {
    await prescriptionStore.createPrescription(form.value)
    emit('created')
    emit('close')
  } catch (err) {
    // Handled
  } finally {
    submitting.value = false
  }
}
</script>
