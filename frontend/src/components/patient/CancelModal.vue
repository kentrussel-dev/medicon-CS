<template>
  <Modal :is-open="isOpen" title="Cancel Appointment" subtitle="Are you sure you want to cancel this visit?" @close="$emit('close')">
    <div class="space-y-4">
      <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-200 text-rose-800 text-xs flex items-start space-x-2">
        <AlertTriangle class="w-4 h-4 flex-shrink-0 mt-0.5" />
        <span>Cancellation policy: Please notify the medical office at least 24 hours in advance to release the slot for other patients.</span>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Reason for Cancellation</label>
        <textarea
          v-model="reason"
          required
          rows="3"
          placeholder="Please provide a brief reason for cancelling..."
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 resize-none"
        ></textarea>
      </div>
    </div>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors"
      >
        Keep Appointment
      </button>
      <button
        type="button"
        :disabled="submitting || !reason.trim()"
        @click="handleCancel"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white transition-colors shadow-sm disabled:opacity-50"
      >
        <span v-if="submitting">Cancelling...</span>
        <span v-else>Confirm Cancellation</span>
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/common/Modal.vue'
import { useAppointmentStore } from '@/stores/appointments'
import { AlertTriangle } from 'lucide-vue-next'

const props = defineProps({
  isOpen: Boolean,
  appointment: Object,
})

const emit = defineEmits(['close', 'cancelled'])

const store = useAppointmentStore()
const reason = ref('')
const submitting = ref(false)

const handleCancel = async () => {
  if (!props.appointment?.id || !reason.value.trim()) return

  submitting.value = true
  try {
    await store.cancelAppointment(props.appointment.id, reason.value)
    emit('cancelled')
    emit('close')
    reason.value = ''
  } catch (err) {
    // Handled
  } finally {
    submitting.value = false
  }
}
</script>
