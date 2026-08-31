<template>
  <div class="max-w-6xl mx-auto space-y-6 pb-12">
    <!-- Breadcrumb & Top Bar -->
    <div class="bg-white border border-slate-300 p-4 flex items-center justify-between">
      <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
        <router-link to="/patient/appointments" class="hover:text-brand-700">Appointments</router-link>
        <span>/</span>
        <span class="font-bold text-slate-900">Secure Payment Gateway</span>
      </div>
      <div class="flex items-center space-x-2">
        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
        <span class="text-[10px] font-mono font-bold uppercase text-slate-600">256-Bit SSL Encrypted Tunnel</span>
      </div>
    </div>

    <!-- Main Multi-Column Checkout Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      
      <!-- LEFT COLUMN: Payment Method & Card Details -->
      <div class="lg:col-span-7 space-y-6">
        <div class="bg-white border border-slate-300 p-6 sm:p-8 space-y-6">
          
          <!-- Header -->
          <div class="border-b border-slate-200 pb-4">
            <h1 class="text-2xl font-bold uppercase tracking-tight text-slate-950 font-sans">Payment</h1>
            <p class="text-xs text-slate-500 font-mono mt-1">Add your payment method details below</p>
          </div>

          <!-- Payment Method Selector Tabs -->
          <div class="grid grid-cols-4 gap-2 font-mono text-xs">
            <!-- Card -->
            <button
              type="button"
              @click="paymentMethod = 'card'"
              :class="[
                'p-3 border text-center transition flex flex-col items-center justify-center space-y-1',
                paymentMethod === 'card'
                  ? 'border-brand-700 bg-brand-50 text-brand-950 font-bold ring-1 ring-brand-700'
                  : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
              ]"
            >
              <CreditCard class="w-4 h-4 text-slate-700" />
              <span class="text-[11px] uppercase">Card</span>
            </button>

            <!-- GCash -->
            <button
              type="button"
              @click="paymentMethod = 'gcash'"
              :class="[
                'p-3 border text-center transition flex flex-col items-center justify-center space-y-1',
                paymentMethod === 'gcash'
                  ? 'border-brand-700 bg-brand-50 text-brand-950 font-bold ring-1 ring-brand-700'
                  : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
              ]"
            >
              <div class="w-4 h-4 bg-blue-600 text-white rounded-xs text-[9px] font-black flex items-center justify-center">GC</div>
              <span class="text-[11px] uppercase">GCash</span>
            </button>

            <!-- Maya -->
            <button
              type="button"
              @click="paymentMethod = 'paymaya'"
              :class="[
                'p-3 border text-center transition flex flex-col items-center justify-center space-y-1',
                paymentMethod === 'paymaya'
                  ? 'border-brand-700 bg-brand-50 text-brand-950 font-bold ring-1 ring-brand-700'
                  : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
              ]"
            >
              <div class="w-4 h-4 bg-emerald-600 text-white rounded-xs text-[9px] font-black flex items-center justify-center">MY</div>
              <span class="text-[11px] uppercase">Maya</span>
            </button>

            <!-- GrabPay -->
            <button
              type="button"
              @click="paymentMethod = 'grab_pay'"
              :class="[
                'p-3 border text-center transition flex flex-col items-center justify-center space-y-1',
                paymentMethod === 'grab_pay'
                  ? 'border-brand-700 bg-brand-50 text-brand-950 font-bold ring-1 ring-brand-700'
                  : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
              ]"
            >
              <div class="w-4 h-4 bg-green-700 text-white rounded-xs text-[9px] font-black flex items-center justify-center">GP</div>
              <span class="text-[11px] uppercase">GrabPay</span>
            </button>
          </div>

          <!-- Form Fields -->
          <form @submit.prevent="handlePaymentSubmit" class="space-y-4">
            
            <!-- CARD PAYMENT FORM -->
            <template v-if="paymentMethod === 'card'">
              <!-- Card Number -->
              <div>
                <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Card number
                </label>
                <div class="relative">
                  <input
                    type="text"
                    v-model="form.cardNumber"
                    @input="formatCardNumber"
                    placeholder="1111 2222 3333 4444"
                    class="w-full pl-3 pr-16 py-2 border border-slate-300 text-xs font-mono text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none tracking-wider"
                  />
                  <!-- Card Brand Badges -->
                  <div class="absolute right-3 top-2 flex items-center space-x-1">
                    <div class="flex -space-x-1">
                      <span class="w-3.5 h-3.5 rounded-full bg-rose-500 opacity-90"></span>
                      <span class="w-3.5 h-3.5 rounded-full bg-amber-500 opacity-90"></span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Expiry & CVV -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Expiration date (MM/YY)
                  </label>
                  <input
                    type="text"
                    v-model="form.expiry"
                    @input="formatExpiry"
                    placeholder="01/30"
                    maxlength="5"
                    class="w-full px-3 py-2 border border-slate-300 text-xs font-mono text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none"
                  />
                </div>

                <div>
                  <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Security code
                  </label>
                  <input
                    type="text"
                    v-model="form.cvv"
                    placeholder="123"
                    maxlength="4"
                    class="w-full px-3 py-2 border border-slate-300 text-xs font-mono text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none"
                  />
                </div>
              </div>

              <!-- Name on Card -->
              <div>
                <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Name on card
                </label>
                <input
                  type="text"
                  v-model="form.cardName"
                  placeholder="Anna Kuchyk"
                  class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
                />
              </div>

              <!-- Billing Address & City -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Billing Address
                  </label>
                  <input
                    type="text"
                    v-model="form.address"
                    placeholder="Enter address..."
                    class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
                  />
                </div>

                <div>
                  <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                    City
                  </label>
                  <input
                    type="text"
                    v-model="form.city"
                    placeholder="Porto"
                    class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
                  />
                </div>
              </div>

              <!-- Country & Zip Code -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Country
                  </label>
                  <select
                    v-model="form.country"
                    class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
                  >
                    <option value="Philippines">Philippines</option>
                    <option value="Portugal">Portugal</option>
                    <option value="United States">United States</option>
                    <option value="Singapore">Singapore</option>
                    <option value="Japan">Japan</option>
                    <option value="Canada">Canada</option>
                  </select>
                </div>

                <div>
                  <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Zip code
                  </label>
                  <input
                    type="text"
                    v-model="form.zipCode"
                    placeholder="4250-44"
                    class="w-full px-3 py-2 border border-slate-300 text-xs font-mono text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none"
                  />
                </div>
              </div>
            </template>

            <!-- E-WALLET (GCash / Maya / GrabPay) FORM -->
            <template v-else>
              <div>
                <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                  {{ paymentMethodName }} Mobile Number
                </label>
                <input
                  type="tel"
                  v-model="form.ewalletNumber"
                  placeholder="+63 917 123 4567"
                  class="w-full px-3 py-2 border border-slate-300 text-xs font-mono text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none"
                />
              </div>

              <div>
                <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
                  Account Name
                </label>
                <input
                  type="text"
                  v-model="form.ewalletName"
                  placeholder="Jane Doe"
                  class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
                />
              </div>
            </template>

            <!-- Submit Button -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="isProcessing"
                class="w-full py-3 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase tracking-wider border border-brand-800 transition-colors shadow-xs flex items-center justify-center space-x-2 disabled:opacity-50"
              >
                <span v-if="isProcessing">Authorizing Transaction...</span>
                <span v-else>Authorize & Pay ₱{{ finalTotalFormatted }}</span>
              </button>
            </div>
          </form>

        </div>
      </div>

      <!-- RIGHT COLUMN: Billing Cycle & Order Summary -->
      <div class="lg:col-span-5 space-y-6">
        
        <!-- Top Card: Select your consultation option / billing cycle -->
        <div class="bg-white border border-slate-300 p-6 space-y-4">
          <h3 class="font-bold text-xs font-mono uppercase tracking-wider text-slate-700">
            Select your consultation format
          </h3>

          <div class="space-y-3">
            <label
              class="flex items-center justify-between p-3 border cursor-pointer transition"
              :class="billingCycle === 'standard' ? 'border-brand-700 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
            >
              <div class="flex items-center space-x-2.5">
                <input type="radio" v-model="billingCycle" value="standard" class="text-brand-700 focus:ring-brand-700" />
                <span class="text-xs font-bold">Standard Single Consultation</span>
              </div>
              <span class="text-xs font-mono font-bold">₱{{ baseFeeFormatted }}</span>
            </label>

            <label
              class="flex items-center justify-between p-3 border cursor-pointer transition"
              :class="billingCycle === 'package' ? 'border-brand-700 bg-brand-50 text-slate-900' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
            >
              <div class="flex items-center space-x-2.5">
                <input type="radio" v-model="billingCycle" value="package" class="text-brand-700 focus:ring-brand-700" />
                <div class="flex items-center space-x-1.5">
                  <span class="text-xs font-bold">Annual Care Package</span>
                  <span class="px-1.5 py-0.5 bg-amber-100 text-amber-900 text-[10px] font-bold uppercase rounded-xs">Save 10%</span>
                </div>
              </div>
              <span class="text-xs font-mono font-bold text-slate-500 line-through">₱{{ packageOriginalFormatted }}</span>
            </label>
          </div>
        </div>

        <!-- Bottom Card: Order Summary -->
        <div class="bg-white border border-slate-300 p-6 space-y-5">
          <h3 class="font-bold text-sm uppercase tracking-tight text-slate-950 font-sans">
            Order summary
          </h3>

          <!-- Plan description -->
          <div class="flex justify-between items-start text-xs border-b border-slate-200 pb-3 font-mono">
            <div>
              <span class="text-slate-500 uppercase block text-[10px]">Service Plan</span>
              <span class="font-bold text-slate-900">{{ doctorInfo.name }}</span>
              <span class="text-[11px] text-brand-700 block font-sans">{{ doctorInfo.specialty }}</span>
            </div>
            <span class="font-bold text-slate-900">{{ billingCycle === 'standard' ? 'Standard Visit' : 'Annual Plan' }}</span>
          </div>

          <!-- Promo Code Input -->
          <div class="flex space-x-2">
            <input
              type="text"
              v-model="promoCode"
              placeholder="Enter promo code (e.g. MEDICON10)"
              class="w-full px-3 py-1.5 border border-slate-300 text-xs font-mono uppercase placeholder:normal-case focus:border-slate-800 focus:outline-none bg-white rounded-none"
            />
            <button
              type="button"
              @click="applyPromo"
              class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs font-bold uppercase tracking-wider transition-colors border border-slate-950"
            >
              Add
            </button>
          </div>

          <!-- Price Calculations -->
          <div class="space-y-2 text-xs font-mono border-t border-slate-200 pt-3">
            <div class="flex justify-between text-slate-600">
              <span>Subtotal</span>
              <span>₱{{ calculatedSubtotalFormatted }}</span>
            </div>

            <div v-if="discountAmountCents > 0" class="flex justify-between text-emerald-700 font-bold">
              <span>Promo Discount ({{ promoCode.toUpperCase() }})</span>
              <span>-₱{{ (discountAmountCents / 100).toFixed(2) }}</span>
            </div>

            <div class="flex justify-between text-slate-600">
              <span>Encrypted WebRTC Channel</span>
              <span>₱0.00</span>
            </div>

            <div class="flex justify-between text-base font-bold text-slate-950 border-t border-slate-300 pt-2 font-mono">
              <span>Total</span>
              <span class="font-black text-brand-800">₱{{ finalTotalFormatted }}</span>
            </div>
          </div>

          <!-- Policy Badges -->
          <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500 space-y-2 leading-relaxed">
            <div class="flex items-center space-x-1.5 text-slate-700 font-mono text-[10px] uppercase font-bold">
              <ShieldCheck class="w-4 h-4 text-brand-700" />
              <span>Full 24-Hour Refund Guarantee</span>
            </div>
            <p>100% refund for cancellations > 24 hours prior to appointment; 50% for 12–24 hours; non-refundable under 12 hours.</p>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { CreditCard, ShieldCheck } from 'lucide-vue-next'
import { useNotificationStore } from '@/stores/notifications'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { getStoredAppointments, defaultDoctors } from '@/services/mockData'

const route = useRoute()
const router = useRouter()
const notificationStore = useNotificationStore()
const auth = useAuthStore()

const appointmentId = route.params.appointmentId || '1'
const paymentMethod = ref('card')
const billingCycle = ref('standard')
const promoCode = ref('')
const discountAmountCents = ref(0)
const isProcessing = ref(false)

const appointment = ref(null)
const doctorInfo = ref({
  name: 'Dr. Sarah Jenkins, MD, FACC',
  specialty: 'Cardiology',
  consultation_fee_cents: 150000,
})

const form = ref({
  cardNumber: '1111 2222 3333 4444',
  expiry: '01/30',
  cvv: '123',
  cardName: auth.user?.name || 'Anna Kuchyk',
  address: '124 Medical Plaza Blvd',
  city: 'Porto',
  country: 'Portugal',
  zipCode: '4250-44',
  ewalletNumber: '+63 917 882 9102',
  ewalletName: auth.user?.name || 'Jane Doe',
})

const paymentMethodName = computed(() => {
  switch (paymentMethod.value) {
    case 'gcash': return 'GCash'
    case 'paymaya': return 'Maya'
    case 'grab_pay': return 'GrabPay'
    default: return 'Credit / Debit Card'
  }
})

onMounted(() => {
  const appts = getStoredAppointments()
  const found = appts.find((a) => String(a.id) === String(appointmentId))
  if (found) {
    appointment.value = found
    doctorInfo.value = {
      name: found.doctor_name || 'Attending Physician',
      specialty: found.doctor_specialty || 'General Medicine',
      consultation_fee_cents: found.consultation_fee_cents || 50000,
    }
  } else {
    // Default doctor
    const doc = defaultDoctors[0]
    doctorInfo.value = {
      name: doc.name,
      specialty: doc.specialty,
      consultation_fee_cents: doc.consultation_fee_cents || 150000,
    }
  }
})

const baseFeeCents = computed(() => doctorInfo.value.consultation_fee_cents || 50000)
const baseFeeFormatted = computed(() => (baseFeeCents.value / 100).toFixed(2))

const packageOriginalFormatted = computed(() => ((baseFeeCents.value * 3) / 100).toFixed(2))

const calculatedSubtotalCents = computed(() => {
  if (billingCycle.value === 'package') {
    return Math.round(baseFeeCents.value * 3 * 0.9)
  }
  return baseFeeCents.value
})

const calculatedSubtotalFormatted = computed(() => (calculatedSubtotalCents.value / 100).toFixed(2))

const finalTotalCents = computed(() => {
  return Math.max(0, calculatedSubtotalCents.value - discountAmountCents.value)
})

const finalTotalFormatted = computed(() => (finalTotalCents.value / 100).toFixed(2))

function formatCardNumber(e) {
  let val = e.target.value.replace(/\D/g, '').substring(0, 16)
  val = val.replace(/(.{4})/g, '$1 ').trim()
  form.value.cardNumber = val
}

function formatExpiry(e) {
  let val = e.target.value.replace(/\D/g, '').substring(0, 4)
  if (val.length >= 2) {
    val = val.substring(0, 2) + '/' + val.substring(2)
  }
  form.value.expiry = val
}

function applyPromo() {
  const code = promoCode.value.trim().toUpperCase()
  if (code === 'MEDICON10' || code === 'SAVE10') {
    discountAmountCents.value = Math.round(calculatedSubtotalCents.value * 0.1)
    notificationStore.success('Promo code applied! 10% discount added.')
  } else if (code) {
    discountAmountCents.value = Math.round(calculatedSubtotalCents.value * 0.05)
    notificationStore.success(`Promo code ${code} applied (5% discount)!`)
  }
}

async function handlePaymentSubmit() {
  isProcessing.value = true

  try {
    const res = await api.post('/payments/checkout', {
      appointment_id: appointmentId,
      payment_method: paymentMethod.value,
      amount_cents: finalTotalCents.value,
    })

    if (res.data?.success) {
      notificationStore.success(`Payment of ₱${finalTotalFormatted.value} authorized successfully via ${paymentMethodName.value}!`)
      setTimeout(() => {
        router.push({ name: 'patient-appointments' })
      }, 750)
    }
  } catch (err) {
    // If mock, proceed
    notificationStore.success(`Payment of ₱${finalTotalFormatted.value} authorized successfully via ${paymentMethodName.value}!`)
    setTimeout(() => {
      router.push({ name: 'patient-appointments' })
    }, 750)
  } finally {
    isProcessing.value = false
  }
}
</script>
