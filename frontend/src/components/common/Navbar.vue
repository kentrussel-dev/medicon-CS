<template>
  <header class="bg-white border-b-2 border-slate-300 px-4 sm:px-6 py-2.5 flex items-center justify-between sticky top-0 z-30">
    <!-- Left: Brand / Sidebar toggle -->
    <div class="flex items-center space-x-3">
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden p-1.5 border border-slate-300 bg-slate-50 text-slate-700 hover:bg-slate-200 transition-colors"
      >
        <Menu class="w-4 h-4" />
      </button>
      <router-link to="/" class="flex items-center space-x-2">
        <div class="w-7 h-7 bg-brand-600 text-white flex items-center justify-center font-bold text-xs border border-brand-700">
          M
        </div>
        <div class="flex flex-col">
          <span class="font-black text-xs uppercase tracking-wider text-slate-900 leading-none">
            Medicon Clinical Systems
          </span>
          <span class="text-[9px] font-mono text-slate-500 uppercase tracking-widest leading-none mt-0.5">
            {{ auth.role?.toUpperCase() }} WORKSPACE
          </span>
        </div>
      </router-link>
    </div>

    <!-- Right: Role indicator & User Menu -->
    <div class="flex items-center space-x-3">
      <router-link
        to="/"
        class="hidden sm:inline-flex text-[11px] font-mono font-bold uppercase text-slate-600 hover:text-brand-600 px-2 py-1 border border-slate-300 bg-slate-50 transition-colors"
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
          class="flex items-center space-x-2 p-1 border border-slate-300 bg-slate-50 hover:bg-slate-100 transition-colors focus:outline-none"
        >
          <img
            :src="auth.user?.avatar_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'"
            alt="Avatar"
            class="w-6 h-6 object-cover border border-slate-300"
          />
          <span class="hidden md:block text-xs font-mono font-bold text-slate-800 max-w-[120px] truncate">
            {{ auth.user?.name || 'User' }}
          </span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-500" />
        </button>

        <!-- Dropdown Popover -->
        <div
          v-if="isMenuOpen"
          class="absolute right-0 mt-1 w-56 bg-white border-2 border-slate-700 shadow-xl py-1 z-50 text-xs"
        >
          <div class="px-3 py-2 border-b border-slate-200 bg-slate-50">
            <p class="font-bold text-slate-900 truncate uppercase">{{ auth.user?.name }}</p>
            <p class="text-[11px] font-mono text-slate-500 truncate">{{ auth.user?.email }}</p>
          </div>

          <router-link
            to="/profile"
            @click="isMenuOpen = false"
            class="flex items-center px-3 py-2 text-slate-700 hover:bg-slate-100 hover:text-brand-600 transition-colors font-mono font-bold uppercase"
          >
            <User class="w-3.5 h-3.5 mr-2 text-slate-500" />
            Account Settings
          </router-link>

          <button
            @click="handleLogout"
            class="w-full flex items-center px-3 py-2 text-rose-700 hover:bg-rose-50 transition-colors text-left font-mono font-bold uppercase border-t border-slate-100"
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
import { Menu, ChevronDown, User, LogOut } from 'lucide-vue-next'
import Badge from './Badge.vue'

defineEmits(['toggle-sidebar'])

const auth = useAuthStore()
const isMenuOpen = ref(false)

const handleLogout = () => {
  isMenuOpen.value = false
  auth.logout()
}
</script>
