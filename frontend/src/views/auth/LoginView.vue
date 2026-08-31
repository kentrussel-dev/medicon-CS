<template>
  <div>
    <div class="text-center mb-6">
      <h3 class="text-xl font-black text-slate-900 tracking-tight">Sign in to your account</h3>
      <p class="text-xs text-slate-500 mt-1">Access your health records and clinical schedule</p>
    </div>

    <!-- Quick Demo Logins Pill Strip -->
    <div class="mb-6 p-3.5 bg-slate-50 border border-slate-100 rounded-2xl">
      <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2 text-center">
        Demo Role Quick Access
      </p>
      <div class="grid grid-cols-3 gap-2">
        <button
          type="button"
          @click="fillDemo('patient@medicon.health')"
          class="px-2 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-brand-50 hover:border-brand-300 hover:text-brand-700 transition-all shadow-2xs"
        >
          Patient
        </button>
        <button
          type="button"
          @click="fillDemo('sarah.jenkins@medicon.health')"
          class="px-2 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-brand-50 hover:border-brand-300 hover:text-brand-700 transition-all shadow-2xs"
        >
          Doctor
        </button>
        <button
          type="button"
          @click="fillDemo('admin@medicon.health')"
          class="px-2 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-brand-50 hover:border-brand-300 hover:text-brand-700 transition-all shadow-2xs"
        >
          Admin
        </button>
      </div>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
        <input
          type="email"
          v-model="email"
          required
          autocomplete="email"
          placeholder="name@provider.com"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
        />
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
        <input
          type="password"
          v-model="password"
          required
          autocomplete="current-password"
          placeholder="••••••••"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
        />
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full mt-2 py-3.5 px-4 rounded-2xl font-black text-xs uppercase tracking-wider bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-md shadow-brand-600/25 disabled:opacity-50 flex items-center justify-center space-x-2"
      >
        <span v-if="loading">Signing in...</span>
        <span v-else>Sign In to Medicon</span>
      </button>
    </form>

    <div class="mt-6 text-center text-xs text-slate-500">
      Don't have an account?
      <router-link to="/register" class="font-bold text-brand-600 hover:text-brand-700 underline">
        Create patient account
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('patient@medicon.health')
const password = ref('Secret123!')
const loading = ref(false)

const fillDemo = (demoEmail) => {
  email.value = demoEmail
  password.value = 'Secret123!'
}

const handleLogin = async () => {
  loading.value = true
  try {
    const user = await auth.login({ email: email.value, password: password.value })
    if (route.query.redirect) {
      router.push(route.query.redirect)
    } else if (user.role === 'admin') {
      router.push({ name: 'admin-dashboard' })
    } else if (user.role === 'doctor') {
      router.push({ name: 'doctor-dashboard' })
    } else {
      router.push({ name: 'patient-dashboard' })
    }
  } catch (err) {
    // Interceptor handled toast
  } finally {
    loading.value = false
  }
}
</script>
