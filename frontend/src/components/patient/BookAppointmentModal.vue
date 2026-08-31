<template>
  <div>
    <Modal :is-open="isOpen && !showPaymentModal" title="Book Doctor Appointment" subtitle="Select your preferred specialist and time slot" size="lg" @close="$emit('close')">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Doctor Selection -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Select Specialist</label>
          <select
            v-model="form.doctor_id"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white dark:bg-slate-800 dark:text-white"
            @change="handleDoctorChange"
          >
            <option value="" disabled>-- Choose a physician --</option>
            <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
              {{ doc.name }} &bull; {{ doc.specialty }} (₱{{ doc.consultation_fee_pesos || (doc.consultation_fee_cents ? (doc.consultation_fee_cents / 100).toFixed(2) : (doc.consultation_fee || 120).toFixed(2)) }})
            </option>
          </select>
        </div>

        <!-- Appointment Type -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Consultation Format</label>
          <div class="grid grid-cols-2 gap-3">
            <label
              class="flex items-center justify-center p-3 rounded-xl border cursor-pointer text-sm font-semibold transition-all"
              :class="form.type === 'TELEHEALTH' ? 'border-brand-500 bg-brand-50 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'"
            >
              <input type="radio" v-model="form.type" value="TELEHEALTH" class="sr-only" />
              <Video class="w-4 h-4 mr-2" />
              Telehealth Video
            </label>
            <label
              class="flex items-center justify-center p-3 rounded-xl border cursor-pointer text-sm font-semibold transition-all"
              :class="form.type === 'IN_PERSON' ? 'border-brand-500 bg-brand-50 text-brand-800 dark:bg-brand-900/30 dark:text-brand-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800'"
            >
              <input type="radio" v-model="form.type" value="IN_PERSON" class="sr-only" />
              <Building2 class="w-4 h-4 mr-2" />
              In-Clinic Visit
            </label>
          </div>
        </div>

        <!-- Date & Time Picker -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Appointment Date</label>
            <input
              type="date"
              v-model="form.date"
              :min="minDate"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-slate-800 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Time Slot (30 mins)</label>
            <select
              v-model="form.time"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white dark:bg-slate-800 dark:text-white"
            >
              <option value="" disabled>-- Select time --</option>
              <option v-for="t in timeSlots" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
        </div>

        <!-- Fee Banner -->
        <div v-if="selectedDoctor" class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-xl flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-semibold text-emerald-900 dark:text-emerald-200">Consultation Fee</span>
          </div>
          <div class="text-right">
            <span class="text-sm font-black text-emerald-700 dark:text-emerald-300">₱{{ selectedFeePesos }}</span>
            <span class="text-[11px] text-emerald-600/80 block">PayMongo / Stripe Gateway</span>
          </div>
        </div>

        <!-- Reason for Visit -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Reason for Consultation</label>
          <textarea
            v-model="form.reason"
            required
            rows="2"
            placeholder="Briefly describe your symptoms or reason for the visit..."
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none dark:bg-slate-800 dark:text-white"
          ></textarea>
        </div>

        <!-- Additional Notes (Encrypted) -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Patient Notes <span class="text-slate-400 font-normal">(Encrypted)</span>
          </label>
          <textarea
            v-model="form.notes"
            rows="2"
            placeholder="Any medications or details the doctor should know in advance..."
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none dark:bg-slate-800 dark:text-white"
          ></textarea>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          :disabled="submitting"
          @click="handleSubmit"
          class="px-5 py-2 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm disabled:opacity-50 flex items-center space-x-2"
        >
          <span v-if="submitting">Booking...</span>
          <span v-else>Proceed to Payment (₱{{ selectedFeePesos }})</span>
        </button>
      </template>
    </Modal>

    <!-- Payment Modal Integration -->
    <PaymentModal
      :is-open="showPaymentModal"
      :appointment-id="createdAppointmentId"
      :doctor-name="selectedDoctor?.name || 'Specialist'"
      :specialty="selectedDoctor?.specialty || 'Clinical Practice'"
      :amount-cents="selectedDoctor?.consultation_fee_cents || 12000"
      @close="handlePaymentClose"
      @payment-complete="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Modal from '@/components/common/Modal.vue'
import PaymentModal from '@/components/patient/PaymentModal.vue'
import { useDoctorStore } from '@/stores/doctors'
import { useAppointmentStore } from '@/stores/appointments'
import { Video, Building2 } from 'lucide-vue-next'

const props = defineProps({
  isOpen: Boolean,
  preselectedDoctorId: Number,
})

const emit = defineEmits(['close', 'booked'])

const doctorStore = useDoctorStore()
const appointmentStore = useAppointmentStore()
const submitting = ref(false)
const showPaymentModal = ref(false)
const createdAppointmentId = ref(null)

const doctors = computed(() => doctorStore.doctors)

const selectedDoctor = computed(() => {
  return doctors.value.find((d) => d.id === Number(form.value.doctor_id)) || null
})

const selectedFeePesos = computed(() => {
  if (!selectedDoctor.value) return '120.00'
  const cents = selectedDoctor.value.consultation_fee_cents || (selectedDoctor.value.consultation_fee ? selectedDoctor.value.consultation_fee * 100 : 12000)
  return (cents / 100).toFixed(2)
})

const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const form = ref({
  doctor_id: '',
  type: 'TELEHEALTH',
  date: minDate.value,
  time: '10:00',
  reason: '',
  notes: '',
})

const timeSlots = [
  '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
  '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
]

watch(() => props.preselectedDoctorId, (newId) => {
  if (newId) {
    form.value.doctor_id = newId
  }
}, { immediate: true })

onMounted(async () => {
  if (doctors.value.length === 0) {
    await doctorStore.fetchDoctors()
  }
})

const handleDoctorChange = () => {
  // Can filter slots based on doctor availability
}

const handleSubmit = async () => {
  if (!form.value.doctor_id || !form.value.date || !form.value.time || !form.value.reason) {
    return
  }

  submitting.value = true
  try {
    const startIso = `${form.value.date}T${form.value.time}:00`
    const [hours, minutes] = form.value.time.split(':').map(Number)
    const endMinutes = minutes + 30
    const endHour = hours + Math.floor(endMinutes / 60)
    const endMinFormatted = String(endMinutes % 60).padStart(2, '0')
    const endHourFormatted = String(endHour).padStart(2, '0')
    const endIso = `${form.value.date}T${endHourFormatted}:${endMinFormatted}:00`

    const res = await appointmentStore.bookAppointment({
      doctor_id: Number(form.value.doctor_id),
      scheduled_start: startIso,
      scheduled_end: endIso,
      type: form.value.type,
      reason: form.value.reason,
      notes: form.value.notes || null,
      consultation_fee_cents: selectedDoctor.value?.consultation_fee_cents || 12000,
    })

    createdAppointmentId.value = res?.id || Date.now()
    showPaymentModal.value = true
  } catch (err) {
    // Handled in Axios interceptor
  } finally {
    submitting.value = false
  }
}

function handlePaymentClose() {
  showPaymentModal.value = false
  emit('booked')
  emit('close')
}

function handlePaymentSuccess() {
  showPaymentModal.value = false
  emit('booked')
  emit('close')
}
</script>
