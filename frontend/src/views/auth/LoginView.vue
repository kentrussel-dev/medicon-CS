<template>
  <div>
    <div class="text-left mb-6 pb-3 border-b border-slate-200">
      <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight">Identity Authentication</h3>
      <p class="text-xs text-slate-600 mt-0.5">Enter authorized clinical credentials to access your account</p>
    </div>

    <!-- Quick Demo Logins Pill Strip -->
    <div class="mb-5 p-3 bg-slate-50 border border-slate-300">
      <p class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500 mb-2">
        Fast Role Selection (Testing Profiles)
      </p>
      <div class="grid grid-cols-3 gap-2">
        <button
          type="button"
          @click="fillDemo('patient@medicon.health')"
          class="px-2 py-1.5 bg-white border border-slate-300 text-xs font-mono font-bold text-slate-800 hover:bg-slate-100 hover:border-slate-400 transition-colors uppercase"
        >
          [Patient]
        </button>
        <button
          type="button"
          @click="fillDemo('sarah.jenkins@medicon.health')"
          class="px-2 py-1.5 bg-white border border-slate-300 text-xs font-mono font-bold text-slate-800 hover:bg-slate-100 hover:border-slate-400 transition-colors uppercase"
        >
          [Doctor]
        </button>
        <button
          type="button"
          @click="fillDemo('admin@medicon.health')"
          class="px-2 py-1.5 bg-white border border-slate-300 text-xs font-mono font-bold text-slate-800 hover:bg-slate-100 hover:border-slate-400 transition-colors uppercase"
        >
          [Admin]
        </button>
      </div>
    </div>

    <form @submit.prevent="handleLogin" class="space-y-4">
      <div>
        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address</label>
        <input
          type="email"
          v-model="email"
          required
          autocomplete="email"
          placeholder="user@medicon.health"
          class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
        />
      </div>

      <div>
        <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Access Password</label>
        <input
          type="password"
          v-model="password"
          required
          autocomplete="current-password"
          placeholder="••••••••"
          class="w-full px-3 py-2 border border-slate-300 text-sm focus:border-brand-600 focus:outline-none bg-white rounded-none font-sans"
        />
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider border border-brand-700 transition-colors disabled:opacity-50 flex items-center justify-center space-x-2"
      >
        <span v-if="loading">Verifying Credentials...</span>
        <span v-else>Authenticate &bull; Sign In</span>
      </button>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-200 text-center text-xs text-slate-600">
      Need patient access?
      <router-link to="/register" class="font-bold text-brand-600 hover:underline uppercase font-mono ml-1">
        Register Account
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
    // Handled
  } finally {
    loading.value = false
  }
}
</script>
