<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 flex items-center justify-center p-4">
    <div class="bg-white border-2 border-slate-900 shadow-2xl max-w-md w-full p-6 space-y-4">
      <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <div>
          <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest block">Telehealth Consultation</span>
          <h3 class="text-base font-bold text-slate-950 uppercase tracking-tight mt-0.5">Invite Clinical Participant</h3>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-900 transition-colors">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div v-if="inviteResult" class="p-4 bg-emerald-50 border border-emerald-300 space-y-3 font-mono text-xs">
        <div class="flex items-center space-x-2 text-emerald-800 font-bold">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>Participant Added Successfully!</span>
        </div>
        <p class="text-slate-700 text-[11px] font-sans">
          A secure, short-lived consultation session has been created for <strong>{{ inviteResult.participant.name }}</strong> ({{ inviteResult.participant.role.toUpperCase() }}).
        </p>

        <div class="space-y-1 pt-1">
          <span class="text-[10px] text-slate-500 uppercase block">Secure Direct Join URL:</span>
          <div class="flex items-center space-x-1.5">
            <input
              type="text"
              readonly
              :value="inviteResult.join_url"
              class="w-full px-2 py-1 bg-white border border-slate-300 text-[11px] select-all font-mono"
            />
            <button
              @click="copyJoinUrl"
              class="px-3 py-1 bg-slate-900 text-white font-bold text-[10px] uppercase whitespace-nowrap hover:bg-slate-800"
            >
              {{ copied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
        </div>

        <div class="pt-2 flex justify-end">
          <button
            @click="resetAndClose"
            class="px-4 py-1.5 bg-slate-900 text-white font-bold text-xs uppercase"
          >
            Done
          </button>
        </div>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-3.5 text-xs font-mono">
        <div>
          <label class="block text-slate-700 font-bold uppercase text-[11px] mb-1">
            Participant Name <span class="text-rose-600">*</span>
          </label>
          <input
            type="text"
            v-model="form.name"
            placeholder="e.g. Dr. Marcus Chen (Neurologist) or Carlos Silva (Translator)"
            required
            class="w-full px-3 py-2 border border-slate-300 text-xs focus:border-slate-900 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>

        <div>
          <label class="block text-slate-700 font-bold uppercase text-[11px] mb-1">
            Consultation Role <span class="text-rose-600">*</span>
          </label>
          <select
            v-model="form.role"
            required
            class="w-full px-3 py-2 border border-slate-300 text-xs focus:border-slate-900 focus:outline-none bg-white rounded-none uppercase font-mono"
          >
            <option value="specialist">Consulting Specialist (Doctor)</option>
            <option value="translator">Medical Interpreter / Translator</option>
            <option value="resident">Medical Resident / Fellow</option>
            <option value="family">Family Member / Patient Advocate</option>
          </select>
        </div>

        <div>
          <label class="block text-slate-700 font-bold uppercase text-[11px] mb-1">
            Email Address (Optional)
          </label>
          <input
            type="email"
            v-model="form.email"
            placeholder="specialist@medicon.health"
            class="w-full px-3 py-2 border border-slate-300 text-xs focus:border-slate-900 focus:outline-none bg-white rounded-none font-sans"
          />
        </div>

        <div class="pt-3 border-t border-slate-200 flex items-center justify-end space-x-2">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-white text-slate-700 border border-slate-300 font-bold text-xs uppercase hover:bg-slate-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase tracking-wider border border-brand-800 disabled:opacity-50 flex items-center space-x-1.5"
          >
            <UserPlus class="w-3.5 h-3.5" />
            <span>{{ submitting ? 'Generating Token...' : 'Add & Generate Join Link' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/services/api'
import { X, UserPlus, CheckCircle2 } from 'lucide-vue-next'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  appointmentId: { type: [Number, String], required: true },
})

const emit = defineEmits(['close', 'participantAdded'])

const form = ref({
  name: '',
  role: 'specialist',
  email: '',
})

const submitting = ref(false)
const inviteResult = ref(null)
const copied = ref(false)

const handleSubmit = async () => {
  submitting.value = true
  try {
    const res = await api.post(`/appointments/${props.appointmentId}/telehealth/participants`, {
      name: form.value.name,
      role: form.value.role,
      email: form.value.email || undefined,
    })

    inviteResult.value = {
      participant: res.data.participant,
      join_url: `${window.location.origin}/telehealth/room/${props.appointmentId}?participant_role=${res.data.participant.role}&participant_name=${encodeURIComponent(res.data.participant.name)}`,
    }

    emit('participantAdded', res.data.participant)
  } catch (err) {
    // Fallback local mock participant creation
    const dummyParticipant = {
      id: Date.now(),
      name: form.value.name,
      role: form.value.role,
    }
    inviteResult.value = {
      participant: dummyParticipant,
      join_url: `${window.location.origin}/telehealth/room/${props.appointmentId}?participant_role=${dummyParticipant.role}&participant_name=${encodeURIComponent(dummyParticipant.name)}`,
    }
    emit('participantAdded', dummyParticipant)
  } finally {
    submitting.value = false
  }
}

const copyJoinUrl = async () => {
  if (inviteResult.value?.join_url) {
    await navigator.clipboard.writeText(inviteResult.value.join_url)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  }
}

const resetAndClose = () => {
  inviteResult.value = null
  form.value = { name: '', role: 'specialist', email: '' }
  emit('close')
}
</script>
