<template>
  <div class="min-h-screen bg-slate-100 flex flex-col font-sans">
    <!-- Top System Strip -->
    <div class="bg-slate-900 text-slate-300 text-[11px] font-mono px-4 sm:px-6 py-1 border-b border-slate-800 flex items-center justify-between z-40">
      <div class="flex items-center space-x-2">
        <span class="inline-block w-1.5 h-1.5 bg-emerald-500"></span>
        <span class="font-bold uppercase tracking-wider text-slate-200">Medicon Clinical Healthcare Platform</span>
        <span class="text-slate-600">|</span>
        <span class="text-slate-400">HIPAA &bull; AES-256 Encrypted</span>
      </div>
      <div class="flex items-center space-x-3">
        <span class="text-slate-400 text-[10px] uppercase font-bold">Role: <span class="text-white">{{ auth.role }}</span></span>
      </div>
    </div>

    <div class="flex-1 flex min-h-0">
      <!-- Sidebar Component -->
      <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

      <!-- Main Workspace Container -->
      <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
        <!-- Navbar -->
        <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto">
          <router-view />
        </main>
      </div>
    </div>

    <!-- Global Toast Container -->
    <ToastContainer />

    <!-- Clinical AI Assistant Floating Widget -->
    <ClinicalAssistantModal />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/common/Navbar.vue'
import Sidebar from '@/components/common/Sidebar.vue'
import ToastContainer from '@/components/common/ToastContainer.vue'
import ClinicalAssistantModal from '@/components/common/ClinicalAssistantModal.vue'

const auth = useAuthStore()
const sidebarOpen = ref(false)
</script>
