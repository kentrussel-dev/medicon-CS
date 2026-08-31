<template>
  <div>
    <!-- Standard Login Form -->
    <div v-if="!twoFactorRequired">
      <div class="text-left mb-6 pb-3 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-tight">Identity Authentication</h3>
        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Enter authorized clinical credentials to access your account</p>
      </div>

      <!-- Quick Demo Logins Pill Strip -->
      <div class="mb-5 p-3 bg-slate-50 dark:bg-slate-750 border border-slate-300 dark:border-slate-700">
        <p class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
          Fast Role Selection (Testing Profiles)
        </p>
        <div class="grid grid-cols-3 gap-2">
          <button
            type="button"
            @click="fillDemo('patient@medicon.health')"
            class="px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors uppercase"
          >
            [Patient]
          </button>
          <button
            type="button"
            @click="fillDemo('sarah.jenkins@medicon.health')"
            class="px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors uppercase"
          >
            [Doctor]
          </button>
          <button
            type="button"
            @click="fillDemo('admin@medicon.health')"
            class="px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors uppercase"
          >
            [Admin]
          </button>
        </div>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
          <input
            type="email"
            v-model="email"
            required
            autocomplete="email"
            placeholder="user@medicon.health"
            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 text-sm focus:border-brand-600 focus:outline-none bg-white dark:bg-slate-800 dark:text-white rounded-none font-sans"
          />
        </div>

        <div>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Access Password</label>
          <input
            type="password"
            v-model="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-700 text-sm focus:border-brand-600 focus:outline-none bg-white dark:bg-slate-800 dark:text-white rounded-none font-sans"
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

      <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 text-center text-xs text-slate-600 dark:text-slate-400">
        Need patient access?
        <router-link to="/register" class="font-bold text-brand-600 hover:underline uppercase font-mono ml-1">
          Register Account
        </router-link>
      </div>
    </div>

    <!-- Two-Factor Authentication Challenge Screen -->
    <div v-else class="space-y-5 animate-in fade-in zoom-in-95 duration-200">
      <div class="text-left pb-3 border-b border-slate-200 dark:border-slate-700">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white uppercase tracking-tight">Two-Factor Authentication</h3>
        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Enter the 6-digit code from your authenticator app or an emergency recovery code</p>
      </div>

      <form @submit.prevent="handle2FaSubmit" class="space-y-4">
        <div v-if="!useRecoveryCode">
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
            6-Digit TOTP Code
          </label>
          <input
            type="text"
            v-model="totpCode"
            maxlength="6"
            placeholder="123456"
            autofocus
            class="w-full px-4 py-3 font-mono text-center tracking-widest text-xl font-black border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white rounded-none focus:border-indigo-600 focus:outline-none"
          />
        </div>

        <div v-else>
          <label class="block text-xs font-mono font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
            Emergency Recovery Code
          </label>
          <input
            type="text"
            v-model="recoveryCodeInput"
            placeholder="XXXX-XXXX"
            autofocus
            class="w-full px-4 py-3 font-mono text-center tracking-wider text-base font-bold border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 dark:text-white rounded-none focus:border-indigo-600 focus:outline-none uppercase"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider border border-indigo-700 transition-colors disabled:opacity-50"
        >
          <span v-if="loading">Verifying 2FA...</span>
          <span v-else>Verify & Sign In</span>
        </button>

        <div class="flex items-center justify-between text-xs pt-2">
          <button
            type="button"
            @click="useRecoveryCode = !useRecoveryCode"
            class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold"
          >
            {{ useRecoveryCode ? 'Use 6-Digit Authenticator Code' : 'Use Emergency Recovery Code' }}
          </button>

          <button
            type="button"
            @click="cancel2Fa"
            class="text-slate-500 hover:text-slate-700 dark:text-slate-400"
          >
            Cancel
          </button>
        </div>
      </form>
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

const twoFactorRequired = ref(false)
const twoFactorToken = ref('')
const totpCode = ref('')
const recoveryCodeInput = ref('')
const useRecoveryCode = ref(false)

const fillDemo = (demoEmail) => {
  email.value = demoEmail
  password.value = 'Secret123!'
}

const redirectAfterAuth = (user) => {
  if (route.query.redirect) {
    router.push(route.query.redirect)
  } else if (user.role === 'admin') {
    router.push({ name: 'admin-dashboard' })
  } else if (user.role === 'doctor') {
    router.push({ name: 'doctor-dashboard' })
  } else {
    router.push({ name: 'patient-dashboard' })
  }
}

const handleLogin = async () => {
  loading.value = true
  try {
    const res = await auth.login({ email: email.value, password: password.value })

    if (res?.two_factor_required) {
      twoFactorRequired.value = true
      twoFactorToken.value = res.two_factor_token
      return
    }

    redirectAfterAuth(res)
  } catch (err) {
    // Handled in store
  } finally {
    loading.value = false
  }
}

const handle2FaSubmit = async () => {
  loading.value = true
  try {
    const user = await auth.verify2FaChallenge({
      two_factor_token: twoFactorToken.value,
      code: useRecoveryCode.value ? null : totpCode.value,
      recovery_code: useRecoveryCode.value ? recoveryCodeInput.value : null,
    })

    redirectAfterAuth(user)
  } catch (err) {
    // Handled in store
  } finally {
    loading.value = false
  }
}

const cancel2Fa = () => {
  twoFactorRequired.value = false
  twoFactorToken.value = ''
  totpCode.value = ''
  recoveryCodeInput.value = ''
  useRecoveryCode.value = false
}
</script>
