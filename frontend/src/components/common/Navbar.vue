<template>
  <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-100/90 px-4 sm:px-8 py-3.5 flex items-center justify-between">
    <!-- Left: Brand / Sidebar toggle -->
    <div class="flex items-center space-x-3">
      <button
        @click="$emit('toggle-sidebar')"
        class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors"
      >
        <Menu class="w-5 h-5" />
      </button>
      <router-link to="/" class="flex items-center space-x-1 text-xl font-black tracking-tight text-slate-900">
        <span>Doc</span><span class="text-brand-600">.</span><span class="text-brand-600">Wise</span>
        <span class="ml-1.5 text-[10px] uppercase font-black px-2 py-0.5 bg-brand-50 text-brand-700 rounded-full border border-brand-200">
          Medicon
        </span>
      </router-link>
    </div>

    <!-- Right: Role indicator & User Menu -->
    <div class="flex items-center space-x-4">
      <router-link
        to="/"
        class="hidden sm:inline-flex text-xs font-bold text-slate-500 hover:text-brand-600 transition-colors"
      >
        Public Homepage
      </router-link>

      <div v-if="auth.isAuthenticated" class="hidden sm:flex items-center space-x-2">
        <Badge :variant="auth.role">{{ auth.role }}</Badge>
      </div>

      <!-- User Dropdown Menu -->
      <div class="relative" ref="dropdownRef">
        <button
          @click="isMenuOpen = !isMenuOpen"
          class="flex items-center space-x-2.5 p-1.5 rounded-full hover:bg-slate-100 transition-colors focus:outline-none"
        >
          <img
            :src="auth.user?.avatar_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'"
            alt="Avatar"
            class="w-8 h-8 rounded-full object-cover border border-slate-200"
          />
          <span class="hidden md:block text-xs font-bold text-slate-800 max-w-[120px] truncate">
            {{ auth.user?.name || 'User' }}
          </span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
        </button>

        <!-- Dropdown Popover -->
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <div
            v-if="isMenuOpen"
            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 text-sm"
          >
            <div class="px-4 py-2.5 border-b border-slate-100">
              <p class="font-bold text-slate-900 truncate">{{ auth.user?.name }}</p>
              <p class="text-xs text-slate-400 truncate">{{ auth.user?.email }}</p>
            </div>

            <router-link
              to="/profile"
              @click="isMenuOpen = false"
              class="flex items-center px-4 py-2 text-slate-700 hover:bg-slate-50 hover:text-brand-600 transition-colors font-medium text-xs"
            >
              <User class="w-4 h-4 mr-2.5 text-slate-400" />
              Account Settings
            </router-link>

            <button
              @click="handleLogout"
              class="w-full flex items-center px-4 py-2 text-rose-600 hover:bg-rose-50 transition-colors text-left font-medium text-xs"
            >
              <LogOut class="w-4 h-4 mr-2.5 text-rose-400" />
              Sign Out
            </button>
          </div>
        </transition>
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
