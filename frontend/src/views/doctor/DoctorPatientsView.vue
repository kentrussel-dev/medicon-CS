<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">Patient Directory</h2>
        <p class="text-xs text-slate-500 mt-0.5">Review patient clinical records, chronic condition profiles, and encounter history</p>
      </div>

      <div class="relative w-full sm:w-72">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
        <input
          type="text"
          v-model="search"
          placeholder="Filter by patient name or email..."
          class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500"
        />
      </div>
    </div>

    <!-- Patients List -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-100">
            <tr>
              <th class="px-6 py-4">Patient Name</th>
              <th class="px-6 py-4">Gender / Age</th>
              <th class="px-6 py-4">Encrypted Allergies</th>
              <th class="px-6 py-4">Clinical Profile</th>
              <th class="px-6 py-4 text-right">Encounter Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr v-for="patient in filteredPatients" :key="patient.id" class="hover:bg-slate-50/50">
              <td class="px-6 py-4">
                <div class="font-bold text-slate-900 text-sm">{{ patient.name }}</div>
                <div class="text-slate-400 text-[11px]">{{ patient.email }}</div>
              </td>
              <td class="px-6 py-4">
                {{ patient.gender === 'F' ? 'Female' : 'Male' }} &bull; {{ patient.age }} yrs
                <div class="text-[11px] text-slate-400">DOB: {{ patient.dob }}</div>
              </td>
              <td class="px-6 py-4 font-semibold text-rose-700">
                {{ patient.allergies || 'None Known' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1.5">
                  <span v-if="patient.hypertension" class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 text-[10px] font-bold">Hypertension</span>
                  <span v-if="patient.diabetes" class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 text-[10px] font-bold">Diabetes</span>
                  <span v-if="!patient.hypertension && !patient.diabetes" class="text-slate-400 text-[11px]">Normal Baseline</span>
                </div>
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <button
                  @click="openHistory(patient)"
                  class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs transition-colors"
                >
                  View Clinical History
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Clinical History Modal -->
    <PatientClinicalHistoryModal
      :is-open="showHistoryModal"
      :patient="selectedPatient"
      @close="showHistoryModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import PatientClinicalHistoryModal from '@/components/doctor/PatientClinicalHistoryModal.vue'
import { Search } from 'lucide-vue-next'

const search = ref('')
const showHistoryModal = ref(false)
const selectedPatient = ref(null)

const patients = ref([
  {
    id: 1,
    name: 'John Doe',
    email: 'patient@medicon.health',
    gender: 'M',
    age: 42,
    dob: '1984-06-15',
    allergies: 'Penicillin, Shellfish',
    hypertension: true,
    diabetes: false,
    notes: 'Patient has mild essential hypertension under Lisinopril management.',
  },
  {
    id: 2,
    name: 'Emily Clark',
    email: 'emily.clark@medicon.health',
    gender: 'F',
    age: 33,
    dob: '1992-11-23',
    allergies: 'Latex',
    hypertension: false,
    diabetes: false,
    notes: 'Episodic migraine headaches with light sensitivity aura.',
  },
  {
    id: 3,
    name: 'Robert Vance',
    email: 'robert.vance@medicon.health',
    gender: 'M',
    age: 58,
    dob: '1968-03-08',
    allergies: 'Sulfa Drugs',
    hypertension: true,
    diabetes: true,
    notes: 'Type 2 Diabetes Mellitus managed with oral hypoglycemics.',
  },
  {
    id: 4,
    name: 'Lisa Martinez',
    email: 'lisa.martinez@medicon.health',
    gender: 'F',
    age: 31,
    dob: '1995-08-30',
    allergies: 'None Known',
    hypertension: false,
    diabetes: false,
    notes: 'Annual preventative wellness and dermatology checks.',
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
  selectedPatient.value = patient
  showHistoryModal.value = true
}
</script>
