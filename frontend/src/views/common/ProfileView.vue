<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-6 border border-slate-300 shadow-xs flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold uppercase tracking-tight text-slate-950">Account & Security Settings</h2>
        <p class="text-xs font-mono text-slate-500 mt-0.5">Manage your profile credentials, two-factor authentication, and data compliance</p>
      </div>
      <Badge :variant="auth.role">{{ auth.role }}</Badge>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white p-6 border border-slate-300 shadow-xs space-y-5">
      <h3 class="font-bold text-sm uppercase tracking-wider text-slate-900 font-mono">Personal Information</h3>
      <form @submit.prevent="handleUpdateProfile" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Full Name</label>
            <input
              type="text"
              v-model="profileForm.name"
              required
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            />
          </div>
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address</label>
            <input
              type="email"
              v-model="profileForm.email"
              required
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            />
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
          <input
            type="text"
            v-model="profileForm.phone"
            class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>

        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="savingProfile"
            class="px-5 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-mono font-bold uppercase tracking-wider border border-brand-800 transition-colors shadow-xs disabled:opacity-50"
          >
            <span v-if="savingProfile">Saving...</span>
            <span v-else>Save Profile</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Two-Factor Authentication (2FA) Section -->
    <div class="bg-white p-6 border border-slate-300 shadow-xs space-y-5">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="font-bold text-sm uppercase tracking-wider text-slate-900 font-mono flex items-center space-x-2">
            <span>Two-Factor Authentication (2FA)</span>
            <span v-if="is2FaEnabled" class="px-2 py-0.5 text-[9px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">
              Active
            </span>
            <span v-else class="px-2 py-0.5 text-[9px] font-mono font-bold bg-slate-100 text-slate-600 border border-slate-300 uppercase">
              Disabled
            </span>
          </h3>
          <p class="text-xs text-slate-500 font-mono mt-0.5">
            Add an extra layer of security using standard Time-based One-Time Password (TOTP) authenticator apps.
          </p>
        </div>

        <button
          v-if="!is2FaEnabled && !show2FaSetup"
          type="button"
          @click="start2FaSetup"
          class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-mono font-bold uppercase border border-slate-950 transition-colors"
        >
          Enable 2FA
        </button>

        <button
          v-else-if="is2FaEnabled"
          type="button"
          @click="disable2Fa"
          class="px-4 py-2 border border-red-300 text-red-700 hover:bg-red-50 text-xs font-mono font-bold uppercase transition-colors"
        >
          Disable 2FA
        </button>
      </div>

      <!-- 2FA Setup Flow Drawer -->
      <div v-if="show2FaSetup" class="p-5 bg-slate-50 border border-slate-300 space-y-4 animate-in fade-in duration-150">
        <h4 class="text-xs font-mono font-bold uppercase text-slate-900">Step 1: Scan Authenticator QR Code</h4>
        <p class="text-xs text-slate-600">
          Open Google Authenticator, Authy, or 1Password and scan this QR code, or manually enter the secret key below:
        </p>

        <!-- Simulated QR Visual Box -->
        <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6 bg-white p-4 border border-slate-300">
          <div class="w-28 h-28 bg-slate-900 p-2 flex items-center justify-center text-white text-center font-mono text-[9px]">
            <div class="w-24 h-24 border border-white/40 border-dashed flex items-center justify-center">
              [TOTP QR CODE]
            </div>
          </div>
          <div class="space-y-2 flex-1 w-full">
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-600">Secret Key</label>
            <div class="flex items-center space-x-2">
              <input
                type="text"
                readonly
                :value="twoFaSecret"
                class="font-mono text-xs px-3 py-1.5 bg-slate-50 border border-slate-300 w-full text-slate-900 select-all"
              />
              <button
                type="button"
                @click="copySecret"
                class="px-3 py-1.5 text-xs font-mono font-bold uppercase bg-slate-200 hover:bg-slate-300 border border-slate-300 text-slate-800"
              >
                Copy
              </button>
            </div>
          </div>
        </div>

        <!-- Recovery Codes -->
        <div class="space-y-2">
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700">
            Emergency Recovery Codes (Save these securely)
          </label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-white p-3 border border-slate-300 font-mono text-xs font-bold text-slate-900">
            <div v-for="code in recoveryCodes" :key="code" class="p-1.5 bg-slate-100 border border-slate-200 text-center">
              {{ code }}
            </div>
          </div>
        </div>

        <!-- Step 2: Verification Code -->
        <div class="space-y-3 pt-2">
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700">
            Step 2: Enter 6-Digit Authenticator Code
          </label>
          <div class="flex space-x-3">
            <input
              type="text"
              maxlength="6"
              v-model="totpVerificationCode"
              placeholder="123456"
              class="w-36 px-3 py-1.5 font-mono text-center tracking-widest text-base font-bold border border-slate-300 bg-white focus:border-slate-800 focus:outline-none"
            />
            <button
              type="button"
              @click="confirm2Fa"
              :disabled="totpVerificationCode.length !== 6"
              class="px-4 py-1.5 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase border border-brand-800 disabled:opacity-50"
            >
              Verify & Enable
            </button>
            <button
              type="button"
              @click="show2FaSetup = false"
              class="px-3 py-1.5 font-mono text-xs font-bold uppercase text-slate-600 hover:bg-slate-200 border border-slate-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Privacy & HIPAA Compliance Rights -->
    <div class="bg-white p-6 border border-slate-300 shadow-xs space-y-5">
      <div>
        <h3 class="font-bold text-sm uppercase tracking-wider text-slate-900 font-mono">Data Compliance & Privacy Rights</h3>
        <p class="text-xs text-slate-500 font-mono mt-0.5">
          In compliance with the Data Privacy Act (DPA), GDPR, and HIPAA standards, you have full control over your clinical data.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Export Data Card -->
        <div class="p-4 border border-slate-300 bg-slate-50 flex flex-col justify-between space-y-3">
          <div>
            <h4 class="font-bold text-xs uppercase font-mono text-slate-900 flex items-center space-x-2">
              <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              <span>Download Health Data Export</span>
            </h4>
            <p class="text-xs text-slate-600 font-sans mt-1 leading-relaxed">
              Download your profile details, clinical encounter summaries, active prescriptions, and payment receipts in portable JSON.
            </p>
          </div>
          <button
            type="button"
            @click="downloadDataExport"
            :disabled="exportingData"
            class="w-full py-2 px-3 bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 font-mono font-bold text-xs uppercase transition flex items-center justify-center space-x-2"
          >
            <span v-if="exportingData">Generating Export...</span>
            <span v-else>Download Export (JSON)</span>
          </button>
        </div>

        <!-- Account Deletion Card -->
        <div class="p-4 border border-red-200 bg-red-50/40 flex flex-col justify-between space-y-3">
          <div>
            <h4 class="font-bold text-xs uppercase font-mono text-red-900 flex items-center space-x-2">
              <svg class="w-4 h-4 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              <span>Right to be Forgotten</span>
            </h4>
            <p class="text-xs text-red-700 font-sans mt-1 leading-relaxed">
              Permanently anonymizes your personal information while preserving legally mandated non-identifying forensic audit records.
            </p>
          </div>
          <button
            type="button"
            @click="showDeleteModal = true"
            class="w-full py-2 px-3 bg-red-700 hover:bg-red-800 text-white font-mono font-bold text-xs uppercase transition border border-red-800"
          >
            Request Account Deletion
          </button>
        </div>
      </div>
    </div>

    <!-- Password Change Section -->
    <div class="bg-white p-6 border border-slate-300 shadow-xs space-y-5">
      <h3 class="font-bold text-sm uppercase tracking-wider text-slate-900 font-mono">Change Password</h3>
      <form @submit.prevent="handleChangePassword" class="space-y-4">
        <div>
          <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Current Password</label>
          <input
            type="password"
            v-model="passwordForm.current_password"
            required
            class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">New Password</label>
            <input
              type="password"
              v-model="passwordForm.password"
              required
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            />
          </div>
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password</label>
            <input
              type="password"
              v-model="passwordForm.password_confirmation"
              required
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 focus:border-slate-800 focus:outline-none bg-white rounded-none font-sans"
            />
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="savingPassword"
            class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-mono font-bold uppercase tracking-wider border border-slate-950 transition-colors disabled:opacity-50"
          >
            <span v-if="savingPassword">Updating...</span>
            <span v-else>Update Password</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Account Deletion Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white rounded-none shadow-2xl max-w-md w-full p-6 space-y-4 border-2 border-red-700">
        <h3 class="text-sm font-mono font-bold uppercase text-red-700">Confirm Account Deletion</h3>
        <p class="text-xs text-slate-600 font-sans leading-relaxed">
          This action is permanent. All identifying credentials will be wiped and your account will be immediately closed.
        </p>

        <div class="space-y-3">
          <div>
            <label class="block text-[10px] font-mono font-bold uppercase text-slate-700 mb-1">Confirm Password</label>
            <input
              type="password"
              v-model="deleteConfirmPassword"
              placeholder="Enter your password..."
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 rounded-none font-sans"
            />
          </div>
          <div v-if="is2FaEnabled">
            <label class="block text-[10px] font-mono font-bold uppercase text-slate-700 mb-1">2FA Authenticator Code</label>
            <input
              type="text"
              v-model="delete2FaCode"
              placeholder="6-digit TOTP or recovery code"
              class="w-full px-3 py-2 border border-slate-300 text-xs text-slate-900 rounded-none font-mono"
            />
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-2">
          <button
            type="button"
            @click="showDeleteModal = false"
            class="px-4 py-2 border border-slate-300 bg-white hover:bg-slate-100 text-xs font-mono font-bold uppercase text-slate-700"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="executeAccountDeletion"
            :disabled="!deleteConfirmPassword"
            class="px-5 py-2 bg-red-700 hover:bg-red-800 text-white text-xs font-mono font-bold uppercase border border-red-800 disabled:opacity-50"
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
