<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex items-center justify-between">
      <div>
        <h2 class="text-xl font-black text-slate-900">Account & Security Settings</h2>
        <p class="text-xs text-slate-500 mt-0.5">Manage your profile information and encrypted credentials</p>
      </div>
      <Badge :variant="auth.role">{{ auth.role }}</Badge>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
      <h3 class="font-bold text-base text-slate-900">Personal Information</h3>
      <form @submit.prevent="handleUpdateProfile" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name</label>
            <input
              type="text"
              v-model="profileForm.name"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
            <input
              type="email"
              v-model="profileForm.email"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Phone Number</label>
          <input
            type="text"
            v-model="profileForm.phone"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500"
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

    <!-- Password Change Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
      <h3 class="font-bold text-base text-slate-900">Change Password</h3>
      <form @submit.prevent="handleChangePassword" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Current Password</label>
          <input
            type="password"
            v-model="passwordForm.current_password"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">New Password</label>
            <input
              type="password"
              v-model="passwordForm.password"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Confirm New Password</label>
            <input
              type="password"
              v-model="passwordForm.password_confirmation"
              required
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500"
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
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Badge from '@/components/common/Badge.vue'

const auth = useAuthStore()

const savingProfile = ref(false)
const savingPassword = ref(false)

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
</script>
