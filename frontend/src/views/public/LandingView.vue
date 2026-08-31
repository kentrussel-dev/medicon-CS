<template>
  <div class="min-h-screen bg-white text-slate-900 font-sans antialiased">
    <!-- Top Location & Hotline Utility Bar (Matching St. Luke's Header Structure) -->
    <div class="border-b border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 py-3 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Hospital Brand Logo -->
        <router-link to="/" class="flex items-center space-x-3">
          <div class="w-11 h-11 bg-slate-900 text-white flex items-center justify-center font-black text-xl border border-slate-800">
            M
          </div>
          <div>
            <div class="text-lg font-black tracking-tight text-slate-950 uppercase leading-none">
              Medicon Medical Center
            </div>
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mt-1">
              Quezon City &bull; Global City &bull; Extension Clinics
            </div>
          </div>
        </router-link>

        <!-- Location Phone Hotlines -->
        <div class="hidden lg:flex items-center space-x-8 text-center text-xs">
          <div>
            <span class="block font-bold uppercase text-slate-800 tracking-wider">QUEZON CITY</span>
            <a href="tel:+63287230101" class="text-brand-600 font-mono font-bold hover:underline">+63-2-8723-0101</a>
          </div>
          <div>
            <span class="block font-bold uppercase text-slate-800 tracking-wider">GLOBAL CITY</span>
            <a href="tel:+63287897700" class="text-brand-600 font-mono font-bold hover:underline">+63-2-8789-7700</a>
          </div>
          <div>
            <span class="block font-bold uppercase text-slate-800 tracking-wider">EXTENSION CLINIC</span>
            <span class="text-brand-600 font-mono font-bold">+63-2-8521-0020 / +63-2-8521-8647</span>
          </div>
        </div>

        <!-- Search Bar & Direct Login -->
        <div class="flex items-center space-x-3">
          <div class="relative w-48 sm:w-56">
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search specialists, services..."
              class="w-full pl-3 pr-8 py-1.5 border border-slate-300 text-xs focus:border-slate-800 focus:outline-none bg-white rounded-none placeholder:text-slate-400"
            />
            <Search class="w-4 h-4 text-slate-400 absolute right-2.5 top-2" />
          </div>

          <template v-if="auth.isAuthenticated">
            <router-link
              :to="dashboardRoute"
              class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider border border-slate-900"
            >
              {{ auth.role.toUpperCase() }}
            </router-link>
          </template>
          <template v-else>
            <router-link
              to="/login"
              class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider border border-slate-900"
            >
              Portal Login
            </router-link>
          </template>
        </div>
      </div>
    </div>

    <!-- Main Solid Full-Width Navigation Bar (Matching St. Luke's Blue Menu Bar) -->
    <nav class="bg-slate-900 text-white border-b-2 border-brand-600 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 flex items-center justify-between overflow-x-auto scrollbar-none">
        <div class="flex items-center text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
          <a href="#specialties" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Health Specialties & Services
          </a>
          <router-link to="/patient/doctors" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Our Doctors
          </router-link>
          <a href="#procedures" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Patients & Visitors
          </a>
          <a href="#telehealth" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Telehealth & International
          </a>
          <router-link to="/login" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Online Portals
          </router-link>
          <a href="#news" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            News & Events
          </a>
          <a href="#articles" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Health Library
          </a>
          <router-link to="/doctor/dashboard" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Healthcare Professionals (HCP)
          </router-link>
          <a href="#about" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            About Us
          </a>
          <a href="#contact" class="px-3 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white">
            Contact Us
          </a>
        </div>
      </div>
    </nav>

    <!-- Hero Announcement Slider Section (Matching St. Luke's Scanner Hero) -->
    <section class="relative bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative">
          <!-- Left: Hero Medical Facility / Scanner Image With Carousel Arrows -->
          <div class="lg:col-span-6 relative group">
            <div class="w-full aspect-[4/3] bg-slate-100 border border-slate-300 overflow-hidden relative">
              <img
                :src="heroSlides[currentHeroIndex].image"
                :alt="heroSlides[currentHeroIndex].title"
                class="w-full h-full object-cover transition-all duration-500"
              />
            </div>

            <!-- Left / Right Carousel Controls -->
            <button
              @click="prevHero"
              class="absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-slate-900/60 hover:bg-slate-900 text-white border border-white/20 transition-colors"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            <button
              @click="nextHero"
              class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-slate-900/60 hover:bg-slate-900 text-white border border-white/20 transition-colors"
            >
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>

          <!-- Right: Hero Headline, Subtitle & Read More Button -->
          <div class="lg:col-span-6 space-y-6">
            <div class="space-y-3">
              <span class="text-xs font-mono font-bold uppercase tracking-widest text-brand-600">
                {{ heroSlides[currentHeroIndex].category }}
              </span>
              <h1 class="text-3xl sm:text-4xl lg:text-4xl font-extrabold text-slate-950 tracking-tight leading-tight">
                {{ heroSlides[currentHeroIndex].title }}
              </h1>
              <p class="text-sm text-slate-600 leading-relaxed">
                {{ heroSlides[currentHeroIndex].description }}
              </p>
            </div>

            <div class="flex items-center space-x-3 pt-2">
              <button
                @click="openBookingModal"
                class="px-6 py-2.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase tracking-wider border border-brand-800 transition-colors"
              >
                Read More
              </button>
              <button
                @click="openBookingModal"
                class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider border border-slate-950 transition-colors"
              >
                Schedule Appointment
              </button>
            </div>

            <!-- Pagination Indicator Dots -->
            <div class="flex items-center space-x-2 pt-4">
              <button
                v-for="(_, idx) in heroSlides"
                :key="idx"
                @click="currentHeroIndex = idx"
                class="w-2.5 h-2.5 transition-colors border border-slate-400"
                :class="currentHeroIndex === idx ? 'bg-brand-600' : 'bg-slate-200 hover:bg-slate-300'"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Latest News Section (2-Column Underlined List Matching Image 1) -->
    <section id="news" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-8">
          <h2 class="text-xl font-black uppercase tracking-wider text-slate-950">
            Latest News
          </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
          <!-- News Column 1 & 2 Items -->
          <div
            v-for="item in latestNews"
            :key="item.title"
            class="border-b border-slate-300 pb-4 hover:border-brand-600 transition-colors cursor-pointer group"
            @click="openBookingModal"
          >
            <h4 class="text-sm font-bold text-brand-700 group-hover:text-brand-800 group-hover:underline leading-snug">
              {{ item.title }}
            </h4>
            <span class="text-xs text-slate-500 font-mono mt-1.5 block">
              {{ item.date }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Events Strip Section (4-Column Image Cards with Carousel Controls Matching Image 2) -->
    <section class="py-12 bg-slate-100 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-6">
          <h2 class="text-xl font-black uppercase tracking-wider text-slate-950">
            Events
          </h2>
        </div>

        <!-- Carousel Gallery Row with Outer Left/Right Arrows -->
        <div class="relative flex items-center">
          <button
            @click="prevEvents"
            class="p-2 border border-slate-300 bg-white text-slate-700 hover:bg-slate-200 transition-colors mr-3 flex-shrink-0"
          >
            <ChevronLeft class="w-5 h-5" />
          </button>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
            <div
              v-for="event in visibleEvents"
              :key="event.title"
              class="bg-white border border-slate-300 overflow-hidden cursor-pointer hover:border-brand-600 transition-all group"
              @click="openBookingModal"
            >
              <div class="aspect-[16/9] bg-slate-200 overflow-hidden">
                <img
                  :src="event.image"
                  :alt="event.title"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
              </div>
              <div class="p-3">
                <span class="text-[10px] font-mono font-bold text-brand-600 uppercase">{{ event.date }}</span>
                <h5 class="font-bold text-xs text-slate-900 mt-0.5 line-clamp-1 group-hover:text-brand-600">
                  {{ event.title }}
                </h5>
              </div>
            </div>
          </div>

          <button
            @click="nextEvents"
            class="p-2 border border-slate-300 bg-white text-slate-700 hover:bg-slate-200 transition-colors ml-3 flex-shrink-0"
          >
            <ChevronRight class="w-5 h-5" />
          </button>
        </div>

        <!-- Carousel Pagination Dots -->
        <div class="flex items-center justify-center space-x-2 mt-6">
          <span class="w-2.5 h-2.5 bg-brand-700 border border-slate-400"></span>
          <span class="w-2.5 h-2.5 bg-slate-300 border border-slate-400"></span>
          <span class="w-2.5 h-2.5 bg-slate-300 border border-slate-400"></span>
        </div>
      </div>
    </section>

    <!-- Health Articles Grid Section (4-Card Photo Grid Matching Image 2) -->
    <section id="articles" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-8">
          <h2 class="text-xl font-black uppercase tracking-wider text-slate-950">
            Health Articles
          </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="article in healthArticles"
            :key="article.title"
            class="bg-white border border-slate-200 hover:border-brand-600 transition-all cursor-pointer group flex flex-col justify-between"
            @click="openBookingModal"
          >
            <div class="aspect-[16/10] bg-slate-100 overflow-hidden border-b border-slate-200">
              <img
                :src="article.image"
                :alt="article.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            </div>
            <div class="p-4 flex-1 flex flex-col justify-between">
              <h4 class="font-bold text-xs text-brand-700 group-hover:text-brand-800 group-hover:underline leading-snug">
                {{ article.title }}
              </h4>
              <span class="text-[11px] text-slate-400 font-mono mt-3 block">
                {{ article.readTime }} &bull; Clinical Review
              </span>
            </div>
          </div>
        </div>

        <!-- View All Button -->
        <div class="text-center mt-10">
          <button
            @click="openBookingModal"
            class="px-8 py-2.5 border-2 border-brand-700 text-brand-700 hover:bg-brand-700 hover:text-white font-bold text-xs uppercase tracking-wider transition-colors"
          >
            View All
          </button>
        </div>
      </div>
    </section>

    <!-- Health Specialties Section (Matching Image 2 Section Header) -->
    <section id="specialties" class="py-12 bg-slate-50 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-8">
          <h2 class="text-xl font-black uppercase tracking-wider text-slate-950">
            Health Specialties
          </h2>
          <p class="text-xs text-slate-600 mt-1">Board-certified clinical departments providing comprehensive healthcare</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div
            v-for="spec in specialtiesList"
            :key="spec.name"
            @click="selectSpecialtyAndBook(spec.name)"
            class="p-4 bg-white border border-slate-300 hover:border-brand-600 cursor-pointer transition-colors flex flex-col justify-between"
          >
            <div>
              <div class="w-8 h-8 bg-slate-100 text-brand-600 flex items-center justify-center mb-3 border border-slate-200">
                <component :is="spec.icon" class="w-4 h-4" />
              </div>
              <h4 class="font-bold text-xs uppercase tracking-tight text-slate-900">
                {{ spec.name }}
              </h4>
              <span class="text-[11px] font-mono text-slate-500 mt-1 block">
                {{ spec.doctorCount }} Licensed Doctors
              </span>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-brand-600 font-bold uppercase">
              <span>Schedule</span>
              <span>&rarr;</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Hospital Institutional Footer -->
    <footer id="contact" class="bg-slate-900 text-white py-12 border-t-4 border-brand-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-xs font-mono">
          <!-- Col 1: Hospital Mission -->
          <div class="md:col-span-2 space-y-3">
            <div class="font-bold text-sm text-white uppercase tracking-wider">
              Medicon Medical Center Network
            </div>
            <p class="text-slate-400 font-sans text-xs max-w-md leading-relaxed">
              Official healthcare portal facilitating board-certified telehealth consultations, encrypted electronic medical records (EHR), automated appointment dispatching, and comprehensive clinical services.
            </p>
            <div class="text-[11px] text-emerald-400 flex items-center space-x-2 font-mono">
              <ShieldCheck class="w-4 h-4" />
              <span>HIPAA CERTIFIED &bull; ISO-9001 ACCREDITATION &bull; JCI STANDARDS</span>
            </div>
          </div>

          <!-- Col 2: Online Portals -->
          <div class="space-y-2">
            <div class="font-bold uppercase tracking-wider text-slate-300 border-b border-slate-800 pb-1">
              Online Portals
            </div>
            <ul class="space-y-1.5 text-slate-400">
              <li><router-link to="/patient/dashboard" class="hover:text-white">&bull; Patient Portal</router-link></li>
              <li><router-link to="/doctor/dashboard" class="hover:text-white">&bull; Physician Workspace</router-link></li>
              <li><router-link to="/admin/dashboard" class="hover:text-white">&bull; Admin Operations</router-link></li>
              <li><router-link to="/login" class="hover:text-white">&bull; Portal Sign-In</router-link></li>
            </ul>
          </div>

          <!-- Col 3: Direct Inquiries -->
          <div class="space-y-2">
            <div class="font-bold uppercase tracking-wider text-slate-300 border-b border-slate-800 pb-1">
              Clinical Hotlines
            </div>
            <ul class="space-y-1.5 text-slate-400">
              <li>Quezon City: +63-2-8723-0101</li>
              <li>Global City: +63-2-8789-7700</li>
              <li>Emergency Triage: +63-2-8521-0020</li>
              <li>Email: info@medicon.health</li>
            </ul>
          </div>
        </div>

        <div class="pt-6 border-t border-slate-800 text-center text-[11px] text-slate-500 font-mono">
          CONFIDENTIAL &bull; MEDICON MEDICAL CENTER &bull; ALL RIGHTS RESERVED
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
  Search,
  ChevronLeft,
  ChevronRight,
  ShieldCheck,
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
const searchQuery = ref('')
const currentHeroIndex = ref(0)
const eventsIndex = ref(0)

const dashboardRoute = computed(() => {
  if (auth.isAdmin) return '/admin/dashboard'
  if (auth.isDoctor) return '/doctor/dashboard'
  return '/patient/dashboard'
})

// Hero Carousel Slides matching Image 1
const heroSlides = [
  {
    category: 'CLINICAL INNOVATION & DIAGNOSTICS',
    title: 'Medicon – Global Medical Center Enters Futuristic Era with Next-Gen Photon-Counting CT Scanner',
    description: 'Providing unprecedented spatial resolution and reduced radiation dosage for cardiovascular, neurological, and oncological diagnostic imaging.',
    image: 'https://images.unsplash.com/photo-1516549655169-df83a0774514?w=800&auto=format&fit=crop&q=80',
  },
  {
    category: 'CARDIOVASCULAR MEDICINE',
    title: 'State-of-the-Art Electrophysiology & Catheterization Laboratories Open for Patient Encounters',
    description: 'Advanced minimally invasive diagnostic suites operating with 24/7 emergency response protocols and board-certified cardiologists.',
    image: 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800&auto=format&fit=crop&q=80',
  },
  {
    category: 'TELEHEALTH ENCOUNTERS',
    title: 'Encrypted Telemedicine Network Connects Patients Worldwide with Board-Certified Specialists',
    description: 'Instant HD WebRTC virtual visits, synchronized digital diagnostic notes, and automated electronic prescriptions delivered to your portal.',
    image: 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=800&auto=format&fit=crop&q=80',
  },
]

const prevHero = () => {
  currentHeroIndex.value = (currentHeroIndex.value - 1 + heroSlides.length) % heroSlides.length
}

const nextHero = () => {
  currentHeroIndex.value = (currentHeroIndex.value + 1) % heroSlides.length
}

// Latest News List matching Image 1
const latestNews = [
  {
    title: 'Key Steps to Keep Your Lungs Healthy and Disease-Free',
    date: 'Aug 14, 2026',
  },
  {
    title: 'Advanced Mole Mapping and Hair Trichoscopy for Early Skin Cancer Detection',
    date: 'Aug 11, 2026',
  },
  {
    title: 'Targeting Stubborn Pigmentation: How Q-Switched Precision Laser Restores Skin Health',
    date: 'Aug 06, 2026',
  },
  {
    title: 'A New Era for Your Heart: How Photon-Counting CT is Revolutionizing Cardiovascular Care',
    date: 'Aug 05, 2026',
  },
]

// Events List matching Image 2
const allEvents = [
  {
    title: 'National Maternal & Infant Wellness Workshop: One Latch, One Love',
    date: 'AUG 28, 2026',
    image: 'https://images.unsplash.com/photo-1555252333-9f8e92e65df9?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Compassionate Palliative Care: Patient-Centered Support Protocols',
    date: 'SEP 04, 2026',
    image: 'https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Pediatric Immunization & Preventative Healthcare Summit 2026',
    date: 'SEP 12, 2026',
    image: 'https://images.unsplash.com/photo-1584515933487-779824d29309?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Golden Vision, Golden Years: Senior Ophthalmology & Glaucoma Screenings',
    date: 'SEP 20, 2026',
    image: 'https://images.unsplash.com/photo-1579684453423-f84349ef60b0?w=500&auto=format&fit=crop&q=80',
  },
]

const visibleEvents = computed(() => allEvents)

const prevEvents = () => {
  eventsIndex.value = (eventsIndex.value - 1 + allEvents.length) % allEvents.length
}

const nextEvents = () => {
  eventsIndex.value = (eventsIndex.value + 1) % allEvents.length
}

// Health Articles List matching Image 2
const healthArticles = [
  {
    title: 'Shielding Your Skin: Understanding Early Skin Cancer Detection and Precision Mohs Micrographic Surgery',
    readTime: '5 min read',
    image: 'https://images.unsplash.com/photo-1512290900672-1f02e6b0f023?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Beyond Burnout: Recognizing Clinical Exhaustion and When to Begin Neurological and Mental Health Recovery',
    readTime: '7 min read',
    image: 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Weight Management and Metabolic Health: Eating Smarter, Not Less for Long-Term Cardiovascular Longevity',
    readTime: '4 min read',
    image: 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Rediscover Life’s Rhythm: Advanced Percutaneous Coronary Angioplasty at Medicon Heart Center',
    readTime: '6 min read',
    image: 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=500&auto=format&fit=crop&q=80',
  },
]

// Specialties
const specialtiesList = [
  { name: 'Cardiology', doctorCount: 24, icon: HeartPulse },
  { name: 'Neurology', doctorCount: 18, icon: Activity },
  { name: 'Orthopedic', doctorCount: 21, icon: Shield },
  { name: 'Pediatrics', doctorCount: 19, icon: Smile },
  { name: 'Dermatology', doctorCount: 15, icon: Sparkles },
  { name: 'General Practice', doctorCount: 35, icon: Stethoscope },
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
