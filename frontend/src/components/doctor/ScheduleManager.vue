<template>
  <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Weekly Clinical Availability Schedule</h3>
        <p class="text-xs text-slate-500 mt-0.5">Configure your active days, operating hours, and consultation slot durations</p>
      </div>
      <button
        @click="saveSchedule"
        :disabled="saving"
        class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-colors shadow-sm disabled:opacity-50 flex items-center justify-center space-x-1.5"
      >
        <Save class="w-4 h-4" />
        <span v-if="saving">Saving Schedule...</span>
        <span v-else>Save Working Hours</span>
      </button>
    </div>

    <!-- Days Grid -->
    <div class="space-y-3">
      <div
        v-for="day in days"
        :key="day.id"
        class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        :class="day.is_active ? 'bg-white border-slate-200' : 'bg-slate-50 border-slate-100 opacity-60'"
      >
        <div class="flex items-center space-x-3 w-40">
          <input
            type="checkbox"
            v-model="day.is_active"
            :id="'day-' + day.id"
            class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300"
          />
          <label :for="'day-' + day.id" class="font-bold text-sm text-slate-900 cursor-pointer">
            {{ day.name }}
          </label>
        </div>

        <!-- Time Range & Slot Duration -->
        <div v-if="day.is_active" class="flex-1 flex flex-wrap items-center gap-3">
          <div class="flex items-center space-x-2 text-xs text-slate-600">
            <span>From:</span>
            <input
              type="time"
              v-model="day.start_time"
              class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-500 bg-white"
            />
          </div>

          <div class="flex items-center space-x-2 text-xs text-slate-600">
            <span>To:</span>
            <input
              type="time"
              v-model="day.end_time"
              class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-500 bg-white"
            />
          </div>

          <div class="flex items-center space-x-2 text-xs text-slate-600 sm:ml-auto">
            <span>Slot:</span>
            <select
              v-model.number="day.slot_duration_minutes"
              class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-brand-500 bg-white"
            >
              <option :value="15">15 min</option>
              <option :value="30">30 min</option>
              <option :value="45">45 min</option>
              <option :value="60">60 min</option>
            </select>
          </div>
        </div>

        <div v-else class="text-xs font-semibold text-slate-400 italic">
          Off Duty / Clinic Closed
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
  { id: 0, name: 'Sunday', is_active: false, start_time: '09:00', end_time: '13:00', slot_duration_minutes: 30 },
])

onMounted(async () => {
  const existing = await doctorStore.fetchAvailabilities()
  if (existing && existing.length > 0) {
    days.value.forEach((d) => {
      const match = existing.find((e) => e.day_of_week === d.id)
      if (match) {
        d.is_active = match.is_active
        d.start_time = match.start_time
        d.end_time = match.end_time
        d.slot_duration_minutes = match.slot_duration_minutes
      }
    })
  }
})

const saveSchedule = async () => {
  saving.value = true
  try {
    const activeSlots = days.value
      .filter((d) => d.is_active)
      .map((d) => ({
        day_of_week: d.id,
        start_time: d.start_time,
        end_time: d.end_time,
        slot_duration_minutes: d.slot_duration_minutes,
        is_active: true,
      }))

    await doctorStore.saveAvailabilities(activeSlots)
  } catch (err) {
    // Handled
  } finally {
    saving.value = false
  }
}
</script>
