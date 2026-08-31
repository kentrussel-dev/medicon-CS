<template>
  <Modal :is-open="isOpen" title="Provision User Account" subtitle="Create clinical staff, patient, or administrative accounts" size="lg" @close="$emit('close')">
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Full Name</label>
        <input
          type="text"
          v-model="form.name"
          required
          placeholder="e.g. Dr. Arthur Conan"
          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Email Address</label>
          <input
            type="email"
            v-model="form.email"
            required
            placeholder="arthur@medicon.health"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Initial Password</label>
          <input
            type="password"
            v-model="form.password"
            required
            placeholder="Min 8 characters"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Role Permission</label>
          <select
            v-model="form.role"
            required
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
          >
            <option value="patient">Patient</option>
            <option value="doctor">Doctor / Clinician</option>
            <option value="admin">System Administrator</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Contact Phone</label>
          <input
            type="text"
            v-model="form.phone"
            placeholder="+1 (555) 000-1122"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>

      <!-- Doctor Specific Inputs -->
      <div v-if="form.role === 'doctor'" class="space-y-4 pt-2 border-t border-slate-100">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Medical Specialty</label>
            <input
              type="text"
              v-model="form.specialty"
              required
              placeholder="e.g. Cardiology, Neurology"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Medical License Number</label>
            <input
              type="text"
              v-model="form.license_number"
              required
              placeholder="MD-LIC-99881"
              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            />
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Consultation Fee (₱)</label>
          <input
            type="number"
            v-model.number="form.consultation_fee"
            min="0"
            step="5"
            placeholder="75.00"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>
      </div>
    </form>

    <template #footer>
      <button
        type="button"
        @click="$emit('close')"
        class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-200 transition-colors"
      >
        Cancel
      </button>
      <button
        type="button"
        :disabled="submitting"
        @click="handleSubmit"
        class="px-5 py-2 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white transition-colors shadow-sm disabled:opacity-50"
      >
        <span v-if="submitting">Provisioning...</span>
        <span v-else>Provision Account</span>
      </button>
    </template>
  </Modal>
</template>

<script setup>
import { ref } from 'vue'
import Modal from '@/components/common/Modal.vue'
import { useAdminStore } from '@/stores/admin'

defineProps({
  isOpen: Boolean,
})

const emit = defineEmits(['close', 'created'])

const adminStore = useAdminStore()
const submitting = ref(false)

const form = ref({
  name: '',
  email: '',
  password: 'Secret123!',
  role: 'patient',
  phone: '',
  specialty: 'General Practice',
  license_number: 'MD-LIC-' + Math.floor(10000 + Math.random() * 90000),
  consultation_fee: 75.00,
})

const handleSubmit = async () => {
  if (!form.value.name || !form.value.email || !form.value.password) return

  submitting.value = true
  try {
    await adminStore.createUser(form.value)
    emit('created')
    emit('close')
  } catch (err) {
    // Handled
  } finally {
    submitting.value = false
  }
}
</script>
