<template>
  <div>
    <!-- Mobile Overlay -->
    <div
      v-if="isOpen"
      @click="$emit('close')"
      class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs lg:hidden"
    ></div>

    <!-- Sidebar Container -->
    <aside
      class="fixed top-0 bottom-0 left-0 z-40 w-64 bg-white border-r border-slate-100/90 transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col"
      :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo Section -->
      <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100">
        <router-link to="/" class="flex items-center space-x-1 text-xl font-black tracking-tight text-slate-900">
          <span>Doc</span><span class="text-brand-600">.</span><span class="text-brand-600">Wise</span>
          <span class="ml-1.5 text-[9px] uppercase font-black px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full border border-brand-200">
            Medicon
          </span>
        </router-link>
        <button
          @click="$emit('close')"
          class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Section -->
      <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
        <div class="px-3 pb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
          {{ roleSectionTitle }}
        </div>

        <router-link
          v-for="item in navItems"
          :key="item.name"
          :to="item.to"
          @click="$emit('close')"
          class="flex items-center px-3.5 py-2.5 rounded-2xl text-xs font-bold transition-all group"
          :class="
            $route.path === item.to
              ? 'bg-brand-50 text-brand-600 font-extrabold shadow-xs'
              : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
          "
        >
          <component
            :is="item.icon"
            class="w-4 h-4 mr-3 transition-colors"
            :class="$route.path === item.to ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600'"
          />
          {{ item.label }}
          <span
            v-if="item.badge"
            class="ml-auto px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-100 text-rose-700"
          >
            {{ item.badge }}
          </span>
        </router-link>

        <div class="pt-4 border-t border-slate-100 mt-4">
          <router-link
            to="/"
            @click="$emit('close')"
            class="flex items-center px-3.5 py-2.5 rounded-2xl text-xs font-bold text-slate-500 hover:bg-slate-50 hover:text-brand-600 transition-all"
          >
            <Home class="w-4 h-4 mr-3 text-slate-400" />
            Public Homepage
          </router-link>
        </div>
      </nav>

      <!-- User Profile Badge Footer -->
      <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        <div class="flex items-center space-x-3">
          <img
            :src="auth.user?.avatar_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'"
            alt="User"
            class="w-9 h-9 rounded-full object-cover border border-slate-200"
          />
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-slate-900 truncate">{{ auth.user?.name }}</p>
            <p class="text-[11px] text-slate-500 capitalize">{{ auth.role }} Portal</p>
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
  if (auth.isAdmin) return 'Administrative Control'
  if (auth.isDoctor) return 'Clinical Workspace'
  return 'Patient Portal'
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
    { label: 'My Health Dashboard', to: '/patient/dashboard', icon: LayoutDashboard },
    { label: 'Find a Specialist', to: '/patient/doctors', icon: Activity },
    { label: 'My Appointments', to: '/patient/appointments', icon: Calendar },
    { label: 'Clinical Records', to: '/patient/records', icon: FileText },
    { label: 'Prescriptions', to: '/patient/prescriptions', icon: Pill },
  ]
})
</script>
