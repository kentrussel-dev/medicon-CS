<template>
  <header class="bg-white dark:bg-slate-800 border-b-2 border-slate-300 dark:border-slate-700 px-4 sm:px-6 py-2.5 flex items-center justify-between sticky top-0 z-30">
    <!-- Left: Brand / Sidebar toggle -->
    <div class="flex items-center space-x-3">
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden p-1.5 border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition-colors"
      >
        <Menu class="w-4 h-4" />
      </button>
      <router-link to="/" class="flex items-center space-x-2">
        <div class="w-7 h-7 bg-brand-600 text-white flex items-center justify-center font-bold text-xs border border-brand-700">
          M
        </div>
        <div class="flex flex-col">
          <span class="font-black text-xs uppercase tracking-wider text-slate-900 dark:text-white leading-none">
            Medicon Clinical Systems
          </span>
          <span class="text-[9px] font-mono text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none mt-0.5">
            {{ auth.role?.toUpperCase() }} WORKSPACE
          </span>
        </div>
      </router-link>
    </div>

    <!-- Center: Scoped Full-Text Search Bar -->
    <div class="hidden md:block relative max-w-md w-full mx-4" ref="searchContainerRef">
      <div class="relative">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
        <input
          type="text"
          v-model="searchQuery"
          @input="handleSearch"
          @focus="showSearchResults = true"
          placeholder="Search doctors, clinical records, prescriptions..."
          class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 dark:bg-slate-750 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500 font-sans"
        />
        <button
          v-if="searchQuery"
          @click="clearSearch"
          class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold"
        >
          ✕
        </button>
      </div>

      <!-- Live Search Results Dropdown -->
      <div
        v-if="showSearchResults && searchQuery && (searchResults.doctors.length || searchResults.records.length || searchResults.prescriptions.length)"
        class="absolute left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-600 rounded-xl shadow-2xl overflow-hidden z-50 max-h-96 overflow-y-auto text-xs animate-in fade-in zoom-in-95 duration-150"
      >
        <!-- Doctors Matches -->
        <div v-if="searchResults.doctors.length" class="p-2 border-b border-slate-100 dark:border-slate-700">
          <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 px-2 py-1">
            Doctors & Specialists ({{ searchResults.doctors.length }})
          </div>
          <router-link
            v-for="doc in searchResults.doctors"
            :key="doc.id"
            :to="`/patient/doctors`"
            @click="showSearchResults = false"
            class="flex items-center space-x-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 transition"
          >
            <div class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-[10px]">
              Dr
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-bold text-slate-900 dark:text-white truncate">{{ doc.name }}</p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ doc.specialty }} • ₱{{ (doc.consultation_fee_cents ? doc.consultation_fee_cents / 100 : (doc.consultation_fee || 120)).toFixed(2) }}</p>
            </div>
          </router-link>
        </div>

        <!-- Medical Records Matches -->
        <div v-if="searchResults.records.length" class="p-2 border-b border-slate-100 dark:border-slate-700">
          <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 px-2 py-1">
            Your Medical Records ({{ searchResults.records.length }})
          </div>
          <router-link
            v-for="rec in searchResults.records"
            :key="rec.id"
            :to="`/patient/records`"
            @click="showSearchResults = false"
            class="flex items-center space-x-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 transition"
          >
            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px]">
              EHR
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-bold text-slate-900 dark:text-white truncate">{{ rec.diagnosis }}</p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ rec.record_date || 'Encounter Note' }} • {{ rec.doctor_name || 'Clinician' }}</p>
            </div>
          </router-link>
        </div>

        <!-- Prescriptions Matches -->
        <div v-if="searchResults.prescriptions.length" class="p-2">
          <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 px-2 py-1">
            Your Prescriptions ({{ searchResults.prescriptions.length }})
          </div>
          <router-link
            v-for="rx in searchResults.prescriptions"
            :key="rx.id"
            :to="`/patient/prescriptions`"
            @click="showSearchResults = false"
            class="flex items-center space-x-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/60 transition"
          >
            <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-[10px]">
              Rx
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-bold text-slate-900 dark:text-white truncate">{{ rx.items?.[0]?.medication_name || rx.notes || 'Medication Order' }}</p>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ rx.doctor_name || 'Prescribing Doctor' }}</p>
            </div>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Right: Role indicator & User Menu -->
    <div class="flex items-center space-x-3">
      <router-link
        to="/"
        class="hidden sm:inline-flex text-[11px] font-mono font-bold uppercase text-slate-600 hover:text-brand-600 px-2 py-1 border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-200 transition-colors"
      >
        Public Portal
      </router-link>

      <div v-if="auth.isAuthenticated" class="hidden sm:flex items-center space-x-1.5">
        <Badge :variant="auth.role">{{ auth.role }}</Badge>
      </div>

      <!-- User Dropdown Menu -->
      <div class="relative" ref="dropdownRef">
        <button
          @click="isMenuOpen = !isMenuOpen"
          class="flex items-center space-x-2 p-1 border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 transition-colors focus:outline-none"
        >
          <img
            :src="auth.user?.avatar_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'"
            alt="Avatar"
            class="w-6 h-6 object-cover border border-slate-300"
          />
          <span class="hidden md:block text-xs font-mono font-bold text-slate-800 dark:text-slate-200 max-w-[120px] truncate">
            {{ auth.user?.name || 'User' }}
          </span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-500" />
        </button>

        <!-- Dropdown Popover -->
        <div
          v-if="isMenuOpen"
          class="absolute right-0 mt-1 w-56 bg-white dark:bg-slate-800 border-2 border-slate-700 dark:border-slate-600 shadow-xl py-1 z-50 text-xs"
        >
          <div class="px-3 py-2 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-750">
            <p class="font-bold text-slate-900 dark:text-white truncate uppercase">{{ auth.user?.name }}</p>
            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 truncate">{{ auth.user?.email }}</p>
          </div>

          <router-link
            to="/profile"
            @click="isMenuOpen = false"
            class="flex items-center px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-brand-600 transition-colors font-mono font-bold uppercase"
          >
            <User class="w-3.5 h-3.5 mr-2 text-slate-500" />
            Account & 2FA Settings
          </router-link>

          <button
            @click="handleLogout"
            class="w-full flex items-center px-3 py-2 text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors text-left font-mono font-bold uppercase border-t border-slate-100 dark:border-slate-700"
          >
            <LogOut class="w-3.5 h-3.5 mr-2 text-rose-600" />
            Sign Out
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { Menu, ChevronDown, User, LogOut, Search } from 'lucide-vue-next'
import Badge from './Badge.vue'
import api from '@/services/api'

defineEmits(['toggle-sidebar'])

const auth = useAuthStore()
const isMenuOpen = ref(false)

const searchQuery = ref('')
const showSearchResults = ref(false)
const searchResults = ref({
  doctors: [],
  records: [],
  prescriptions: [],
})

let searchTimeout = null

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  if (!searchQuery.value.trim()) {
    searchResults.value = { doctors: [], records: [], prescriptions: [] }
    return
  }

  searchTimeout = setTimeout(async () => {
    try {
      const res = await api.get('/search', {
        params: { q: searchQuery.value },
      })
      if (res.data?.success) {
        searchResults.value = res.data.data
        showSearchResults.value = true
      }
    } catch (err) {
      // fallback
    }
  }, 200)
}

const clearSearch = () => {
  searchQuery.value = ''
  showSearchResults.value = false
  searchResults.value = { doctors: [], records: [], prescriptions: [] }
}

const handleLogout = () => {
  isMenuOpen.value = false
  auth.logout()
}
</script>
