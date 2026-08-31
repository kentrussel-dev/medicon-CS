<template>
  <div class="bg-white border border-slate-300 p-5 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
      <div>
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-950">Weekly Clinical Availability Schedule</h3>
        <p class="text-xs text-slate-500 font-mono mt-0.5">Configure active days, operating hours, and consultation slot durations</p>
      </div>
      <button
        @click="saveSchedule"
        :disabled="saving"
        class="px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold uppercase tracking-wider border border-brand-800 transition-colors disabled:opacity-50 flex items-center justify-center space-x-1.5"
      >
        <Save class="w-3.5 h-3.5" />
        <span v-if="saving">Saving Schedule...</span>
        <span v-else>Save Working Hours</span>
      </button>
    </div>

    <!-- Days Grid -->
    <div class="space-y-2">
      <div
        v-for="day in days"
        :key="day.id"
        class="p-3 border transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3"
        :class="day.is_active ? 'bg-white border-slate-300' : 'bg-slate-50 border-slate-200 opacity-60'"
      >
        <div class="flex items-center space-x-2.5 w-36">
          <input
            type="checkbox"
            v-model="day.is_active"
            :id="'day-' + day.id"
            class="w-4 h-4 rounded-none text-brand-600 focus:ring-0 border-slate-300"
          />
          <label :for="'day-' + day.id" class="font-bold text-xs uppercase tracking-wider text-slate-900 cursor-pointer font-mono">
            {{ day.name }}
          </label>
        </div>

        <!-- Time Range & Slot Duration -->
        <div v-if="day.is_active" class="flex-1 flex flex-wrap items-center gap-3 font-mono text-xs">
          <div class="flex items-center space-x-1.5 text-slate-600">
            <span class="text-[10px] uppercase">From:</span>
            <input
              type="time"
              v-model="day.start_time"
              class="px-2 py-1 border border-slate-300 text-xs bg-white rounded-none"
            />
          </div>

          <div class="flex items-center space-x-1.5 text-slate-600">
            <span class="text-[10px] uppercase">To:</span>
            <input
              type="time"
              v-model="day.end_time"
              class="px-2 py-1 border border-slate-300 text-xs bg-white rounded-none"
            />
          </div>

          <div class="flex items-center space-x-1.5 text-slate-600 sm:ml-auto">
            <span class="text-[10px] uppercase">Block:</span>
            <select
              v-model.number="day.slot_duration_minutes"
              class="px-2 py-1 border border-slate-300 text-xs bg-white rounded-none uppercase font-mono"
            >
              <option :value="15">15 min</option>
              <option :value="30">30 min</option>
              <option :value="45">45 min</option>
              <option :value="60">60 min</option>
            </select>
          </div>
        </div>

        <div v-else class="text-xs font-mono text-slate-400 italic">
          Off Duty (Clinic Closed)
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useDoctorStore } from '@/stores/doctors'
import { Save } from 'lucide-vue-next'

const doctorStore = useDoctorStore()
const saving = ref(false)

const days = ref([
  { id: 1, name: 'Monday', is_active: true, start_time: '09:00', end_time: '17:00', slot_duration_minutes: 30 },
  { id: 2, name: 'Tuesday', is_active: true, start_time: '09:00', end_time: '17:00', slot_duration_minutes: 30 },
  { id: 3, name: 'Wednesday', is_active: true, start_time: '09:00', end_time: '17:00', slot_duration_minutes: 30 },
  { id: 4, name: 'Thursday', is_active: true, start_time: '09:00', end_time: '17:00', slot_duration_minutes: 30 },
  { id: 5, name: 'Friday', is_active: true, start_time: '09:00', end_time: '17:00', slot_duration_minutes: 30 },
  { id: 6, name: 'Saturday', is_active: false, start_time: '09:00', end_time: '13:00', slot_duration_minutes: 30 },
  { id: 7, name: 'Sunday', is_active: false, start_time: '09:00', end_time: '13:00', slot_duration_minutes: 30 },
])

const saveSchedule = async () => {
  saving.value = true
  try {
    const slots = days.value
      .filter((d) => d.is_active)
      .map((d) => ({
        day_of_week: d.name,
        start_time: d.start_time,
        end_time: d.end_time,
        slot_duration_minutes: d.slot_duration_minutes,
      }))
    await doctorStore.saveAvailabilities(slots)
  } finally {
    saving.value = false
  }
}
</script>
