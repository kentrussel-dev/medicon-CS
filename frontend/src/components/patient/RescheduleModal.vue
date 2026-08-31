<template>
  <Modal :is-open="isOpen" title="Reschedule Appointment" subtitle="Choose a new date and time for your consultation" @close="$emit('close')">
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600">
        <span class="font-bold text-slate-900">Current Slot:</span>
        {{ formatDate(appointment?.scheduled_start) }}
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">New Date</label>
          <input
            type="date"
            v-model="form.date"
            :min="minDate"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">New Time Slot</label>
          <select
            v-model="form.time"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
          >
            <option v-for="t in timeSlots" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Reason for Rescheduling</label>
        <textarea
          v-model="form.reason"
          rows="2"
          placeholder="Brief explanation for changing the schedule..."
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
        Dismiss
      </button>
      <button
        type="button"
        :disabled="submitting"
        @click="handleSubmit"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white transition-colors shadow-sm disabled:opacity-50"
      >
        <span v-if="submitting">Updating...</span>
        <span v-else>Confirm New Time</span>
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import Modal from '@/components/common/Modal.vue'
import { useAppointmentStore } from '@/stores/appointments'

const props = defineProps({
  isOpen: Boolean,
  appointment: Object,
})

const emit = defineEmits(['close', 'updated'])

const store = useAppointmentStore()
const submitting = ref(false)

const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const form = ref({
  date: minDate.value,
  time: '11:00',
  reason: '',
})

const timeSlots = [
  '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
  '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
]

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const handleSubmit = async () => {
  if (!props.appointment?.id || !form.value.date || !form.value.time) return

  submitting.value = true
  try {
    const startIso = `${form.value.date}T${form.value.time}:00`
    const [hours, minutes] = form.value.time.split(':').map(Number)
    const endMinutes = minutes + 30
    const endHour = hours + Math.floor(endMinutes / 60)
    const endMinFormatted = String(endMinutes % 60).padStart(2, '0')
    const endHourFormatted = String(endHour).padStart(2, '0')
    const endIso = `${form.value.date}T${endHourFormatted}:${endMinFormatted}:00`

    await store.rescheduleAppointment(props.appointment.id, {
      scheduled_start: startIso,
      scheduled_end: endIso,
      reason: form.value.reason || 'Patient requested schedule update.',
    })

    emit('updated')
    emit('close')
  } catch (err) {
    // Handled
  } finally {
    submitting.value = false
  }
}
</script>
