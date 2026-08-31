<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between">
      <div>
        <h2 class="text-xl font-black text-slate-900 dark:text-white">Account & Security Settings</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage your profile information, two-factor authentication, and data compliance</p>
      </div>
      <Badge :variant="auth.role">{{ auth.role }}</Badge>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm space-y-6">
      <h3 class="font-bold text-base text-slate-900 dark:text-white">Personal Information</h3>
      <form @submit.prevent="handleUpdateProfile" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Full Name</label>
            <input
              type="text"
              v-model="profileForm.name"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-750 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
            <input
              type="email"
              v-model="profileForm.email"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-750 dark:text-white"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Phone Number</label>
          <input
            type="text"
            v-model="profileForm.phone"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-750 dark:text-white"
          />
        </div>

        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="savingProfile"
            class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs disabled:opacity-50"
          >
            <span v-if="savingProfile">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Two-Factor Authentication (2FA) Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center space-x-2">
            <span>Two-Factor Authentication (2FA)</span>
            <span v-if="is2FaEnabled" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
              Active
            </span>
            <span v-else class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400">
              Disabled
            </span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Add an extra layer of security using standard Time-based One-Time Password (TOTP) authenticator apps.
          </p>
        </div>

        <button
          v-if="!is2FaEnabled && !show2FaSetup"
          type="button"
          @click="start2FaSetup"
          class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-xs"
        >
          Enable 2FA
        </button>

        <button
          v-else-if="is2FaEnabled"
          type="button"
          @click="disable2Fa"
          class="px-4 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/40 text-xs font-bold transition-all"
        >
          Disable 2FA
        </button>
      </div>

      <!-- 2FA Setup Flow Drawer -->
      <div v-if="show2FaSetup" class="p-6 bg-slate-50 dark:bg-slate-750 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-5 animate-in fade-in duration-200">
        <h4 class="text-sm font-bold text-slate-900 dark:text-white">Step 1: Scan Authenticator QR Code</h4>
        <p class="text-xs text-slate-600 dark:text-slate-400">
          Open Google Authenticator, Authy, or 1Password and scan this QR code, or manually enter the secret key below:
        </p>

        <!-- Simulated QR Visual Box -->
        <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
          <div class="w-32 h-32 bg-slate-900 rounded-lg p-2 flex items-center justify-center text-white text-center font-mono text-[10px] shadow-md">
            <div>
              <div class="w-24 h-24 border-2 border-white/40 border-dashed rounded flex items-center justify-center">
                [TOTP QR CODE]
              </div>
            </div>
          </div>
          <div class="space-y-2 flex-1">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Secret Key</label>
            <div class="flex items-center space-x-2">
              <input
                type="text"
                readonly
                :value="twoFaSecret"
                class="font-mono text-sm px-3 py-1.5 bg-slate-100 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 w-full text-slate-900 dark:text-white"
              />
              <button
                type="button"
                @click="copySecret"
                class="px-3 py-1.5 text-xs font-semibold bg-slate-200 dark:bg-slate-600 hover:bg-slate-300 rounded-lg text-slate-800 dark:text-slate-200"
              >
                Copy
              </button>
            </div>
          </div>
        </div>

        <!-- Recovery Codes -->
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            Emergency Recovery Codes (Save these securely)
          </label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-white dark:bg-slate-800 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
            <div v-for="code in recoveryCodes" :key="code" class="p-1 bg-slate-100 dark:bg-slate-700 rounded text-center">
              {{ code }}
            </div>
          </div>
        </div>

        <!-- Step 2: Verification Code -->
        <div class="space-y-3 pt-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            Step 2: Enter 6-Digit Authenticator Code
          </label>
          <div class="flex space-x-3">
            <input
              type="text"
              maxlength="6"
              v-model="totpVerificationCode"
              placeholder="123456"
              class="w-44 px-4 py-2 font-mono text-center tracking-widest text-lg font-bold rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500"
            />
            <button
              type="button"
              @click="confirm2Fa"
              :disabled="totpVerificationCode.length !== 6"
              class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold disabled:opacity-50"
            >
              Verify & Enable
            </button>
            <button
              type="button"
              @click="show2FaSetup = false"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 dark:text-slate-400"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Privacy & HIPAA Compliance Rights -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm space-y-6">
      <div>
        <h3 class="font-bold text-base text-slate-900 dark:text-white">Data Compliance & Privacy Rights</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          In compliance with the Data Privacy Act (DPA), GDPR, and HIPAA standards, you have full control over your clinical data.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Export Data Card -->
        <div class="p-5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-750 flex flex-col justify-between space-y-3">
          <div>
            <h4 class="font-bold text-sm text-slate-900 dark:text-white flex items-center space-x-2">
              <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              <span>Download Health Data Export</span>
            </h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Download all your personal profile details, clinical encounter summaries, active prescriptions, and payment receipts in a portable JSON format.
            </p>
          </div>
          <button
            type="button"
            @click="downloadDataExport"
            :disabled="exportingData"
            class="w-full py-2.5 px-4 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-800 dark:text-slate-200 font-bold text-xs shadow-xs transition flex items-center justify-center space-x-2"
          >
            <span v-if="exportingData">Generating Export...</span>
            <span v-else>Download Complete Health Export (JSON)</span>
          </button>
        </div>

        <!-- Account Deletion Card -->
        <div class="p-5 rounded-2xl border border-red-100 dark:border-red-950/60 bg-red-50/50 dark:bg-red-950/20 flex flex-col justify-between space-y-3">
          <div>
            <h4 class="font-bold text-sm text-red-900 dark:text-red-300 flex items-center space-x-2">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span>Right to be Forgotten & Deletion</span>
            </h4>
            <p class="text-xs text-red-700/80 dark:text-red-400/80 mt-1">
              Permanently anonymizes your personal information (name, email, phone, credentials) while preserving legally mandated non-identifying clinical audit records.
            </p>
          </div>
          <button
            type="button"
            @click="showDeleteModal = true"
            class="w-full py-2.5 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs shadow-xs transition"
          >
            Request Account Deletion
          </button>
        </div>
      </div>
    </div>

    <!-- Password Change Section -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-100 dark:border-slate-700 shadow-sm space-y-6">
      <h3 class="font-bold text-base text-slate-900 dark:text-white">Change Password</h3>
      <form @submit.prevent="handleChangePassword" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Current Password</label>
          <input
            type="password"
            v-model="passwordForm.current_password"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-750 dark:text-white"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">New Password</label>
            <input
              type="password"
              v-model="passwordForm.password"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-750 dark:text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password</label>
            <input
              type="password"
              v-model="passwordForm.password_confirmation"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-brand-500 dark:bg-slate-750 dark:text-white"
            />
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="savingPassword"
            class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-xs disabled:opacity-50"
          >
            <span v-if="savingPassword">Updating...</span>
            <span v-else>Update Password</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Account Deletion Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-red-200 dark:border-red-900">
        <h3 class="text-lg font-bold text-red-600 dark:text-red-400">Confirm Account Deletion</h3>
        <p class="text-xs text-slate-600 dark:text-slate-300">
          This action is permanent. All identifying credentials will be wiped and your account will be immediately closed.
        </p>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm Password</label>
            <input
              type="password"
              v-model="deleteConfirmPassword"
              placeholder="Enter your password..."
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm dark:bg-slate-750 dark:text-white"
            />
          </div>
          <div v-if="is2FaEnabled">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">2FA Authenticator Code</label>
            <input
              type="text"
              v-model="delete2FaCode"
              placeholder="6-digit TOTP or recovery code"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm dark:bg-slate-750 dark:text-white"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-2">
          <button
            type="button"
            @click="showDeleteModal = false"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="executeAccountDeletion"
            :disabled="!deleteConfirmPassword"
            class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold disabled:opacity-50"
          >
            Permanently Anonymize & Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notifications'
import Badge from '@/components/common/Badge.vue'
import api from '@/services/api'

const auth = useAuthStore()
const notificationStore = useNotificationStore()

const savingProfile = ref(false)
const savingPassword = ref(false)
const exportingData = ref(false)

const is2FaEnabled = ref(false)
const show2FaSetup = ref(false)
const twoFaSecret = ref('')
const recoveryCodes = ref([])
const totpVerificationCode = ref('')

const showDeleteModal = ref(false)
const deleteConfirmPassword = ref('')
const delete2FaCode = ref('')

const profileForm = ref({
  name: auth.user?.name || '',
  email: auth.user?.email || '',
  phone: auth.user?.phone || '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

onMounted(async () => {
  try {
    const res = await api.get('/auth/me')
    is2FaEnabled.value = !!res.data?.two_factor_enabled
  } catch (err) {
    // fallback
  }
})

const handleUpdateProfile = async () => {
  savingProfile.value = true
  try {
    await auth.updateProfile(profileForm.value)
  } finally {
    savingProfile.value = false
  }
}

const handleChangePassword = async () => {
  savingPassword.value = true
  try {
    await auth.updatePassword(passwordForm.value)
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
  } finally {
    savingPassword.value = false
  }
}

async function start2FaSetup() {
  try {
    const res = await api.post('/auth/2fa/enable')
    if (res.data?.success) {
      twoFaSecret.value = res.data.data.secret
      recoveryCodes.value = res.data.data.recovery_codes
      show2FaSetup.value = true
    }
  } catch (err) {
    notificationStore.error('Failed to initiate 2FA setup.')
  }
}

async function confirm2Fa() {
  try {
    const res = await api.post('/auth/2fa/confirm', {
      code: totpVerificationCode.value,
    })
    if (res.data?.success) {
      is2FaEnabled.value = true
      show2FaSetup.value = false
      totpVerificationCode.value = ''
      notificationStore.success('Two-factor authentication is now enabled on your account!')
    }
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Invalid code. Please try again.')
  }
}

async function disable2Fa() {
  const pwd = prompt('Enter your password to disable Two-Factor Authentication:')
  if (!pwd) return

  try {
    const res = await api.post('/auth/2fa/disable', { password: pwd })
    if (res.data?.success) {
      is2FaEnabled.value = false
      notificationStore.success('Two-factor authentication disabled.')
    }
  } catch (err) {
    notificationStore.error('Failed to disable 2FA.')
  }
}

function copySecret() {
  navigator.clipboard.writeText(twoFaSecret.value)
  notificationStore.success('Secret key copied to clipboard!')
}

async function downloadDataExport() {
  exportingData.value = true
  try {
    const res = await api.get('/compliance/export')
    if (res.data?.success) {
      const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(res.data.data, null, 2))
      const downloadAnchor = document.createElement('a')
      downloadAnchor.setAttribute('href', dataStr)
      downloadAnchor.setAttribute('download', res.data.filename || 'medicon_health_data_export.json')
      document.body.appendChild(downloadAnchor)
      downloadAnchor.click()
      downloadAnchor.remove()
      notificationStore.success('Health data export downloaded successfully!')
    }
  } catch (err) {
    notificationStore.error('Failed to generate health data export.')
  } finally {
    exportingData.value = false
  }
}

async function executeAccountDeletion() {
  try {
    const res = await api.post('/compliance/account-deletion', {
      password: deleteConfirmPassword.value,
      two_factor_code: delete2FaCode.value || undefined,
    })

    if (res.data?.success) {
      showDeleteModal.value = false
      alert(res.data.message)
      window.location.href = '/login'
    }
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Account deletion failed.')
  }
}
</script>
