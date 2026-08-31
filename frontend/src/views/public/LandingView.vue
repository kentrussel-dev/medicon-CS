<template>
  <div class="min-h-screen bg-[#fcfdff] text-slate-900 font-sans selection:bg-brand-500 selection:text-white antialiased">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-100/80 transition-all">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-1 text-2xl font-black tracking-tight text-slate-900">
          <span>Doc</span><span class="text-brand-600">.</span><span class="text-brand-600">Wise</span>
          <span class="ml-2 text-[10px] uppercase font-extrabold px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full border border-brand-200">
            Medicon
          </span>
        </router-link>

        <!-- Desktop Navigation Links -->
        <nav class="hidden lg:flex items-center space-x-8 text-sm font-semibold text-slate-600">
          <a href="#specialists" class="hover:text-brand-600 transition-colors">Specialists</a>
          <a href="#how-it-works" class="hover:text-brand-600 transition-colors">How It Works</a>
          <a href="#features" class="hover:text-brand-600 transition-colors">Telehealth Care</a>
          <a href="#security" class="hover:text-brand-600 transition-colors">HIPAA Security</a>
          <a href="#reviews" class="hover:text-brand-600 transition-colors">Testimonials</a>
        </nav>

        <!-- Right User Actions -->
        <div class="flex items-center space-x-3">
          <template v-if="auth.isAuthenticated">
            <router-link
              :to="dashboardRoute"
              class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-md shadow-brand-500/20 flex items-center space-x-1.5"
            >
              <LayoutDashboard class="w-4 h-4" />
              <span>Open {{ auth.role.toUpperCase() }} Portal</span>
            </router-link>
          </template>
          <template v-else>
            <router-link
              to="/login"
              class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-md shadow-brand-500/20 hover:shadow-brand-500/30 active:scale-98"
            >
              Login
            </router-link>
          </template>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-6 pb-20 lg:pt-12 lg:pb-32 overflow-hidden bg-gradient-to-b from-[#f5f9ff] via-[#f8fbff] to-[#fcfdff]">
      <!-- Background Concentric Arch Rings -->
      <div class="absolute top-12 right-[5%] w-[580px] h-[580px] rounded-full border-[40px] border-blue-200/40 pointer-events-none hidden lg:block -z-0"></div>
      <div class="absolute top-28 right-[10%] w-[460px] h-[460px] rounded-full border-[32px] border-sky-300/30 pointer-events-none hidden lg:block -z-0"></div>
      <div class="absolute top-44 right-[15%] w-[340px] h-[340px] rounded-full bg-gradient-to-tr from-brand-100/60 to-transparent pointer-events-none hidden lg:block -z-0"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          <!-- Left Hero Text & CTA -->
          <div class="lg:col-span-7 space-y-6">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-brand-50 border border-brand-200/80 text-brand-700 text-xs font-bold shadow-2xs">
              <span class="flex h-2 w-2 rounded-full bg-brand-600 animate-pulse"></span>
              <span>Enterprise Telemedicine & Patient EHR Platform</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-[56px] font-black text-slate-950 tracking-tight leading-[1.12]">
              Healthier Hearts<br />
              Come From<br />
              <span class="text-brand-600 relative inline-block">
                Preventive Care
                <span class="absolute -bottom-1 left-0 right-0 h-1.5 bg-brand-400/30 rounded-full"></span>
              </span>
            </h1>

            <p class="text-sm sm:text-base text-slate-500 font-normal leading-relaxed max-w-lg">
              Connect directly with verified board-certified physicians, schedule instant encrypted telehealth visits, and access complete medical records with automated reminders.
            </p>

            <!-- Floating Quick Booking Widget Card -->
            <div class="mt-8 bg-white rounded-3xl p-6 sm:p-7 shadow-hero-card border border-slate-100/80 space-y-5">
              <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-base text-slate-900 flex items-center space-x-2">
                  <span>Book An Appointment</span>
                </h3>
                <span class="text-[11px] font-semibold text-brand-600 bg-brand-50 px-2.5 py-1 rounded-full">
                  Instant Confirmation
                </span>
              </div>

              <!-- 3 Mini Feature Selectors -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Location / Mode selector -->
                <div
                  @click="selectedMode = selectedMode === 'Telehealth Video' ? 'In-Clinic Visit' : 'Telehealth Video'"
                  class="p-3.5 rounded-2xl bg-slate-50 hover:bg-brand-50/50 border border-slate-100 hover:border-brand-200 transition-all cursor-pointer flex items-center space-x-3 group"
                >
                  <div class="w-10 h-10 rounded-xl bg-white shadow-xs border border-slate-100 flex items-center justify-center text-rose-500 group-hover:scale-105 transition-transform flex-shrink-0">
                    <MapPin class="w-5 h-5 text-rose-500" />
                  </div>
                  <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase block">Location / Mode</span>
                    <span class="text-xs font-bold text-slate-800 line-clamp-1">{{ selectedMode }}</span>
                  </div>
                </div>

                <!-- Suitable Time -->
                <div
                  @click="openBookingModal"
                  class="p-3.5 rounded-2xl bg-slate-50 hover:bg-brand-50/50 border border-slate-100 hover:border-brand-200 transition-all cursor-pointer flex items-center space-x-3 group"
                >
                  <div class="w-10 h-10 rounded-xl bg-white shadow-xs border border-slate-100 flex items-center justify-center text-brand-600 group-hover:scale-105 transition-transform flex-shrink-0">
                    <Calendar class="w-5 h-5 text-brand-600" />
                  </div>
                  <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase block">Suitable Time</span>
                    <span class="text-xs font-bold text-slate-800">Flexible 30m Slots</span>
                  </div>
                </div>

                <!-- Top Rated Doctors -->
                <div
                  @click="openBookingModal"
                  class="p-3.5 rounded-2xl bg-slate-50 hover:bg-brand-50/50 border border-slate-100 hover:border-brand-200 transition-all cursor-pointer flex items-center space-x-3 group"
                >
                  <div class="w-10 h-10 rounded-xl bg-white shadow-xs border border-slate-100 flex items-center justify-center text-amber-500 group-hover:scale-105 transition-transform flex-shrink-0">
                    <Search class="w-5 h-5 text-amber-500" />
                  </div>
                  <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase block">Top Rated</span>
                    <span class="text-xs font-bold text-slate-800">4.9/5 Specialists</span>
                  </div>
                </div>
              </div>

              <!-- Main CTA Action Button -->
              <button
                @click="openBookingModal"
                class="w-full py-3.5 px-6 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-sm shadow-md shadow-brand-600/25 transition-all hover:shadow-brand-600/35 active:scale-[0.99] flex items-center justify-center space-x-2"
              >
                <span>Make An Appointment</span>
                <ArrowRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Right Hero Image With Concentric Arch Glow -->
          <div class="lg:col-span-5 relative flex justify-center lg:justify-end">
            <div class="relative w-full max-w-[440px] aspect-[4/5] rounded-[36px] overflow-hidden shadow-2xl border-4 border-white">
              <img
                src="https://images.unsplash.com/photo-1594824813593-9c8df6cbeeb0?w=800&auto=format&fit=crop&q=80"
                alt="Doctor with tablet"
                class="w-full h-full object-cover object-top hover:scale-105 transition-transform duration-700"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>

              <!-- Floating Live Verified Pill Badge -->
              <div class="absolute bottom-6 left-6 right-6 p-4 rounded-2xl bg-white/95 backdrop-blur-md shadow-lg border border-white/40 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                    <ShieldCheck class="w-6 h-6" />
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-900 block">Board-Certified Clinicians</span>
                    <span class="text-[11px] text-slate-500">100% HIPAA & EHR Verified</span>
                  </div>
                </div>
                <div class="flex items-center space-x-1 text-amber-500 font-black text-xs">
                  <Star class="w-4 h-4 fill-amber-400 text-amber-400" />
                  <span>4.95</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Specialist Section -->
    <section id="specialists" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
          <div>
            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-600">Specialist Network</span>
            <h2 class="text-3xl font-black text-slate-950 mt-1">Our Specialists</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Find experienced physicians and surgeons across all clinical specialties</p>
          </div>

          <button
            @click="openBookingModal"
            class="px-5 py-2 rounded-xl border border-brand-200 text-brand-600 hover:bg-brand-50 text-xs font-bold transition-colors self-start sm:self-auto"
          >
            View All Specialists
          </button>
        </div>

        <!-- Specialty Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div
            v-for="spec in specialtiesList"
            :key="spec.name"
            @click="selectSpecialtyAndBook(spec.name)"
            class="p-5 rounded-3xl border transition-all cursor-pointer hover:shadow-card hover:-translate-y-1 group flex flex-col justify-between"
            :class="spec.highlighted ? 'bg-[#fff5f8] border-pink-200/80 shadow-sm' : 'bg-white border-slate-100 hover:border-brand-200'"
          >
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform" :class="spec.bgClass">
              <component :is="spec.icon" class="w-6 h-6" />
            </div>
            <div>
              <h4 class="font-extrabold text-sm text-slate-900 group-hover:text-brand-600 transition-colors">
                {{ spec.name }}
              </h4>
              <span class="text-[11px] font-semibold text-slate-400 mt-0.5 block">
                {{ spec.doctorCount }} Doctors
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 4 Easy Steps To Get Your Solution -->
    <section id="how-it-works" class="py-20 bg-[#f8fbff] border-y border-slate-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center max-w-xl mx-auto mb-14">
          <span class="text-xs font-extrabold uppercase tracking-wider text-brand-600">How It Works</span>
          <h2 class="text-3xl font-black text-slate-950 mt-1">4 Easy Steps to Get Your Solution</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-2">
            Seamless clinical access from anywhere &mdash; scheduled in minutes with automated reminder workflows
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="(step, idx) in steps"
            :key="step.title"
            class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all relative space-y-4"
          >
            <div class="flex items-center justify-between">
              <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-black">
                <component :is="step.icon" class="w-6 h-6" />
              </div>
              <span class="text-3xl font-black text-slate-200">0{{ idx + 1 }}</span>
            </div>

            <div>
              <h4 class="font-extrabold text-base text-slate-900">{{ step.title }}</h4>
              <p class="text-xs text-slate-500 mt-1.5 leading-relaxed font-normal">
                {{ step.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Feature Highlights Section (Telehealth & Reminders) -->
    <section id="features" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          <!-- Left Content -->
          <div class="lg:col-span-5 space-y-6">
            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-600">Clinical Standards</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-950 leading-tight">
              Hospital-Grade Telemedicine & Encrypted Health Records
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed font-normal">
              Medicon combines encrypted electronic health records, automated clinical reminders, and seamless physician scheduling to ensure smooth, continuous patient care.
            </p>

            <div class="pt-2">
              <button
                @click="openBookingModal"
                class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-all flex items-center space-x-2"
              >
                <span>Book Instant Video Call</span>
                <ArrowRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Right Feature Cards Stack (Matching mockup layout with SVG icons) -->
          <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <!-- Feature 1: Instant video consultation -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:bg-white hover:shadow-card hover:border-brand-100 transition-all space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-600 flex items-center justify-center">
                <Video class="w-6 h-6" />
              </div>
              <h4 class="font-extrabold text-base text-slate-900">Instant Video Consultation</h4>
              <p class="text-xs text-slate-500 leading-relaxed font-normal">
                Every physician on Medicon is board-certified. Secure HD WebRTC telemedicine rooms with end-to-end encryption.
              </p>
            </div>

            <!-- Feature 2: Easy payment options -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:bg-white hover:shadow-card hover:border-brand-100 transition-all space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center">
                <CreditCard class="w-6 h-6" />
              </div>
              <h4 class="font-extrabold text-base text-slate-900">Transparent Pricing</h4>
              <p class="text-xs text-slate-500 leading-relaxed font-normal">
                Clear upfront consultation fees with instant electronic billing and digital receipt generation.
              </p>
            </div>

            <!-- Feature 3: Health history & encryption -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:bg-white hover:shadow-card hover:border-brand-100 transition-all space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <FileText class="w-6 h-6" />
              </div>
              <h4 class="font-extrabold text-base text-slate-900">Encrypted Health Records</h4>
              <p class="text-xs text-slate-500 leading-relaxed font-normal">
                Diagnoses, examination notes, and vital signs are AES-256 encrypted at rest with immutable audit logs.
              </p>
            </div>

            <!-- Feature 4: Automated Visit Reminders -->
            <div class="p-6 rounded-3xl bg-slate-50/70 border border-slate-100 hover:bg-white hover:shadow-card hover:border-brand-100 transition-all space-y-3">
              <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center">
                <BellRing class="w-6 h-6" />
              </div>
              <h4 class="font-extrabold text-base text-slate-900">Automated Visit Reminders</h4>
              <p class="text-xs text-slate-500 leading-relaxed font-normal">
                Automated SMS notifications and calendar integrations ensure patients and doctors never miss a consultation.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials / "What Our Clients Say" Section -->
    <section id="reviews" class="py-20 bg-[#f8fbff] border-t border-slate-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center max-w-xl mx-auto mb-12">
          <span class="text-xs font-extrabold uppercase tracking-wider text-brand-600">Patient Satisfaction</span>
          <h2 class="text-3xl font-black text-slate-950 mt-1">What Our Clients Say</h2>
          <p class="text-xs sm:text-sm text-slate-500 mt-2">
            Trusted by thousands of patients and healthcare providers nationwide
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="review in reviews"
            :key="review.author"
            class="bg-white rounded-3xl p-7 border border-slate-100 shadow-sm space-y-4 relative"
          >
            <div class="text-brand-600 font-serif text-4xl leading-none">“</div>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal italic">
              {{ review.text }}
            </p>
            <div class="flex items-center space-x-3 pt-2 border-t border-slate-50">
              <img :src="review.avatar" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-slate-100" />
              <div>
                <h5 class="font-bold text-xs text-slate-900">{{ review.author }}</h5>
                <span class="text-[11px] text-slate-400">{{ review.role }}</span>
              </div>
              <div class="ml-auto flex items-center text-amber-400">
                <Star v-for="i in 5" :key="i" class="w-3.5 h-3.5 fill-amber-400" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer id="security" class="bg-slate-950 text-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 space-y-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Col 1 -->
          <div class="md:col-span-2 space-y-4">
            <div class="flex items-center space-x-1 text-2xl font-black text-white">
              <span>Doc</span><span class="text-brand-400">.</span><span class="text-brand-400">Wise</span>
              <span class="ml-2 text-[10px] uppercase font-bold px-2 py-0.5 bg-brand-900 text-brand-300 rounded border border-brand-700">
                Medicon Enterprise
              </span>
            </div>
            <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
              Enterprise telehealth and clinical encounter management platform providing secure virtual visits, encrypted EHR, and seamless scheduling.
            </p>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-400">
              <ShieldCheck class="w-4 h-4" />
              <span>HIPAA Compliant &bull; AES-256 Encryption &bull; Append-Only Audit Trail</span>
            </div>
          </div>

          <!-- Col 2: Quick Links -->
          <div class="space-y-3 text-xs">
            <h4 class="font-bold uppercase tracking-wider text-slate-200">Portals</h4>
            <ul class="space-y-2 text-slate-400">
              <li><router-link to="/patient/dashboard" class="hover:text-white transition-colors">Patient Portal</router-link></li>
              <li><router-link to="/doctor/dashboard" class="hover:text-white transition-colors">Physician Workspace</router-link></li>
              <li><router-link to="/admin/dashboard" class="hover:text-white transition-colors">Admin Analytics</router-link></li>
              <li><router-link to="/login" class="hover:text-white transition-colors">Account Sign In</router-link></li>
            </ul>
          </div>

          <!-- Col 3: Specialties -->
          <div class="space-y-3 text-xs">
            <h4 class="font-bold uppercase tracking-wider text-slate-200">Specialties</h4>
            <ul class="space-y-2 text-slate-400">
              <li><a href="#specialists" class="hover:text-white">Cardiology Clinic</a></li>
              <li><a href="#specialists" class="hover:text-white">Neurology & Brain Health</a></li>
              <li><a href="#specialists" class="hover:text-white">Orthopedic Surgery</a></li>
              <li><a href="#specialists" class="hover:text-white">Dermatology & Skin Care</a></li>
            </ul>
          </div>
        </div>

        <div class="pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
          &copy; 2026 Medicon Telehealth Systems. All rights reserved. Encrypted HIPAA Cloud Infrastructure.
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
  MapPin,
  Calendar,
  Search,
  ArrowRight,
  ShieldCheck,
  Star,
  LayoutDashboard,
  Video,
  FileText,
  Pill,
  CreditCard,
  BellRing,
  HeartPulse,
  Activity,
  Shield,
  Sparkles,
  Stethoscope,
  Smile,
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
  { name: 'Cardiology', doctorCount: 24, icon: HeartPulse, bgClass: 'bg-rose-50 text-rose-600', highlighted: false },
  { name: 'Neurology', doctorCount: 18, icon: Activity, bgClass: 'bg-indigo-50 text-indigo-600', highlighted: false },
  { name: 'Orthopedic', doctorCount: 21, icon: Shield, bgClass: 'bg-pink-50 text-pink-600', highlighted: true },
  { name: 'Pediatrics', doctorCount: 19, icon: Smile, bgClass: 'bg-sky-50 text-sky-600', highlighted: false },
  { name: 'Dermatology', doctorCount: 15, icon: Sparkles, bgClass: 'bg-amber-50 text-amber-600', highlighted: false },
  { name: 'General Practice', doctorCount: 35, icon: Stethoscope, bgClass: 'bg-emerald-50 text-emerald-600', highlighted: false },
]

const steps = [
  {
    title: 'Select Specialist',
    description: 'Browse certified physicians by specialty, ratings, consultation fees, and patient reviews.',
    icon: Search,
  },
  {
    title: 'Schedule Appointment',
    description: 'Pick your preferred date, 30-min time slot, and consultation format (Telehealth or Clinic).',
    icon: Calendar,
  },
  {
    title: 'Attend Video Visit',
    description: 'Join secure HD WebRTC telehealth video calls with end-to-end encrypted audio and video.',
    icon: Video,
  },
  {
    title: 'Get Your Solution & Rx',
    description: 'Receive encrypted clinical encounter notes, vital signs assessments, and electronic prescriptions.',
    icon: Pill,
  },
]

const reviews = [
  {
    text: 'Every doctor on the Medicon platform is certified and responsive. Booking a cardiology telehealth follow-up took under two minutes, and my encrypted prescription was available immediately.',
    author: 'Sarah M.',
    role: 'Patient since 2025',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
  },
  {
    text: 'The automated scheduling and reminders have significantly improved our clinical attendance rates. Our physicians love the intuitive schedule customizer and encrypted encounter notes.',
    author: 'Dr. Marcus Chen',
    role: 'Chief Medical Officer',
    avatar: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=150&auto=format&fit=crop&q=80',
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
