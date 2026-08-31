<template>
  <div class="space-y-5">
    <!-- Header & Search Filter Bar -->
    <div class="bg-white border border-slate-300 p-4 space-y-3">
      <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
        <span>Patient Portal</span>
        <span>/</span>
        <span class="font-bold text-slate-900">Physician Directory</span>
      </div>
      <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950">Board-Certified Specialists</h1>

      <!-- Search inputs -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
        <div class="sm:col-span-2 relative">
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
          <input
            type="text"
            v-model="searchQuery"
            @input="handleFilter"
            placeholder="Search by physician name or specialty..."
            class="w-full pl-9 pr-3 py-1.5 border border-slate-300 text-xs focus:border-slate-800 focus:outline-none bg-white rounded-none"
          />
        </div>

        <div>
          <select
            v-model="selectedSpecialty"
            @change="handleFilter"
            class="w-full px-3 py-1.5 border border-slate-300 text-xs focus:border-slate-800 focus:outline-none bg-white rounded-none"
          >
            <option value="">All Specialties</option>
            <option v-for="spec in specialties" :key="spec" :value="spec">{{ spec }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Doctors Grid -->
    <div v-if="loading" class="py-12">
      <LoadingSpinner text="Fetching active physicians..." />
    </div>

    <div v-else-if="doctors.length === 0" class="bg-white border border-slate-300 p-12 text-center">
      <p class="text-xs font-mono text-slate-500">No physicians found matching search criteria.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="doctor in doctors"
        :key="doctor.id"
        class="bg-white border border-slate-300 p-4 hover:border-brand-600 transition-colors flex flex-col justify-between"
      >
        <div>
          <div class="flex items-start space-x-3">
            <img
              :src="doctor.avatar_url || 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&auto=format&fit=crop&q=80'"
              alt="Doctor"
              class="w-12 h-12 object-cover border border-slate-300 flex-shrink-0"
            />
            <div>
              <span class="text-[10px] font-mono font-bold text-brand-600 uppercase block">{{ doctor.specialty }}</span>
              <h3 class="font-bold text-sm text-slate-950 uppercase mt-0.5 leading-snug">{{ doctor.name }}</h3>
              <p class="text-[11px] font-mono text-slate-500 mt-0.5">License: {{ doctor.license_number || 'MD-REG-001' }}</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 mt-3 leading-relaxed">
            {{ doctor.bio || 'Board-certified medical practitioner specializing in preventive care and patient treatment.' }}
          </p>

          <div class="mt-3 pt-2.5 border-t border-slate-200 grid grid-cols-2 gap-2 text-xs font-mono">
            <div class="p-2 bg-slate-50 border border-slate-200">
              <span class="text-[10px] text-slate-500 uppercase block">Consultation Fee</span>
              <span class="font-bold text-slate-900">₱{{ (doctor.consultation_fee_cents ? doctor.consultation_fee_cents / 100 : (doctor.consultation_fee || 120)).toFixed(2) }}</span>
            </div>
            <div class="p-2 bg-slate-50 border border-slate-200">
              <span class="text-[10px] text-slate-500 uppercase block">Rating Score</span>
              <span class="font-bold text-slate-900">{{ doctor.rating || 4.9 }} / 5.0</span>
            </div>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-200 mt-3">
          <button
            @click="openBookModalForDoctor(doctor)"
            class="w-full py-2 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase tracking-wider border border-brand-800 transition-colors"
          >
            Book Consultation
          </button>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <BookAppointmentModal
      :is-open="showBookModal"
      :preselected-doctor="selectedDoctorForBooking"
      @close="showBookModal = false"
      @booked="handleBookingSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useDoctorStore } from '@/stores/doctors'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import { Search } from 'lucide-vue-next'

const doctorStore = useDoctorStore()
const router = useRouter()
const route = useRoute()

const searchQuery = ref('')
const selectedSpecialty = ref('')
const showBookModal = ref(false)
const selectedDoctorForBooking = ref(null)

const doctors = ref([])
const specialties = ref([])
const loading = ref(false)

const handleFilter = async () => {
  loading.value = true
  try {
    doctors.value = await doctorStore.fetchDoctors({
      search: searchQuery.value,
      specialty: selectedSpecialty.value,
    })
  } finally {
    loading.value = false
  }
}

const openBookModalForDoctor = (doctor) => {
  selectedDoctorForBooking.value = doctor
  showBookModal.value = true
}

const handleBookingSuccess = () => {
  router.push('/patient/appointments')
}

onMounted(async () => {
  if (route.query.specialty) {
    selectedSpecialty.value = route.query.specialty
  }
  loading.value = true
  try {
    specialties.value = await doctorStore.fetchSpecialties()
    await handleFilter()
  } finally {
    loading.value = false
  }
})
</script>
