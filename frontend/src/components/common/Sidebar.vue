<template>
  <div>
    <!-- Mobile Overlay -->
    <div
      v-if="isOpen"
      @click="$emit('close')"
      class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"
    ></div>

    <!-- Sidebar Container -->
    <aside
      class="fixed top-0 bottom-0 left-0 z-40 w-64 bg-slate-900 text-white border-r border-slate-800 transform transition-transform duration-200 ease-in-out lg:translate-x-0 flex flex-col"
      :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo Section -->
      <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 bg-slate-950">
        <router-link to="/" class="flex items-center space-x-2.5">
          <div class="w-7 h-7 bg-brand-600 text-white flex items-center justify-center font-bold text-xs border border-brand-500">
            M
          </div>
          <div>
            <span class="font-black text-xs uppercase tracking-wider text-white block">Medicon</span>
            <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest block">Clinical EHR</span>
          </div>
        </router-link>
        <button
          @click="$emit('close')"
          class="lg:hidden p-1 border border-slate-700 bg-slate-800 text-slate-400 hover:text-white"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Navigation Section -->
      <nav class="flex-1 overflow-y-auto p-3 space-y-1">
        <div class="px-2.5 py-1.5 text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400">
          {{ roleSectionTitle }}
        </div>

        <router-link
          v-for="item in navItems"
          :key="item.name"
          :to="item.to"
          @click="$emit('close')"
          class="flex items-center px-3 py-2 text-xs font-bold uppercase tracking-wider transition-colors border-l-2"
          :class="
            $route.path === item.to
              ? 'bg-slate-800 text-white border-brand-500 font-bold'
              : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 border-transparent'
          "
        >
          <component
            :is="item.icon"
            class="w-4 h-4 mr-2.5"
            :class="$route.path === item.to ? 'text-brand-400' : 'text-slate-500'"
          />
          {{ item.label }}
          <span
            v-if="item.badge"
            class="ml-auto px-1.5 py-0.2 text-[9px] font-mono font-bold bg-rose-900 text-rose-300 border border-rose-700"
          >
            {{ item.badge }}
          </span>
        </router-link>

        <div class="pt-4 border-t border-slate-800 mt-4">
          <router-link
            to="/"
            @click="$emit('close')"
            class="flex items-center px-3 py-2 text-xs font-mono font-bold text-slate-400 hover:bg-slate-800 hover:text-white uppercase transition-colors"
          >
            <Home class="w-4 h-4 mr-2.5 text-slate-500" />
            Public Gateway
          </router-link>
        </div>
      </nav>

      <!-- User Profile Badge Footer -->
      <div class="p-3.5 border-t border-slate-800 bg-slate-950">
        <div class="flex items-center space-x-2.5">
          <img
            :src="auth.user?.avatar_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'"
            alt="User"
            class="w-7 h-7 object-cover border border-slate-700"
          />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-white truncate uppercase">{{ auth.user?.name }}</p>
            <p class="text-[10px] font-mono text-slate-400 uppercase">{{ auth.role }} Workspace</p>
          </div>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  X,
  Home,
  LayoutDashboard,
  Users,
  Calendar,
  FileText,
  Pill,
  Clock,
  History,
  Activity,
} from 'lucide-vue-next'

defineProps({
  isOpen: Boolean,
})
defineEmits(['close'])

const auth = useAuthStore()

const roleSectionTitle = computed(() => {
  if (auth.isAdmin) return 'Administrative Modules'
  if (auth.isDoctor) return 'Clinical Modules'
  return 'Patient Modules'
})

const navItems = computed(() => {
  if (auth.isAdmin) {
    return [
      { label: 'Overview Analytics', to: '/admin/dashboard', icon: LayoutDashboard },
      { label: 'All Appointments', to: '/admin/appointments', icon: Calendar },
      { label: 'User Directory', to: '/admin/users', icon: Users },
      { label: 'HIPAA Audit Trail', to: '/admin/audit-logs', icon: History },
    ]
  }

  if (auth.isDoctor) {
    return [
      { label: 'Doctor Dashboard', to: '/doctor/dashboard', icon: LayoutDashboard },
      { label: 'Appointment Schedule', to: '/doctor/appointments', icon: Calendar },
      { label: 'Working Availability', to: '/doctor/schedule', icon: Clock },
      { label: 'Patient Directory', to: '/doctor/patients', icon: Users },
    ]
  }

  // Patient items
  return [
    { label: 'Health Dashboard', to: '/patient/dashboard', icon: LayoutDashboard },
    { label: 'Find a Specialist', to: '/patient/doctors', icon: Activity },
    { label: 'My Appointments', to: '/patient/appointments', icon: Calendar },
    { label: 'Clinical Records', to: '/patient/records', icon: FileText },
    { label: 'Prescriptions', to: '/patient/prescriptions', icon: Pill },
  ]
})
</script>
