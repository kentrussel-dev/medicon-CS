<template>
  <div class="min-h-screen bg-slate-100 text-slate-900 font-sans antialiased">
    <!-- Top Official Banner Strip -->
    <div class="bg-slate-900 text-slate-300 text-[11px] font-mono px-4 sm:px-8 py-1.5 border-b border-slate-800 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <span class="inline-block w-2 h-2 bg-emerald-500 rounded-none"></span>
        <span class="font-bold uppercase tracking-wider text-slate-100">Official Clinical Healthcare Platform</span>
        <span class="hidden md:inline text-slate-500">|</span>
        <span class="hidden md:inline text-slate-400">HIPAA & EHR Certified Infrastructure</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="text-slate-400 hidden sm:inline">System Status: <span class="text-emerald-400 font-bold">ONLINE</span></span>
        <a href="#security" class="underline hover:text-white">Compliance Standard</a>
      </div>
    </div>

    <!-- Official Header -->
    <header class="bg-white border-b-2 border-slate-300 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
        <!-- Agency Brand -->
        <router-link to="/" class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-brand-600 text-white flex items-center justify-center font-bold text-lg border border-brand-700">
            M
          </div>
          <div>
            <div class="text-lg font-black tracking-tight text-slate-950 uppercase leading-none">
              Medicon Clinical Systems
            </div>
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">
              Telehealth & Patient Encounter Network
            </div>
          </div>
        </router-link>

        <!-- Navigation Menu -->
        <nav class="hidden lg:flex items-center space-x-1 text-xs font-bold uppercase tracking-wider text-slate-700">
          <a href="#directory" class="px-3 py-2 hover:bg-slate-100 hover:text-brand-600 border-b-2 border-transparent hover:border-brand-600 transition-all">Specialist Directory</a>
          <a href="#procedures" class="px-3 py-2 hover:bg-slate-100 hover:text-brand-600 border-b-2 border-transparent hover:border-brand-600 transition-all">Operating Protocols</a>
          <a href="#standards" class="px-3 py-2 hover:bg-slate-100 hover:text-brand-600 border-b-2 border-transparent hover:border-brand-600 transition-all">Clinical Standards</a>
          <a href="#security" class="px-3 py-2 hover:bg-slate-100 hover:text-brand-600 border-b-2 border-transparent hover:border-brand-600 transition-all">HIPAA & Security</a>
        </nav>

        <!-- Right User Actions -->
        <div class="flex items-center space-x-3">
          <template v-if="auth.isAuthenticated">
            <router-link
              :to="dashboardRoute"
              class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider border border-brand-700 flex items-center space-x-2"
            >
              <LayoutDashboard class="w-4 h-4" />
              <span>Enter {{ auth.role.toUpperCase() }} Workspace</span>
            </router-link>
          </template>
          <template v-else>
            <router-link
              to="/login"
              class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider border border-brand-700"
            >
              Portal Login
            </router-link>
          </template>
        </div>
      </div>
    </header>

    <!-- Main Hero Section (Crisp Government Architecture) -->
    <section class="py-10 lg:py-14 bg-slate-50 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <!-- Left Main Column: Mandate & Booking System -->
          <div class="lg:col-span-7 space-y-6">
            <div class="border-l-4 border-brand-600 pl-4 py-1">
              <span class="text-xs font-mono font-bold uppercase tracking-widest text-brand-600 block">
                Healthcare Directive &bull; Encrypted Telehealth Services
              </span>
              <h1 class="text-3xl sm:text-4xl lg:text-4xl font-extrabold text-slate-950 tracking-tight mt-1 uppercase leading-tight">
                National Clinical Telehealth & Patient Management Infrastructure
              </h1>
            </div>

            <p class="text-sm text-slate-700 leading-relaxed">
              Official healthcare portal facilitating verified board-certified virtual visits, encrypted electronic medical records (EHR), automated appointment dispatching, and secure electronic prescriptions.
            </p>

            <!-- Structured Appointment Request Card -->
            <div class="bg-white border-2 border-slate-300 shadow-panel">
              <div class="bg-slate-900 text-white px-5 py-3 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <Calendar class="w-4 h-4 text-brand-400" />
                  <span class="font-bold text-xs uppercase tracking-wider">Schedule Clinical Consultation</span>
                </div>
                <span class="text-[10px] font-mono font-bold bg-slate-800 text-slate-300 px-2 py-0.5 border border-slate-700 uppercase">
                  Direct Queue Dispatch
                </span>
              </div>

              <div class="p-6 space-y-4">
                <!-- 3 Sharp Form Columns -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <!-- Consultation Mode -->
                  <div
                    @click="selectedMode = selectedMode === 'Telehealth Video' ? 'In-Clinic Facility' : 'Telehealth Video'"
                    class="p-3.5 bg-slate-50 border border-slate-300 hover:border-brand-600 cursor-pointer transition-colors"
                  >
                    <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">01. Service Mode</span>
                    <span class="text-xs font-bold text-slate-900 mt-1 block">{{ selectedMode }}</span>
                    <span class="text-[10px] text-slate-500 block mt-0.5">Click to toggle</span>
                  </div>

                  <!-- Slot Length -->
                  <div
                    @click="openBookingModal"
                    class="p-3.5 bg-slate-50 border border-slate-300 hover:border-brand-600 cursor-pointer transition-colors"
                  >
                    <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">02. Time Allocation</span>
                    <span class="text-xs font-bold text-slate-900 mt-1 block">30-Minute Schedule</span>
                    <span class="text-[10px] text-slate-500 block mt-0.5">Standard clinical block</span>
                  </div>

                  <!-- Physician Tier -->
                  <div
                    @click="openBookingModal"
                    class="p-3.5 bg-slate-50 border border-slate-300 hover:border-brand-600 cursor-pointer transition-colors"
                  >
                    <span class="text-[10px] font-mono font-bold uppercase text-slate-500 block">03. Staff Verification</span>
                    <span class="text-xs font-bold text-slate-900 mt-1 block">Board-Certified</span>
                    <span class="text-[10px] text-slate-500 block mt-0.5">Verified state license</span>
                  </div>
                </div>

                <!-- Submit Button -->
                <button
                  @click="openBookingModal"
                  class="w-full py-3.5 px-6 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs uppercase tracking-wider border border-brand-700 transition-colors flex items-center justify-center space-x-2"
                >
                  <span>Request Patient Consultation</span>
                  <ArrowRight class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Right Column: Institutional Verification & Physician Registry -->
          <div class="lg:col-span-5 space-y-4">
            <!-- Medical Officer Profile Card -->
            <div class="bg-white border border-slate-300 shadow-crisp p-5 space-y-4">
              <div class="flex items-center space-x-4">
                <img
                  src="https://images.unsplash.com/photo-1594824813593-9c8df6cbeeb0?w=600&auto=format&fit=crop&q=80"
                  alt="Attending Physician"
                  class="w-20 h-20 object-cover border border-slate-300"
                />
                <div>
                  <span class="text-[10px] font-mono font-bold text-brand-600 uppercase tracking-wider block">
                    Chief of Clinical Medicine
                  </span>
                  <h4 class="text-base font-bold text-slate-950 uppercase">Dr. Sarah Jenkins, MD, FACC</h4>
                  <p class="text-xs text-slate-600 mt-0.5 font-mono">License: MD-99281-STATE</p>
                  <p class="text-xs text-slate-600 font-mono">Specialty: Cardiovascular Medicine</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-200 text-xs font-mono">
                <div class="p-2 bg-slate-50 border border-slate-200">
                  <span class="text-slate-500 block text-[10px] uppercase">Encounter Mode</span>
                  <span class="font-bold text-slate-900">Virtual / Telehealth</span>
                </div>
                <div class="p-2 bg-slate-50 border border-slate-200">
                  <span class="text-slate-500 block text-[10px] uppercase">Audit Protocol</span>
                  <span class="font-bold text-emerald-700">HIPAA Verified</span>
                </div>
              </div>
            </div>

            <!-- Certified Standards Callout -->
            <div class="bg-slate-900 text-white p-5 border border-slate-800 space-y-3">
              <div class="text-xs font-bold uppercase tracking-wider flex items-center space-x-2 text-brand-400">
                <ShieldCheck class="w-4 h-4" />
                <span>Security & Regulatory Assurances</span>
              </div>
              <ul class="space-y-2 text-xs text-slate-300 font-mono">
                <li class="flex items-center space-x-2">
                  <span class="text-emerald-400 font-bold">[✓]</span>
                  <span>AES-256 Encrypted Patient Record Store</span>
                </li>
                <li class="flex items-center space-x-2">
                  <span class="text-emerald-400 font-bold">[✓]</span>
                  <span>Immutable Append-Only Access Audit Log</span>
                </li>
                <li class="flex items-center space-x-2">
                  <span class="text-emerald-400 font-bold">[✓]</span>
                  <span>Role-Based Access Control Policies (RBAC)</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 2: Clinical Specialties Directory -->
    <section id="directory" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8 pb-4 border-b border-slate-200">
          <div>
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-brand-600">Section 01 // Directory</span>
            <h2 class="text-2xl font-black text-slate-950 uppercase tracking-tight mt-0.5">
              Certified Clinical Departments
            </h2>
            <p class="text-xs text-slate-600 mt-1">Select a department to view available specialist physicians and schedule consultations</p>
          </div>

          <button
            @click="openBookingModal"
            class="px-4 py-2 border border-slate-400 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider transition-colors self-start sm:self-auto"
          >
            Browse Full Registry
          </button>
        </div>

        <!-- 6-Column Grid of Crisp Specialty Panels -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
          <div
            v-for="spec in specialtiesList"
            :key="spec.name"
            @click="selectSpecialtyAndBook(spec.name)"
            class="p-4 bg-white border border-slate-300 hover:border-brand-600 hover:bg-slate-50 cursor-pointer transition-all flex flex-col justify-between"
          >
            <div>
              <div class="w-8 h-8 bg-slate-100 text-brand-600 flex items-center justify-center mb-3 border border-slate-200">
                <component :is="spec.icon" class="w-4 h-4" />
              </div>
              <h4 class="font-bold text-xs uppercase tracking-tight text-slate-900">
                {{ spec.name }}
              </h4>
              <span class="text-[11px] font-mono text-slate-500 mt-1 block">
                {{ spec.doctorCount }} Licensed Staff
              </span>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-brand-600 font-bold uppercase">
              <span>Book Dept</span>
              <span>&rarr;</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 3: Operating Directives & Protocols -->
    <section id="procedures" class="py-12 bg-slate-50 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="mb-8 pb-4 border-b border-slate-200">
          <span class="text-xs font-mono font-bold uppercase tracking-wider text-brand-600">Section 02 // Protocol</span>
          <h2 class="text-2xl font-black text-slate-950 uppercase tracking-tight mt-0.5">
            Standard Patient Encounter Workflow
          </h2>
          <p class="text-xs text-slate-600 mt-1">Four-stage formal process for telemedicine consultation and medical record formulation</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="(step, idx) in steps"
            :key="step.title"
            class="bg-white border border-slate-300 p-5 space-y-3"
          >
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
              <span class="font-mono text-xs font-bold text-brand-600 uppercase">Stage 0{{ idx + 1 }}</span>
              <component :is="step.icon" class="w-4 h-4 text-slate-500" />
            </div>

            <div>
              <h4 class="font-bold text-sm text-slate-900 uppercase">{{ step.title }}</h4>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                {{ step.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section 4: Clinical Standards & Assurances -->
    <section id="standards" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <div class="lg:col-span-5 space-y-4">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-brand-600">Section 03 // Specifications</span>
            <h2 class="text-2xl font-black text-slate-950 uppercase tracking-tight">
              Hospital-Grade Telemedicine Infrastructure
            </h2>
            <p class="text-xs text-slate-700 leading-relaxed">
              Medicon complies with all state and federal telehealth standards, providing secure browser-based WebRTC video links, structured ICD-10 diagnostic coding, and automated appointment notification workflows.
            </p>
            <div class="pt-2">
              <button
                @click="openBookingModal"
                class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider border border-slate-950 flex items-center space-x-2"
              >
                <span>Initiate Consultation Request</span>
                <ArrowRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Standard 1 -->
            <div class="p-4 bg-slate-50 border border-slate-300 space-y-2">
              <div class="flex items-center space-x-2 text-brand-600 font-bold text-xs uppercase">
                <Video class="w-4 h-4" />
                <span>Virtual Clinical Encounters</span>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed">
                Direct peer-to-peer encrypted WebRTC video rooms requiring zero third-party software installation.
              </p>
            </div>

            <!-- Standard 2 -->
            <div class="p-4 bg-slate-50 border border-slate-300 space-y-2">
              <div class="flex items-center space-x-2 text-brand-600 font-bold text-xs uppercase">
                <CreditCard class="w-4 h-4" />
                <span>Transparent Fee Schedules</span>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed">
                Standardized consultation rates with automated digital invoicing and itemized clinical receipts.
              </p>
            </div>

            <!-- Standard 3 -->
            <div class="p-4 bg-slate-50 border border-slate-300 space-y-2">
              <div class="flex items-center space-x-2 text-brand-600 font-bold text-xs uppercase">
                <FileText class="w-4 h-4" />
                <span>AES-256 Encrypted EHR</span>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed">
                Vital signs, clinical examination notes, and diagnoses encrypted at rest with forensic audit trails.
              </p>
            </div>

            <!-- Standard 4 -->
            <div class="p-4 bg-slate-50 border border-slate-300 space-y-2">
              <div class="flex items-center space-x-2 text-brand-600 font-bold text-xs uppercase">
                <BellRing class="w-4 h-4" />
                <span>Automated Notification Pipeline</span>
              </div>
              <p class="text-xs text-slate-600 leading-relaxed">
                Automated SMS and email reminders prior to visits to ensure continuous patient care delivery.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Institutional Footer -->
    <footer id="security" class="bg-slate-900 text-white py-12 border-t-4 border-brand-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-xs font-mono">
          <!-- Col 1 -->
          <div class="md:col-span-2 space-y-3">
            <div class="font-bold text-sm text-white uppercase tracking-wider">
              Medicon Clinical Healthcare Network
            </div>
            <p class="text-slate-400 font-sans text-xs max-w-md leading-relaxed">
              Official enterprise telehealth and patient encounter management platform providing secure virtual consultations, encrypted EHR records, and automated scheduling services.
            </p>
            <div class="text-[11px] text-emerald-400 flex items-center space-x-2 font-mono">
              <ShieldCheck class="w-4 h-4" />
              <span>HIPAA CERTIFIED &bull; AES-256 COMPLIANT &bull; NIST STANDARDS</span>
            </div>
          </div>

          <!-- Col 2: Workspace Direct Portals -->
          <div class="space-y-2">
            <div class="font-bold uppercase tracking-wider text-slate-300 border-b border-slate-800 pb-1">
              Authorized Portals
            </div>
            <ul class="space-y-1.5 text-slate-400">
              <li><router-link to="/patient/dashboard" class="hover:text-white">&bull; Patient Portal</router-link></li>
              <li><router-link to="/doctor/dashboard" class="hover:text-white">&bull; Physician Workspace</router-link></li>
              <li><router-link to="/admin/dashboard" class="hover:text-white">&bull; Admin Operations</router-link></li>
              <li><router-link to="/login" class="hover:text-white">&bull; Account Authentication</router-link></li>
            </ul>
          </div>

          <!-- Col 3: Medical Services -->
          <div class="space-y-2">
            <div class="font-bold uppercase tracking-wider text-slate-300 border-b border-slate-800 pb-1">
              Departments
            </div>
            <ul class="space-y-1.5 text-slate-400">
              <li><a href="#directory" class="hover:text-white">&bull; Cardiovascular Clinic</a></li>
              <li><a href="#directory" class="hover:text-white">&bull; Neurological Services</a></li>
              <li><a href="#directory" class="hover:text-white">&bull; Orthopedic Surgery</a></li>
              <li><a href="#directory" class="hover:text-white">&bull; Primary & Urgent Care</a></li>
            </ul>
          </div>
        </div>

        <div class="pt-6 border-t border-slate-800 text-center text-[11px] text-slate-500 font-mono">
          CONFIDENTIAL &amp; PROPRIETARY &bull; MEDICON HEALTHCARE INFORMATION SYSTEMS &bull; ALL RIGHTS RESERVED
        </div>
      </div>
    </footer>

    <!-- Global Book Appointment Modal -->
    <BookAppointmentModal
      :is-open="showBookingModal"
      @close="showBookingModal = false"
      @booked="handleBookingSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import {
  Calendar,
  ArrowRight,
  ShieldCheck,
  LayoutDashboard,
  Video,
  FileText,
  CreditCard,
  BellRing,
  HeartPulse,
  Activity,
  Shield,
  Sparkles,
  Stethoscope,
  Smile,
  Search,
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const showBookingModal = ref(false)
const selectedMode = ref('Telehealth Video')

const dashboardRoute = computed(() => {
  if (auth.isAdmin) return '/admin/dashboard'
  if (auth.isDoctor) return '/doctor/dashboard'
  return '/patient/dashboard'
})

const specialtiesList = [
  { name: 'Cardiology', doctorCount: 24, icon: HeartPulse },
  { name: 'Neurology', doctorCount: 18, icon: Activity },
  { name: 'Orthopedic', doctorCount: 21, icon: Shield },
  { name: 'Pediatrics', doctorCount: 19, icon: Smile },
  { name: 'Dermatology', doctorCount: 15, icon: Sparkles },
  { name: 'General Practice', doctorCount: 35, icon: Stethoscope },
]

const steps = [
  {
    title: 'Select Clinical Department',
    description: 'Browse certified state medical departments and licensed staff by specialty and consultation rates.',
    icon: Search,
  },
  {
    title: 'Submit Schedule Request',
    description: 'Allocate an authorized 30-minute consultation slot and select virtual or in-clinic visit mode.',
    icon: Calendar,
  },
  {
    title: 'Virtual Consultation Encounter',
    description: 'Connect directly through encrypted WebRTC telehealth rooms with verified attending physician.',
    icon: Video,
  },
  {
    title: 'EHR & Prescription Formulation',
    description: 'Obtain encrypted diagnostic medical summary, physiological vitals assessment, and e-prescription.',
    icon: FileText,
  },
]

const openBookingModal = () => {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: '/patient/appointments' } })
    return
  }
  showBookingModal.value = true
}

const selectSpecialtyAndBook = (specialtyName) => {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: '/patient/doctors' } })
    return
  }
  router.push({ path: '/patient/doctors', query: { specialty: specialtyName } })
}

const handleBookingSuccess = () => {
  if (auth.isAuthenticated) {
    router.push('/patient/appointments')
  }
}
</script>
