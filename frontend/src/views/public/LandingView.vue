<template>
  <div class="min-h-screen bg-white text-slate-900 font-sans antialiased">
    <!-- 1. Top Utility & Location Header Bar -->
    <div class="border-b border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 py-3 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Hospital Brand Logo -->
        <router-link to="/" class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-slate-950 text-white flex items-center justify-center font-black text-xl border border-slate-800">
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
            <a href="tel:+63287230101" class="text-brand-700 font-mono font-bold hover:underline">+63-2-8723-0101</a>
          </div>
          <div>
            <span class="block font-bold uppercase text-slate-800 tracking-wider">GLOBAL CITY</span>
            <a href="tel:+63287897700" class="text-brand-700 font-mono font-bold hover:underline">+63-2-8789-7700</a>
          </div>
          <div>
            <span class="block font-bold uppercase text-slate-800 tracking-wider">24/7 EMERGENCY</span>
            <span class="text-rose-600 font-mono font-bold">+63-2-8521-0020</span>
          </div>
        </div>

        <!-- Search Bar & Dynamic Authentication Capsule -->
        <div class="flex items-center space-x-3">
          <div class="relative w-44 sm:w-52">
            <input
              type="text"
              v-model="doctorSearchQuery"
              placeholder="Search doctors, specialties..."
              class="w-full pl-3 pr-8 py-1.5 border border-slate-300 text-xs focus:border-slate-800 focus:outline-none bg-white placeholder:text-slate-400"
            />
            <Search class="w-4 h-4 text-slate-400 absolute right-2.5 top-2" />
          </div>

          <!-- Signed In User Status Capsule -->
          <template v-if="auth.isAuthenticated">
            <div class="flex items-center space-x-2 bg-slate-50 border border-slate-300 px-3 py-1">
              <div class="w-6 h-6 rounded-full bg-brand-700 text-white flex items-center justify-center font-bold text-xs uppercase">
                {{ auth.user?.name?.charAt(0) || 'U' }}
              </div>
              <div class="hidden sm:block text-left">
                <span class="text-xs font-bold text-slate-900 block truncate max-w-[100px]">{{ auth.user?.name }}</span>
                <span class="text-[9px] font-mono text-brand-700 font-bold uppercase block">{{ auth.role }}</span>
              </div>
              <router-link
                :to="dashboardRoute"
                class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold uppercase tracking-wider transition-colors"
              >
                Dashboard
              </router-link>
              <button
                @click="handleSignOut"
                title="Sign Out"
                class="p-1 hover:bg-slate-200 text-slate-600 hover:text-rose-600 transition-colors"
              >
                <LogOut class="w-3.5 h-3.5" />
              </button>
            </div>
          </template>

          <!-- Guest / Not Signed In Controls -->
          <template v-else>
            <div class="flex items-center space-x-2">
              <router-link
                to="/login"
                class="px-4 py-1.5 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold uppercase tracking-wider border border-brand-800 flex items-center space-x-1.5 shadow-xs transition-colors"
              >
                <LogIn class="w-3.5 h-3.5" />
                <span>Sign In</span>
              </router-link>
              <router-link
                to="/register"
                class="hidden sm:inline-block px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors"
              >
                Register
              </router-link>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- 2. Solid Full-Width Navigation Bar -->
    <nav class="bg-slate-900 text-white border-b-2 border-brand-600 sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 flex items-center justify-between overflow-x-auto scrollbar-none">
        <div class="flex items-center text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
          <a href="#specialties" @click="scrollToAnchor('#specialties', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            Health Specialties
          </a>
          <a href="#doctors" @click="scrollToAnchor('#doctors', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            Our Doctors
          </a>
          <a href="#telehealth-join" @click="scrollToAnchor('#telehealth-join', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white text-amber-300 cursor-pointer">
            Telehealth Room
          </a>
          <a href="#about" @click="scrollToAnchor('#about', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            About Us
          </a>
          <a href="#leadership" @click="scrollToAnchor('#leadership', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            Clinical Leadership
          </a>
          <a href="#articles" @click="scrollToAnchor('#articles', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            Health Library
          </a>
          <a href="#news" @click="scrollToAnchor('#news', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            News & Events
          </a>
          <a href="#contact" @click="scrollToAnchor('#contact', $event)" class="px-3.5 py-3.5 hover:bg-brand-700 transition-colors border-b-2 border-transparent hover:border-white cursor-pointer">
            Contact Us
          </a>
        </div>

        <div class="hidden md:flex items-center space-x-2 text-xs font-mono">
          <button
            @click="openBookingModal"
            class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white font-bold uppercase text-[10px] tracking-wider transition-colors"
          >
            Book Appointment
          </button>
        </div>
      </div>
    </nav>

    <!-- 3. Telehealth Quick-Join Strip -->
    <section id="telehealth-join" class="bg-slate-100 border-b border-slate-300 py-3.5 px-4 sm:px-8">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center space-x-2.5">
          <div class="w-8 h-8 rounded bg-brand-700 text-white flex items-center justify-center">
            <Video class="w-4 h-4" />
          </div>
          <div>
            <span class="text-xs font-bold text-slate-900 uppercase tracking-tight block">Instant Telehealth Room Access</span>
            <span class="text-[11px] text-slate-500">Have a 3-part consultation code? Enter below to join the Green Room.</span>
          </div>
        </div>

        <div class="flex items-center space-x-2 w-full md:w-auto">
          <input
            type="text"
            v-model="quickRoomCode"
            placeholder="e.g. k9x-yqp2-481"
            class="px-3 py-1.5 border border-slate-300 bg-white text-xs font-mono uppercase focus:outline-none focus:border-brand-700 w-full sm:w-48"
            @keyup.enter="joinQuickRoom"
          />
          <button
            @click="joinQuickRoom"
            class="px-4 py-1.5 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase tracking-wider transition-colors whitespace-nowrap"
          >
            Join Room
          </button>
          <button
            @click="startAdHocRoom"
            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs font-bold uppercase tracking-wider transition-colors whitespace-nowrap hidden sm:inline-block"
          >
            New Room
          </button>
        </div>
      </div>
    </section>

    <!-- 4. Hero Announcement Slider Section (with Skeleton Loading Shimmer) -->
    <section class="relative bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 lg:py-12">
        <!-- Skeleton Loading Hero -->
        <div v-if="isLoading" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center animate-pulse">
          <div class="lg:col-span-6 aspect-[4/3] bg-slate-200"></div>
          <div class="lg:col-span-6 space-y-4">
            <div class="h-4 bg-slate-200 w-1/4"></div>
            <div class="h-10 bg-slate-200 w-3/4"></div>
            <div class="h-20 bg-slate-200 w-full"></div>
            <div class="flex space-x-3 pt-2">
              <div class="h-9 bg-slate-200 w-28"></div>
              <div class="h-9 bg-slate-200 w-36"></div>
            </div>
          </div>
        </div>

        <!-- Populated Hero Slider -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative">
          <!-- Left: Medical Facility / Scanner Image With Carousel Arrows -->
          <div class="lg:col-span-6 relative group">
            <div class="w-full aspect-[4/3] bg-slate-100 border border-slate-300 overflow-hidden relative shadow-xs">
              <img
                :src="heroSlides[currentHeroIndex].image"
                :alt="heroSlides[currentHeroIndex].title"
                class="w-full h-full object-cover transition-all duration-500"
              />
            </div>

            <!-- Left / Right Carousel Controls -->
            <button
              @click="prevHero"
              title="Previous Slide"
              class="absolute left-2 top-1/2 -translate-y-1/2 p-2 bg-slate-900/70 hover:bg-slate-900 text-white border border-white/20 transition-colors"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            <button
              @click="nextHero"
              title="Next Slide"
              class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-slate-900/70 hover:bg-slate-900 text-white border border-white/20 transition-colors"
            >
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>

          <!-- Right: Hero Headline, Subtitle & Action Buttons -->
          <div class="lg:col-span-6 space-y-6">
            <div class="space-y-3">
              <span class="text-xs font-mono font-bold uppercase tracking-widest text-brand-700">
                {{ heroSlides[currentHeroIndex].category }}
              </span>
              <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-950 tracking-tight leading-tight">
                {{ heroSlides[currentHeroIndex].title }}
              </h1>
              <p class="text-sm text-slate-600 leading-relaxed">
                {{ heroSlides[currentHeroIndex].description }}
              </p>
            </div>

            <div class="flex items-center space-x-3 pt-2">
              <button
                @click="openBookingModal"
                class="px-6 py-2.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase tracking-wider border border-brand-800 transition-colors shadow-xs"
              >
                Schedule Appointment
              </button>
              <a
                href="#about"
                @click="scrollToAnchor('#about', $event)"
                class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider border border-slate-950 transition-colors cursor-pointer"
              >
                About Our Center
              </a>
            </div>

            <!-- Pagination Indicator Dots -->
            <div class="flex items-center space-x-2 pt-4">
              <button
                v-for="(_, idx) in heroSlides"
                :key="idx"
                @click="currentHeroIndex = idx"
                class="w-3 h-3 transition-colors border border-slate-400"
                :class="currentHeroIndex === idx ? 'bg-brand-700' : 'bg-slate-200 hover:bg-slate-300'"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Interactive Doctor Directory Section -->
    <section id="doctors" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <h2 class="text-2xl font-black uppercase tracking-wider text-slate-950">
              Our Medical Specialists
            </h2>
            <p class="text-xs text-slate-600 mt-1">Board-certified physicians available for in-person and telehealth consultations</p>
          </div>

          <!-- Specialty Filter Chips -->
          <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 text-xs font-mono">
            <button
              v-for="filter in specialtyFilters"
              :key="filter"
              @click="selectedSpecialty = filter"
              class="px-3 py-1.5 border font-bold uppercase transition-colors whitespace-nowrap"
              :class="selectedSpecialty === filter ? 'bg-brand-700 text-white border-brand-700' : 'bg-slate-50 text-slate-700 border-slate-300 hover:bg-slate-100'"
            >
              {{ filter }}
            </button>
          </div>
        </div>

        <!-- Skeleton Loading Grid for Doctors -->
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="border border-slate-200 p-5 space-y-4 animate-pulse">
            <div class="flex space-x-4">
              <div class="w-16 h-16 bg-slate-200 rounded"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-slate-200 w-3/4"></div>
                <div class="h-3 bg-slate-200 w-1/2"></div>
              </div>
            </div>
            <div class="h-12 bg-slate-200"></div>
          </div>
        </div>

        <!-- Populated Doctor Cards Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="doc in filteredDoctors"
            :key="doc.id"
            class="bg-white border border-slate-300 hover:border-brand-700 transition-all p-5 flex flex-col justify-between shadow-xs group"
          >
            <div>
              <div class="flex items-start space-x-4">
                <img
                  :src="doc.avatar_url"
                  :alt="doc.name"
                  class="w-16 h-16 object-cover border border-slate-300 flex-shrink-0"
                />
                <div class="flex-1 min-w-0">
                  <span class="px-2 py-0.5 bg-brand-50 border border-brand-200 text-brand-800 text-[10px] font-mono font-bold uppercase rounded inline-block mb-1">
                    {{ doc.specialty }}
                  </span>
                  <h3 class="font-bold text-sm text-slate-900 truncate group-hover:text-brand-700">
                    {{ doc.name }}
                  </h3>
                  <div class="flex items-center space-x-2 text-[11px] text-slate-500 font-mono mt-0.5">
                    <span>{{ doc.experience }} Yrs Exp</span>
                    <span>&bull;</span>
                    <span class="text-amber-600 font-bold">★ {{ doc.rating }}</span>
                  </div>
                </div>
              </div>

              <p class="text-xs text-slate-600 font-sans mt-3 line-clamp-2 leading-relaxed">
                {{ doc.bio }}
              </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
              <div>
                <span class="text-[10px] text-slate-400 font-mono uppercase block">Consultation Fee</span>
                <span class="text-sm font-bold font-mono text-slate-900">₱{{ (doc.consultation_fee_cents ? doc.consultation_fee_cents / 100 : (doc.consultation_fee || 120)).toFixed(2) }}</span>
              </div>
              <button
                @click="openBookingForDoctor(doc)"
                class="px-4 py-1.5 bg-slate-900 hover:bg-brand-700 text-white font-mono text-xs font-bold uppercase transition-colors"
              >
                Book Visit
              </button>
            </div>
          </div>
        </div>

        <div v-if="!isLoading && filteredDoctors.length === 0" class="text-center py-12 border border-dashed border-slate-300">
          <p class="text-sm text-slate-500 font-mono">No doctors found matching "{{ doctorSearchQuery }}".</p>
          <button @click="doctorSearchQuery = ''; selectedSpecialty = 'All'" class="text-xs text-brand-700 font-bold underline mt-2">
            Reset Filters
          </button>
        </div>
      </div>
    </section>

    <!-- 6. Health Specialties Grid -->
    <section id="specialties" class="py-12 bg-slate-50 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-8">
          <h2 class="text-2xl font-black uppercase tracking-wider text-slate-950">
            Clinical Health Specialties
          </h2>
          <p class="text-xs text-slate-600 mt-1">Board-certified clinical departments providing comprehensive healthcare</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div
            v-for="spec in specialtiesList"
            :key="spec.name"
            @click="selectSpecialtyAndFilter(spec.name)"
            class="p-4 bg-white border border-slate-300 hover:border-brand-700 cursor-pointer transition-all flex flex-col justify-between shadow-xs group"
          >
            <div>
              <div class="w-9 h-9 bg-slate-100 text-brand-700 flex items-center justify-center mb-3 border border-slate-200 group-hover:bg-brand-700 group-hover:text-white transition-colors">
                <component :is="spec.icon" class="w-4 h-4" />
              </div>
              <h4 class="font-bold text-xs uppercase tracking-tight text-slate-900">
                {{ spec.name }}
              </h4>
              <span class="text-[11px] font-mono text-slate-500 mt-1 block">
                {{ spec.doctorCount }} Licensed Doctors
              </span>
            </div>
            <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono text-brand-700 font-bold uppercase">
              <span>Explore</span>
              <span>&rarr;</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 7. About Us & Clinical Standards Section -->
    <section id="about" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
          <div class="lg:col-span-6 space-y-4">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-brand-700">
              INSTITUTIONAL ACCREDITATION & ETHOS
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-950 uppercase tracking-tight leading-tight">
              Pioneering Clinical Excellence & Patient-Centric Telehealth
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
              Medicon Medical Center Network integrates world-class inpatient healthcare with encrypted digital telehealth infrastructure. Our hospital network serves over 250,000 patient encounters annually across specialized tertiary centers in Quezon City and Global City.
            </p>
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
              Every clinical encounter adheres to international Joint Commission International (JCI) standards, with 100% HIPAA-compliant data encryption, automated SOAP charting, and machine learning triage designed to eliminate scheduling delays.
            </p>

            <div class="pt-2 grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-slate-200 text-xs font-mono">
              <div class="p-3 bg-slate-50 border border-slate-200">
                <span class="text-xl font-bold text-slate-900 block">99.4%</span>
                <span class="text-[10px] text-slate-500 uppercase">Diagnostic Accuracy</span>
              </div>
              <div class="p-3 bg-slate-50 border border-slate-200">
                <span class="text-xl font-bold text-slate-900 block">25,000+</span>
                <span class="text-[10px] text-slate-500 uppercase">Telehealth Visits</span>
              </div>
              <div class="p-3 bg-slate-50 border border-slate-200">
                <span class="text-xl font-bold text-slate-900 block">24/7</span>
                <span class="text-[10px] text-slate-500 uppercase">Clinical Dispatch</span>
              </div>
            </div>

            <div class="pt-2">
              <button
                @click="showCharterModal = true"
                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs font-bold uppercase tracking-wider transition-colors"
              >
                Read Quality & Safety Charter
              </button>
            </div>
          </div>

          <div class="lg:col-span-6 bg-slate-100 border border-slate-300 p-6 space-y-4">
            <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wide border-b border-slate-200 pb-2">
              Clinical Quality & Compliance Standards
            </h3>
            <div class="space-y-3 text-xs">
              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 rounded bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                  1
                </div>
                <div>
                  <h4 class="font-bold text-slate-900">HIPAA & AES-256 Data Protection</h4>
                  <p class="text-slate-600 text-[11px] mt-0.5">All diagnostic SOAP notes, vitals, and video streams are encrypted at rest and in transit.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 rounded bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                  2
                </div>
                <div>
                  <h4 class="font-bold text-slate-900">Machine Learning Attendance Triage</h4>
                  <p class="text-slate-600 text-[11px] mt-0.5">Scikit-Learn predictive algorithms evaluate attendance patterns to ensure maximum clinic availability.</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <div class="w-6 h-6 rounded bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
                  3
                </div>
                <div>
                  <h4 class="font-bold text-slate-900">Ephemeral Telehealth Architecture</h4>
                  <p class="text-slate-600 text-[11px] mt-0.5">In-room chat messages and WebRTC media tokens are permanently wiped upon consultation closure.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 8. Clinical Leadership Section -->
    <section id="leadership" class="py-12 bg-slate-50 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-8">
          <h2 class="text-2xl font-black uppercase tracking-wider text-slate-950">
            Clinical Leadership & Medical Board
          </h2>
          <p class="text-xs text-slate-600 mt-1">Guiding medical standards, specialist peer reviews, and institutional ethics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white border border-slate-300 p-5 space-y-3">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-lg">
                E
              </div>
              <div>
                <h4 class="font-bold text-sm text-slate-900">Dr. Eleanor Vance, MD</h4>
                <span class="text-xs font-mono text-brand-700 font-bold block">Chief Medical Officer (CMO)</span>
              </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-sans">
              Oversees clinical quality assurance, multi-disciplinary hospital governance, physician credentialing, and ethical AI deployment across Medicon.
            </p>
          </div>

          <div class="bg-white border border-slate-300 p-5 space-y-3">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-lg">
                S
              </div>
              <div>
                <h4 class="font-bold text-sm text-slate-900">Dr. Sarah Jenkins, MD, FACC</h4>
                <span class="text-xs font-mono text-brand-700 font-bold block">Director of Cardiovascular Medicine</span>
              </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-sans">
              Harvard Medical School alumna directing preventative cardiology, emergency catheterization, and remote vital monitoring telemetry.
            </p>
          </div>

          <div class="bg-white border border-slate-300 p-5 space-y-3">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-full bg-brand-800 text-white flex items-center justify-center font-bold text-lg">
                M
              </div>
              <div>
                <h4 class="font-bold text-sm text-slate-900">Dr. Marcus Chen, MD, PhD</h4>
                <span class="text-xs font-mono text-brand-700 font-bold block">Director of Neurology & Stroke Care</span>
              </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-sans">
              Leads clinical neuro-diagnostic protocols, acute tele-stroke intervention systems, and chronic neurological rehabilitation care.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- 9. Health Articles Section -->
    <section id="articles" class="py-12 bg-white border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="text-center mb-8">
          <h2 class="text-2xl font-black uppercase tracking-wider text-slate-950">
            Health Library & Clinical Articles
          </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="article in healthArticles"
            :key="article.title"
            class="bg-white border border-slate-200 hover:border-brand-700 transition-all cursor-pointer group flex flex-col justify-between shadow-xs"
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
      </div>
    </section>

    <!-- 10. Interactive Contact Us & Inquiries Form Section -->
    <section id="contact" class="py-12 bg-slate-100 border-b border-slate-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <!-- Left: Contact Details & Hotlines -->
          <div class="lg:col-span-5 space-y-6">
            <div>
              <span class="text-xs font-mono font-bold uppercase tracking-widest text-brand-700">
                24/7 CLINICAL ASSISTANCE
              </span>
              <h2 class="text-2xl font-black text-slate-950 uppercase tracking-tight mt-1">
                Contact Medicon Medical Center
              </h2>
              <p class="text-xs text-slate-600 mt-2 leading-relaxed font-sans">
                Our patient coordination and triage teams are available around the clock to assist with scheduling, clinical inquiries, and telehealth setup.
              </p>
            </div>

            <div class="space-y-3 text-xs font-mono">
              <div class="p-3.5 bg-white border border-slate-300 space-y-1">
                <span class="font-bold text-slate-900 uppercase block">Quezon City Main Hospital</span>
                <p class="text-slate-600 text-[11px] font-sans">279 E. Rodriguez Sr. Ave, Cathedral Heights, Quezon City</p>
                <a href="tel:+63287230101" class="text-brand-700 font-bold hover:underline block">+63-2-8723-0101</a>
              </div>

              <div class="p-3.5 bg-white border border-slate-300 space-y-1">
                <span class="font-bold text-slate-900 uppercase block">Global City Medical Center</span>
                <p class="text-slate-600 text-[11px] font-sans">32nd Street cor. 5th Ave, Bonifacio Global City, Taguig</p>
                <a href="tel:+63287897700" class="text-brand-700 font-bold hover:underline block">+63-2-8789-7700</a>
              </div>

              <div class="p-3.5 bg-white border border-slate-300 space-y-1">
                <span class="font-bold text-slate-900 uppercase block">HIPAA Compliance & Privacy Office</span>
                <p class="text-slate-600 text-[11px] font-sans">For medical records verification and privacy inquiries:</p>
                <span class="text-brand-700 font-bold block">privacy@medicon.health</span>
              </div>
            </div>
          </div>

          <!-- Right: Working Interactive Contact Form -->
          <div class="lg:col-span-7 bg-white border border-slate-300 p-6 sm:p-8 shadow-xs">
            <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight mb-1">
              Send a Clinical or Portal Inquiry
            </h3>
            <p class="text-xs text-slate-500 mb-6 font-sans">Please complete the form below. A triage coordinator will respond within 2 hours.</p>

            <!-- Success Alert Banner -->
            <div
              v-if="contactSubmitted"
              class="mb-6 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-mono space-y-1"
            >
              <div class="font-bold flex items-center space-x-1.5">
                <ShieldCheck class="w-4 h-4 text-emerald-600" />
                <span>INQUIRY #{{ contactTicketId }} RECEIVED</span>
              </div>
              <p class="font-sans text-[11px] text-emerald-700">
                Thank you, {{ contactForm.name }}. Your message has been logged in our clinical triage queue. Our team will contact you at {{ contactForm.email }}.
              </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitContactForm" class="space-y-4 text-xs font-mono">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block font-bold text-slate-700 uppercase mb-1">Your Full Name *</label>
                  <input
                    type="text"
                    v-model="contactForm.name"
                    required
                    placeholder="Jane Doe"
                    class="w-full p-2.5 border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:border-slate-800 font-sans text-xs"
                  />
                </div>
                <div>
                  <label class="block font-bold text-slate-700 uppercase mb-1">Email Address *</label>
                  <input
                    type="email"
                    v-model="contactForm.email"
                    required
                    placeholder="jane.doe@example.com"
                    class="w-full p-2.5 border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:border-slate-800 font-sans text-xs"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                  <input
                    type="tel"
                    v-model="contactForm.phone"
                    placeholder="+63 917 123 4567"
                    class="w-full p-2.5 border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:border-slate-800 font-sans text-xs"
                  />
                </div>
                <div>
                  <label class="block font-bold text-slate-700 uppercase mb-1">Inquiry Category *</label>
                  <select
                    v-model="contactForm.category"
                    required
                    class="w-full p-2.5 border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:border-slate-800 font-sans text-xs"
                  >
                    <option value="General Clinical Inquiry">General Clinical Inquiry</option>
                    <option value="Appointment Assistance">Appointment Scheduling</option>
                    <option value="Telehealth Technical Support">Telehealth Video & Code Support</option>
                    <option value="Medical Records & Billing">Medical Records & e-Prescriptions</option>
                    <option value="Hospital Partnership">Hospital & Laboratory Partnership</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Subject *</label>
                <input
                  type="text"
                  v-model="contactForm.subject"
                  required
                  placeholder="Inquiry regarding Cardiology Telehealth consultation"
                  class="w-full p-2.5 border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:border-slate-800 font-sans text-xs"
                />
              </div>

              <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Message Details *</label>
                <textarea
                  v-model="contactForm.message"
                  required
                  rows="4"
                  placeholder="Please describe your question or assistance needed..."
                  class="w-full p-2.5 border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:border-slate-800 font-sans text-xs"
                ></textarea>
              </div>

              <button
                type="submit"
                :disabled="isSubmittingContact"
                class="w-full py-3 bg-brand-700 hover:bg-brand-800 disabled:bg-slate-400 text-white font-bold uppercase tracking-wider transition-colors shadow-xs"
              >
                <span v-if="isSubmittingContact">Submitting to Clinical Queue...</span>
                <span v-else>Submit Inquiry</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- 11. Institutional Footer -->
    <footer class="bg-slate-900 text-white py-12 border-t-4 border-brand-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-xs font-mono">
          <!-- Col 1: Hospital Mission -->
          <div class="md:col-span-2 space-y-3">
            <div class="font-bold text-sm text-white uppercase tracking-wider">
              Medicon Medical Center Network
            </div>
            <p class="text-slate-400 font-sans text-xs max-w-md leading-relaxed">
              Official clinical healthcare portal facilitating board-certified telehealth consultations, encrypted electronic medical records (EHR), automated appointment dispatching, and comprehensive outpatient services.
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

    <!-- Quality Charter Modal -->
    <div
      v-if="showCharterModal"
      class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white border-2 border-slate-300 max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
          <div class="flex items-center space-x-2">
            <ShieldCheck class="w-5 h-5 text-brand-700" />
            <h3 class="font-bold text-sm uppercase text-slate-900">Medicon Clinical Quality Charter</h3>
          </div>
          <button @click="showCharterModal = false" class="text-slate-400 hover:text-slate-800">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="text-xs text-slate-600 space-y-3 font-sans leading-relaxed">
          <p>
            Medicon Medical Center Network is committed to providing evidence-based healthcare with zero compromise on diagnostic accuracy, patient privacy, and clinical accessibility.
          </p>
          <div class="p-3 bg-slate-50 border border-slate-200 space-y-1 font-mono text-[11px]">
            <span class="font-bold text-slate-800 block uppercase">&bull; Encrypted Telehealth Architecture</span>
            <span class="text-slate-600 block">WebRTC video consultation feeds are end-to-end encrypted with zero server-side recordings.</span>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200 space-y-1 font-mono text-[11px]">
            <span class="font-bold text-slate-800 block uppercase">&bull; Transparent Doctor Credentials</span>
            <span class="text-slate-600 block">All attending physicians are actively licensed by the Professional Regulation Commission (PRC) and board-certified in their medical specialties.</span>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-200 flex justify-end">
          <button
            @click="showCharterModal = false"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-mono text-xs font-bold uppercase"
          >
            Close Charter
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BookAppointmentModal from '@/components/patient/BookAppointmentModal.vue'
import { defaultDoctors, generateUniqueRoomCode } from '@/services/mockData'
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
  Video,
  LogIn,
  LogOut,
  X,
} from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()

const isLoading = ref(true)
const showBookingModal = ref(false)
const showCharterModal = ref(false)
const doctorSearchQuery = ref('')
const selectedSpecialty = ref('All')
const quickRoomCode = ref('')
const currentHeroIndex = ref(0)

// Interactive Contact Form State
const contactSubmitted = ref(false)
const isSubmittingContact = ref(false)
const contactTicketId = ref('')
const contactForm = ref({
  name: '',
  email: '',
  phone: '',
  category: 'General Clinical Inquiry',
  subject: '',
  message: '',
})

onMounted(() => {
  // Simulate smooth 450ms initial data loading state
  setTimeout(() => {
    isLoading.value = false
  }, 450)
})

const dashboardRoute = computed(() => {
  if (auth.isAdmin) return '/admin/dashboard'
  if (auth.isDoctor) return '/doctor/dashboard'
  return '/patient/dashboard'
})

const handleSignOut = async () => {
  await auth.logout()
}

// Quick Room Code Launcher
const joinQuickRoom = () => {
  if (!quickRoomCode.value.trim()) return
  const clean = quickRoomCode.value.trim().replace(/^#/, '')
  router.push(`/telehealth/room/${clean}`)
}

const startAdHocRoom = () => {
  const newCode = generateUniqueRoomCode()
  router.push(`/telehealth/room/${newCode}`)
}

// Specialty Filters
const specialtyFilters = ['All', 'Cardiology', 'Neurology', 'Dermatology', 'General Practice', 'Psychiatry', 'Orthopedics']

const filteredDoctors = computed(() => {
  return defaultDoctors.filter((doc) => {
    const matchesSpecialty = selectedSpecialty.value === 'All' || doc.specialty.toLowerCase() === selectedSpecialty.value.toLowerCase()
    const query = doctorSearchQuery.value.toLowerCase().trim()
    const matchesQuery = !query || doc.name.toLowerCase().includes(query) || doc.specialty.toLowerCase().includes(query) || doc.bio.toLowerCase().includes(query)
    return matchesSpecialty && matchesQuery
  })
})

const openBookingForDoctor = (doctor) => {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: '/patient/appointments' } })
    return
  }
  showBookingModal.value = true
}

const scrollToAnchor = (targetId, e) => {
  if (e) e.preventDefault()
  const cleanId = targetId.replace(/^#/, '')
  const el = document.getElementById(cleanId)
  if (el) {
    const navOffset = 60
    const targetPosition = el.getBoundingClientRect().top + window.pageYOffset - navOffset
    const startPosition = window.pageYOffset
    const distance = targetPosition - startPosition
    const duration = 300
    let startTime = null

    function animation(currentTime) {
      if (startTime === null) startTime = currentTime
      const timeElapsed = currentTime - startTime
      const progress = Math.min(timeElapsed / duration, 1)
      // fast easeOutCubic
      const ease = 1 - Math.pow(1 - progress, 3)
      window.scrollTo(0, startPosition + distance * ease)
      if (timeElapsed < duration) {
        requestAnimationFrame(animation)
      }
    }

    requestAnimationFrame(animation)
  }
}

const selectSpecialtyAndFilter = (specialtyName) => {
  selectedSpecialty.value = specialtyName
  scrollToAnchor('#doctors')
}

// Hero Carousel Slides
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

// Health Articles List
const healthArticles = [
  {
    title: 'Shielding Your Skin: Understanding Early Skin Cancer Detection and Precision Mohs Surgery',
    readTime: '5 min read',
    image: 'https://images.unsplash.com/photo-1512290900672-1f02e6b0f023?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Beyond Burnout: Recognizing Clinical Exhaustion and When to Begin Neurological Care',
    readTime: '7 min read',
    image: 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Weight Management and Metabolic Health: Eating Smarter for Long-Term Cardiovascular Longevity',
    readTime: '4 min read',
    image: 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=500&auto=format&fit=crop&q=80',
  },
  {
    title: 'Rediscover Life’s Rhythm: Advanced Percutaneous Coronary Angioplasty at Medicon Heart Center',
    readTime: '6 min read',
    image: 'https://images.unsplash.com/photo-1551076805-e1869033e561?w=500&auto=format&fit=crop&q=80',
  },
]

// Specialties List
const specialtiesList = [
  { name: 'Cardiology', doctorCount: 24, icon: HeartPulse },
  { name: 'Neurology', doctorCount: 18, icon: Activity },
  { name: 'Orthopedics', doctorCount: 21, icon: Shield },
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

const handleBookingSuccess = () => {
  if (auth.isAuthenticated) {
    router.push('/patient/appointments')
  }
}

// Contact Form Handler
const submitContactForm = () => {
  isSubmittingContact.value = true
  setTimeout(() => {
    isSubmittingContact.value = false
    contactTicketId.value = 'MED-' + Math.floor(1000 + Math.random() * 9000)
    contactSubmitted.value = true
    // Reset form after short delay
    setTimeout(() => {
      contactForm.value = {
        name: '',
        email: '',
        phone: '',
        category: 'General Clinical Inquiry',
        subject: '',
        message: '',
      }
    }, 500)
  }, 700)
}
</script>
