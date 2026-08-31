<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Doctor Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Registry</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">Patient Clinical Directory</h1>
      </div>

      <div class="relative w-full sm:w-64">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
        <input
          type="text"
          v-model="search"
          placeholder="Filter by patient name..."
          class="w-full pl-9 pr-3 py-1.5 border border-slate-300 text-xs focus:border-slate-800 bg-white rounded-none font-mono"
        />
      </div>
    </div>

    <!-- Patients List -->
    <div class="bg-white border border-slate-300 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-slate-100 text-slate-600 uppercase font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-2.5">Patient Name</th>
              <th class="px-4 py-2.5">Gender / Age</th>
              <th class="px-4 py-2.5">Encrypted Allergies</th>
              <th class="px-4 py-2.5">Clinical Profile</th>
              <th class="px-4 py-2.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="patient in filteredPatients" :key="patient.id" class="hover:bg-slate-50">
              <td class="px-4 py-3">
                <div class="font-bold font-sans text-slate-950 text-sm uppercase">{{ patient.name }}</div>
                <div class="text-slate-400 text-[11px]">{{ patient.email }}</div>
              </td>
              <td class="px-4 py-3">
                {{ patient.gender === 'F' ? 'Female' : 'Male' }} &bull; {{ patient.age }} yrs
                <div class="text-[10px] text-slate-400">DOB: {{ patient.dob }}</div>
              </td>
              <td class="px-4 py-3 font-semibold text-rose-800">
                {{ patient.allergies || 'None Known' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1">
                  <span v-if="patient.hypertension" class="px-1.5 py-0.5 bg-amber-50 text-amber-900 border border-amber-300 text-[10px]">Hypertension</span>
                  <span v-if="patient.diabetes" class="px-1.5 py-0.5 bg-blue-50 text-blue-900 border border-blue-300 text-[10px]">Diabetes</span>
                  <span v-if="!patient.hypertension && !patient.diabetes" class="text-slate-400 text-[10px]">Baseline Normal</span>
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  @click="openHistory(patient)"
                  class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] uppercase border border-slate-950"
                >
                  Clinical History
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Search } from 'lucide-vue-next'

const search = ref('')

const patients = ref([
  {
    id: 1,
    name: 'Jane Doe',
    email: 'patient@medicon.health',
    gender: 'F',
    age: 31,
    dob: '1995-05-10',
    allergies: 'Penicillin, Sulfa',
    hypertension: true,
    diabetes: false,
  },
  {
    id: 2,
    name: 'Robert Vance',
    email: 'robert.vance@example.com',
    gender: 'M',
    age: 48,
    dob: '1978-02-14',
    allergies: 'Latex',
    hypertension: true,
    diabetes: true,
  },
  {
    id: 3,
    name: 'Maria Santos',
    email: 'maria.santos@example.com',
    gender: 'F',
    age: 26,
    dob: '2000-09-22',
    allergies: null,
    hypertension: false,
    diabetes: false,
  },
  {
    id: 4,
    name: 'David Kim',
    email: 'david.kim@example.com',
    gender: 'M',
    age: 62,
    dob: '1964-11-03',
    allergies: 'Aspirin',
    hypertension: true,
    diabetes: false,
  },
])

const filteredPatients = computed(() => {
  if (!search.value) return patients.value
  const q = search.value.toLowerCase()
  return patients.value.filter(
    (p) => p.name.toLowerCase().includes(q) || p.email.toLowerCase().includes(q)
  )
})

const openHistory = (patient) => {
  alert(`Viewing full encrypted EHR records for ${patient.name}`)
}
</script>
