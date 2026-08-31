<template>
  <div>
    <div class="text-left mb-6 pb-3 border-b border-slate-200">
      <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight">Patient Account Registration</h3>
      <p class="text-xs text-slate-600 mt-0.5">Register for confidential telehealth consultations and encrypted medical records</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <div>
        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Full Legal Name</label>
        <input
          type="text"
          v-model="form.name"
          required
          placeholder="Jane Doe"
          class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address</label>
          <input
            type="email"
            v-model="form.email"
            required
            placeholder="jane@example.com"
            class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>
        <div>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone</label>
          <input
            type="text"
            v-model="form.phone"
            placeholder="+1 (555) 019-2834"
            class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Date of Birth</label>
          <input
            type="date"
            v-model="form.date_of_birth"
            required
            class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>
        <div>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Biological Gender</label>
          <select
            v-model="form.gender"
            class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
          >
            <option value="F">Female</option>
            <option value="M">Male</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Password</label>
        <input
          type="password"
          v-model="form.password"
          required
          placeholder="Min. 8 characters"
          class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
        />
      </div>

      <div>
        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">
          Known Drug Allergies <span class="text-slate-500 font-normal font-sans">(AES-256 Encrypted)</span>
        </label>
        <input
          type="text"
          v-model="form.allergies"
          placeholder="e.g. Penicillin, Sulfa, Latex, None"
          class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
        />
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider border border-brand-700 transition-colors disabled:opacity-50"
      >
        <span v-if="loading">Processing Registration...</span>
        <span v-else>Register Clinical Account</span>
      </button>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-200 text-center text-xs text-slate-600">
      Existing account holder?
      <router-link to="/login" class="font-bold text-brand-600 hover:underline uppercase font-mono ml-1">
        Sign in here
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const loading = ref(false)

const form = ref({
  name: '',
  email: '',
  phone: '',
  date_of_birth: '1995-05-10',
  gender: 'F',
  password: 'Secret123!',
  allergies: 'None known',
  role: 'patient',
})

const handleRegister = async () => {
  loading.value = true
  try {
    await auth.register(form.value)
    router.push({ name: 'patient-dashboard' })
  } catch (err) {
    // Handled
  } finally {
    loading.value = false
  }
}
</script>
