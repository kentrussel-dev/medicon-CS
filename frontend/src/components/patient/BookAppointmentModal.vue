<template>
  <div>
    <Modal :is-open="isOpen && !showPaymentModal" title="Book Doctor Appointment" subtitle="Select your preferred specialist and time slot" size="lg" @close="$emit('close')">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Doctor Selection -->
        <div>
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Select Specialist</label>
          <select
            v-model="form.doctor_id"
            required
            class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            @change="handleDoctorChange"
          >
            <option value="" disabled>-- Choose a physician --</option>
            <option v-for="doc in doctors" :key="doc.id" :value="doc.id">
              {{ doc.name }} &bull; {{ doc.specialty }} (₱{{ (doc.consultation_fee_cents ? doc.consultation_fee_cents / 100 : (doc.consultation_fee || 500)).toFixed(2) }})
            </option>
          </select>
        </div>

        <!-- Appointment Type -->
        <div>
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Consultation Format</label>
          <div class="grid grid-cols-2 gap-3">
            <label
              class="flex items-center justify-center p-2.5 border cursor-pointer text-xs font-mono font-bold uppercase transition-all rounded-none"
              :class="form.type === 'TELEHEALTH' ? 'border-brand-700 bg-brand-700 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
            >
              <input type="radio" v-model="form.type" value="TELEHEALTH" class="sr-only" />
              <Video class="w-3.5 h-3.5 mr-2" />
              Telehealth Video
            </label>
            <label
              class="flex items-center justify-center p-2.5 border cursor-pointer text-xs font-mono font-bold uppercase transition-all rounded-none"
              :class="form.type === 'IN_PERSON' ? 'border-brand-700 bg-brand-700 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
            >
              <input type="radio" v-model="form.type" value="IN_PERSON" class="sr-only" />
              <Building2 class="w-3.5 h-3.5 mr-2" />
              In-Clinic Visit
            </label>
          </div>
        </div>

        <!-- Date & Time Picker -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Appointment Date</label>
            <input
              type="date"
              v-model="form.date"
              :min="minDate"
              required
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            />
          </div>
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Time Slot (30 mins)</label>
            <select
              v-model="form.time"
              required
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            >
              <option value="" disabled>-- Select time --</option>
              <option v-for="t in timeSlots" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
        </div>

        <!-- Fee Banner -->
        <div v-if="selectedDoctor" class="p-3 bg-slate-50 border border-slate-300 flex items-center justify-between font-mono">
          <div class="flex items-center space-x-2">
            <span class="w-2 h-2 bg-brand-700"></span>
            <span class="text-xs font-bold uppercase text-slate-700">Consultation Fee</span>
          </div>
          <div class="text-right">
            <span class="text-sm font-black text-slate-950">₱{{ selectedFeePesos }}</span>
            <span class="text-[10px] text-slate-500 uppercase block">PayMongo / Stripe Gateway</span>
          </div>
        </div>

        <!-- Reason for Visit -->
        <div>
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Reason for Consultation</label>
          <textarea
            v-model="form.reason"
            required
            rows="2"
            placeholder="Briefly describe your symptoms or reason for the visit..."
            class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none resize-none font-sans"
          ></textarea>
        </div>

        <!-- Additional Notes (Encrypted) -->
        <div>
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
            Patient Notes <span class="text-slate-400 font-normal font-sans">(Encrypted)</span>
          </label>
          <textarea
            v-model="form.notes"
            rows="2"
            placeholder="Any medications or details the doctor should know in advance..."
            class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none resize-none font-sans"
          ></textarea>
        </div>
      </form>

      <template #footer>
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-mono text-xs font-bold uppercase transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          :disabled="submitting"
          @click="handleSubmit"
          class="px-5 py-2 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase border border-brand-800 transition-colors shadow-xs disabled:opacity-50 flex items-center space-x-2"
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
      :amount-cents="selectedDoctor?.consultation_fee_cents || 50000"
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
  if (!selectedDoctor.value) return '500.00'
  const cents = selectedDoctor.value.consultation_fee_cents || (selectedDoctor.value.consultation_fee ? selectedDoctor.value.consultation_fee * 100 : 50000)
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
  // Filter slots based on doctor availability
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
      consultation_fee_cents: selectedDoctor.value?.consultation_fee_cents || 50000,
    })

    createdAppointmentId.value = res?.id || Date.now()
    showPaymentModal.value = true
  } catch (err) {
    // Handled in store
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
