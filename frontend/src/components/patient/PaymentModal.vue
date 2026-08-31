<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-200">
      
      <!-- Header -->
      <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold">Clinical Consultation Payment</h3>
            <p class="text-xs text-emerald-100">PayMongo & Stripe Fallback Gateway (PHP)</p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-white/80 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-6">
        
        <!-- Summary Box -->
        <div class="bg-slate-50 dark:bg-slate-750 p-4 rounded-xl border border-slate-200 dark:border-slate-700 flex justify-between items-center">
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Consultation Fee</p>
            <p class="text-2xl font-black text-slate-900 dark:text-white">₱{{ (amountCents / 100).toFixed(2) }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ doctorName }} • {{ specialty }}</p>
          </div>
          <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 text-xs font-bold rounded-full">
            Philippine Peso (PHP)
          </span>
        </div>

        <!-- Payment Method Selection -->
        <div v-if="step === 'select'">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">
            Select Payment Method
          </label>

          <div class="grid grid-cols-2 gap-3">
            <!-- GCash -->
            <button
              type="button"
              @click="selectedMethod = 'gcash'"
              :class="[
                'p-3.5 rounded-xl border text-left flex items-center space-x-3 transition',
                selectedMethod === 'gcash'
                  ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 ring-2 ring-blue-500/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-xs">
                GC
              </div>
              <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">GCash</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">PayMongo Direct</p>
              </div>
            </button>

            <!-- Maya -->
            <button
              type="button"
              @click="selectedMethod = 'paymaya'"
              :class="[
                'p-3.5 rounded-xl border text-left flex items-center space-x-3 transition',
                selectedMethod === 'paymaya'
                  ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/20 ring-2 ring-emerald-500/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-xs">
                MY
              </div>
              <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Maya</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">PayMongo Direct</p>
              </div>
            </button>

            <!-- GrabPay -->
            <button
              type="button"
              @click="selectedMethod = 'grab_pay'"
              :class="[
                'p-3.5 rounded-xl border text-left flex items-center space-x-3 transition',
                selectedMethod === 'grab_pay'
                  ? 'border-green-500 bg-green-50/50 dark:bg-green-900/20 ring-2 ring-green-500/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <div class="w-9 h-9 rounded-lg bg-green-700 text-white flex items-center justify-center font-black text-xs">
                GP
              </div>
              <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">GrabPay</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">PayMongo Direct</p>
              </div>
            </button>

            <!-- Credit / Debit Card -->
            <button
              type="button"
              @click="selectedMethod = 'card'"
              :class="[
                'p-3.5 rounded-xl border text-left flex items-center space-x-3 transition',
                selectedMethod === 'card'
                  ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20 ring-2 ring-indigo-500/20'
                  : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'
              ]"
            >
              <div class="w-9 h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-xs">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Credit / Debit</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">PayMongo + Stripe</p>
              </div>
            </button>
          </div>

          <!-- Refund Notice -->
          <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-start space-x-2">
            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <span class="font-bold">Cancellation Policy:</span> 100% refund for cancellations > 24 hours prior to appointment; 50% for 12–24 hours; non-refundable under 12 hours.
            </div>
          </div>
        </div>

        <!-- Processing Step -->
        <div v-else-if="step === 'processing'" class="py-8 text-center space-y-4">
          <div class="w-16 h-16 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin mx-auto"></div>
          <div>
            <h4 class="text-base font-bold text-slate-900 dark:text-white">Connecting to {{ selectedMethodName }} Gateway</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Securing tokenized 256-bit encryption tunnel...</p>
          </div>
        </div>

        <!-- Success Step -->
        <div v-else-if="step === 'success'" class="py-6 text-center space-y-4">
          <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto text-3xl">
            ✓
          </div>
          <div>
            <h4 class="text-lg font-bold text-slate-900 dark:text-white">Payment Confirmed!</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Your consultation appointment has been scheduled and confirmed.</p>
          </div>
          <div class="bg-slate-50 dark:bg-slate-750 p-4 rounded-xl text-left text-xs space-y-1.5 border border-slate-200 dark:border-slate-700">
            <div class="flex justify-between"><span class="text-slate-500">Transaction ID:</span> <span class="font-mono font-bold">{{ paymentReceipt?.gateway_payment_id || 'pay_pm_89210' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Amount Paid:</span> <span class="font-bold text-emerald-600">₱{{ (amountCents / 100).toFixed(2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Processor:</span> <span class="uppercase font-semibold">{{ paymentReceipt?.gateway || 'PayMongo' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Method:</span> <span class="uppercase font-semibold">{{ selectedMethodName }}</span></div>
          </div>
        </div>

      </div>

      <!-- Footer Buttons -->
      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
        <button
          v-if="step === 'select'"
          type="button"
          @click="$emit('close')"
          class="px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition"
        >
          Cancel
        </button>

        <button
          v-if="step === 'select'"
          type="button"
          @click="processPayment"
          :disabled="loading"
          class="px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-lg shadow-emerald-500/20 transition flex items-center space-x-2"
        >
          <span>Pay ₱{{ (amountCents / 100).toFixed(2) }}</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>

        <button
          v-if="step === 'success'"
          type="button"
          @click="finishAndClose"
          class="w-full py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-lg transition"
        >
          Done & View Appointment
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from '@/stores/notifications'

const props = defineProps({
  isOpen: Boolean,
  appointmentId: [Number, String],
  doctorName: {
    type: String,
    default: 'Attending Clinician',
  },
  specialty: {
    type: String,
    default: 'General Practice',
  },
  amountCents: {
    type: Number,
    default: 12000,
  },
})

const emit = defineEmits(['close', 'payment-complete'])

const notificationStore = useNotificationStore()
const step = ref('select') // 'select', 'processing', 'success'
const selectedMethod = ref('gcash')
const loading = ref(false)
const paymentReceipt = ref(null)

const selectedMethodName = computed(() => {
  switch (selectedMethod.value) {
    case 'gcash': return 'GCash'
    case 'paymaya': return 'Maya'
    case 'grab_pay': return 'GrabPay'
    case 'card': return 'Credit / Debit Card'
    default: return 'PayMongo'
  }
})

async function processPayment() {
  loading.value = true
  step.value = 'processing'

  try {
    const res = await api.post('/payments/checkout', {
      appointment_id: props.appointmentId,
      payment_method: selectedMethod.value,
      amount_cents: props.amountCents,
    })

    if (res.data?.success) {
      paymentReceipt.value = res.data.data
      setTimeout(() => {
        step.value = 'success'
        loading.value = false
        notificationStore.success(`Payment of ₱${(props.amountCents / 100).toFixed(2)} received via ${selectedMethodName.value}!`)
      }, 1000)
    }
  } catch (err) {
    step.value = 'select'
    loading.value = false
    notificationStore.error(err.response?.data?.message || 'Payment failed to process.')
  }
}

function finishAndClose() {
  emit('payment-complete', paymentReceipt.value)
  emit('close')
  step.value = 'select'
}
</script>
