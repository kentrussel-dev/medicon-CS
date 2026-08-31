<template>
  <div>
    <div class="text-center mb-6">
      <h3 class="text-xl font-black text-slate-900 tracking-tight">Create Patient Account</h3>
      <p class="text-xs text-slate-500 mt-1">Register for confidential telehealth & specialist booking</p>
    </div>

    <form @submit.prevent="handleRegister" class="space-y-4">
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Legal Name</label>
        <input
          type="text"
          v-model="form.name"
          required
          placeholder="Jane Doe"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
          <input
            type="email"
            v-model="form.email"
            required
            placeholder="jane@example.com"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Phone Number</label>
          <input
            type="text"
            v-model="form.phone"
            placeholder="+1 (555) 019-2834"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Date of Birth</label>
          <input
            type="date"
            v-model="form.date_of_birth"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Biological Gender</label>
          <select
            v-model="form.gender"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
          >
            <option value="F">Female</option>
            <option value="M">Male</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
        <input
          type="password"
          v-model="form.password"
          required
          placeholder="Min. 8 characters"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
        />
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
          Known Drug Allergies <span class="text-slate-400 font-normal">(AES-256 Encrypted)</span>
        </label>
        <input
          type="text"
          v-model="form.allergies"
          placeholder="e.g. Penicillin, Sulfa, Latex, None"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
        />
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full mt-2 py-3.5 px-4 rounded-2xl font-black text-xs uppercase tracking-wider bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-md shadow-brand-600/25 disabled:opacity-50"
      >
        <span v-if="loading">Creating Account...</span>
        <span v-else>Register Account</span>
      </button>
    </form>

    <div class="mt-6 text-center text-xs text-slate-500">
      Already have an account?
      <router-link to="/login" class="font-bold text-brand-600 hover:text-brand-700 underline">
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
