<template>
  <div class="space-y-6">
    <!-- Header & Search Filter Bar -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl font-black text-slate-900">Find Board-Certified Specialists</h2>
          <p class="text-xs text-slate-500 mt-0.5">Browse certified practitioners, compare ratings, and book appointments</p>
        </div>
      </div>

      <!-- Search inputs -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
        <div class="sm:col-span-2 relative">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5" />
          <input
            type="text"
            v-model="searchQuery"
            @input="handleFilter"
            placeholder="Search by physician name or keyword..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
          />
        </div>

        <div>
          <select
            v-model="selectedSpecialty"
            @change="handleFilter"
            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white"
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

    <div v-else-if="doctors.length === 0" class="bg-white rounded-3xl p-12 text-center border border-slate-100">
      <p class="text-sm font-semibold text-slate-500">No physicians found matching your search criteria.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="doctor in doctors"
        :key="doctor.id"
        class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <!-- Doctor Top Meta -->
          <div class="flex items-start space-x-4">
            <img
              :src="doctor.avatar_url || 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&auto=format&fit=crop&q=80'"
              alt="Doctor"
              class="w-14 h-14 rounded-2xl object-cover border border-slate-100 shadow-xs"
            />
            <div class="flex-1 min-w-0">
              <h4 class="font-extrabold text-slate-900 text-base leading-tight truncate">{{ doctor.name }}</h4>
              <span class="text-xs font-bold text-brand-600 block mt-0.5">{{ doctor.specialty }}</span>
              <div class="flex items-center space-x-1 mt-1 text-xs text-amber-500 font-bold">
                <Star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                <span>{{ doctor.rating }}</span>
                <span class="text-slate-400 font-normal">({{ doctor.years_of_experience }} yrs exp)</span>
              </div>
            </div>
          </div>

          <!-- Doctor Bio -->
          <p class="text-xs text-slate-600 mt-4 line-clamp-3 leading-relaxed font-sans">
            {{ doctor.bio }}
          </p>
        </div>

        <!-- Card Footer -->
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
          <div>
            <span class="text-[10px] uppercase font-bold text-slate-400 block">Consultation Fee</span>
            <span class="text-base font-black text-slate-900">${{ doctor.consultation_fee }}</span>
          </div>

          <button
            @click="openBooking(doctor)"
            class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5"
          >
            <Calendar class="w-3.5 h-3.5" />
            <span>Book Visit</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <BookAppointmentModal
      :is-open="showBookModal"
      :preselected-doctor-id="selectedDoctor?.id"
      @close="showBookModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDoctorStore } from '@/stores/doctors'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import LoadingSpinner from '@/components/common/LoadingSpinner.vue'
import { Search, Star, Calendar } from 'lucide-vue-next'

const doctorStore = useDoctorStore()
const loading = ref(false)
const searchQuery = ref('')
const selectedSpecialty = ref('')
const showBookModal = ref(false)
const selectedDoctor = ref(null)

const doctors = computed(() => doctorStore.doctors)
const specialties = computed(() => doctorStore.specialties)

const handleFilter = async () => {
  await doctorStore.fetchDoctors({
    search: searchQuery.value || undefined,
    specialty: selectedSpecialty.value || undefined,
  })
}

const openBooking = (doc) => {
  selectedDoctor.value = doc
  showBookModal.value = true
}

onMounted(async () => {
  loading.value = true
  try {
    await doctorStore.fetchSpecialties()
    await doctorStore.fetchDoctors()
  } finally {
    loading.value = false
  }
})
</script>
