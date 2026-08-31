<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-none shadow-2xl max-w-lg w-full overflow-hidden border-2 border-slate-700 animate-in fade-in zoom-in-95 duration-150">
      
      <!-- Header -->
      <div class="px-6 py-4 bg-slate-900 text-white border-b-2 border-brand-600 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-brand-600 text-white flex items-center justify-center font-bold text-sm border border-brand-700">
            ₱
          </div>
          <div>
            <h3 class="text-sm font-bold uppercase tracking-tight">Clinical Consultation Payment</h3>
            <p class="text-[11px] font-mono text-slate-400">PayMongo & Stripe Gateway • Philippine Peso</p>
          </div>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-white p-1 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-5">
        
        <!-- Summary Box -->
        <div class="bg-slate-50 p-4 border border-slate-300 flex justify-between items-center font-mono">
          <div>
            <p class="text-[10px] text-slate-500 uppercase font-bold">Consultation Fee</p>
            <p class="text-2xl font-black text-slate-950">₱{{ (amountCents / 100).toFixed(2) }}</p>
            <p class="text-xs text-slate-600 font-sans mt-0.5">{{ doctorName }} &bull; {{ specialty }}</p>
          </div>
          <span class="px-2.5 py-1 bg-brand-50 text-brand-800 border border-brand-300 text-[10px] font-bold uppercase">
            PHP Currency
          </span>
        </div>

        <!-- Payment Method Selection -->
        <div v-if="step === 'select'">
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-2">
            Select Payment Method
          </label>

          <div class="grid grid-cols-2 gap-3">
            <!-- GCash -->
            <button
              type="button"
              @click="selectedMethod = 'gcash'"
              :class="[
                'p-3 border text-left flex items-center space-x-3 transition rounded-none font-mono',
                selectedMethod === 'gcash'
                  ? 'border-brand-700 bg-brand-50 ring-1 ring-brand-700 text-brand-950'
                  : 'border-slate-300 bg-white hover:bg-slate-50 text-slate-800'
              ]"
            >
              <div class="w-8 h-8 bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                GC
              </div>
              <div>
                <p class="text-xs font-bold uppercase">GCash</p>
                <p class="text-[10px] text-slate-500 font-sans">PayMongo Direct</p>
              </div>
            </button>

            <!-- Maya -->
            <button
              type="button"
              @click="selectedMethod = 'paymaya'"
              :class="[
                'p-3 border text-left flex items-center space-x-3 transition rounded-none font-mono',
                selectedMethod === 'paymaya'
                  ? 'border-brand-700 bg-brand-50 ring-1 ring-brand-700 text-brand-950'
                  : 'border-slate-300 bg-white hover:bg-slate-50 text-slate-800'
              ]"
            >
              <div class="w-8 h-8 bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">
                MY
              </div>
              <div>
                <p class="text-xs font-bold uppercase">Maya</p>
                <p class="text-[10px] text-slate-500 font-sans">PayMongo Direct</p>
              </div>
            </button>

            <!-- GrabPay -->
            <button
              type="button"
              @click="selectedMethod = 'grab_pay'"
              :class="[
                'p-3 border text-left flex items-center space-x-3 transition rounded-none font-mono',
                selectedMethod === 'grab_pay'
                  ? 'border-brand-700 bg-brand-50 ring-1 ring-brand-700 text-brand-950'
                  : 'border-slate-300 bg-white hover:bg-slate-50 text-slate-800'
              ]"
            >
              <div class="w-8 h-8 bg-green-800 text-white flex items-center justify-center font-bold text-xs">
                GP
              </div>
              <div>
                <p class="text-xs font-bold uppercase">GrabPay</p>
                <p class="text-[10px] text-slate-500 font-sans">PayMongo Direct</p>
              </div>
            </button>

            <!-- Credit / Debit Card -->
            <button
              type="button"
              @click="selectedMethod = 'card'"
              :class="[
                'p-3 border text-left flex items-center space-x-3 transition rounded-none font-mono',
                selectedMethod === 'card'
                  ? 'border-brand-700 bg-brand-50 ring-1 ring-brand-700 text-brand-950'
                  : 'border-slate-300 bg-white hover:bg-slate-50 text-slate-800'
              ]"
            >
              <div class="w-8 h-8 bg-slate-800 text-white flex items-center justify-center font-bold text-xs">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold uppercase">Card</p>
                <p class="text-[10px] text-slate-500 font-sans">PayMongo + Stripe</p>
              </div>
            </button>
          </div>

          <!-- Refund Notice -->
          <div class="mt-4 p-3 bg-slate-50 border border-slate-300 text-xs text-slate-700 flex items-start space-x-2 font-mono">
            <svg class="w-4 h-4 text-brand-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-[11px] leading-relaxed font-sans">
              <strong class="font-mono font-bold uppercase text-slate-900">Cancellation Policy:</strong> 100% refund for cancellations > 24 hours prior to appointment; 50% for 12–24 hours; non-refundable under 12 hours.
            </div>
          </div>
        </div>

        <!-- Processing Step -->
        <div v-else-if="step === 'processing'" class="py-8 text-center space-y-4 font-mono">
          <div class="w-12 h-12 border-4 border-brand-700 border-t-transparent animate-spin mx-auto"></div>
          <div>
            <h4 class="text-xs font-bold uppercase text-slate-900">Connecting to {{ selectedMethodName }} Gateway</h4>
            <p class="text-[11px] text-slate-500 font-sans mt-1">Securing tokenized 256-bit encryption tunnel...</p>
          </div>
        </div>

        <!-- Success Step -->
        <div v-else-if="step === 'success'" class="py-4 text-center space-y-4">
          <div class="w-12 h-12 bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center mx-auto text-xl font-bold font-mono">
            ✓
          </div>
          <div>
            <h4 class="text-sm font-bold uppercase text-slate-950 font-mono">Payment Confirmed</h4>
            <p class="text-xs text-slate-600 font-sans mt-0.5">Your consultation appointment has been confirmed.</p>
          </div>
          <div class="bg-slate-50 p-3.5 text-left text-xs space-y-1 border border-slate-300 font-mono">
            <div class="flex justify-between"><span class="text-slate-500 uppercase">Txn ID:</span> <span class="font-bold">{{ paymentReceipt?.gateway_payment_id || 'pay_pm_89210' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500 uppercase">Amount:</span> <span class="font-bold text-slate-900">₱{{ (amountCents / 100).toFixed(2) }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500 uppercase">Processor:</span> <span class="uppercase font-bold">{{ paymentReceipt?.gateway || 'PayMongo' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500 uppercase">Method:</span> <span class="uppercase font-bold">{{ selectedMethodName }}</span></div>
          </div>
        </div>

      </div>

      <!-- Footer Buttons -->
      <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-300 flex justify-end space-x-3">
        <button
          v-if="step === 'select'"
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-xs font-mono font-bold uppercase text-slate-700 hover:bg-slate-200 border border-slate-300 bg-white transition"
        >
          Cancel
        </button>

        <button
          v-if="step === 'select'"
          type="button"
          @click="processPayment"
          :disabled="loading"
          class="px-5 py-2 text-xs font-mono font-bold uppercase text-white bg-brand-700 hover:bg-brand-800 border border-brand-800 shadow-xs transition flex items-center space-x-2 disabled:opacity-50"
        >
          <span>Authorize ₱{{ (amountCents / 100).toFixed(2) }}</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>

        <button
          v-if="step === 'success'"
          type="button"
          @click="finishAndClose"
          class="w-full py-2 text-xs font-mono font-bold uppercase text-white bg-slate-900 hover:bg-slate-800 border border-slate-950 shadow-xs transition"
        >
          Done & Return to Workspace
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
    default: 50000,
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
      }, 750)
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
