<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar Component -->
    <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

    <!-- Main Workspace Container -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
      <!-- Navbar -->
      <Navbar @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Page Content -->
      <main class="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
        <router-view v-slot="{ Component }">
          <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
            mode="out-in"
          >
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>

    <!-- Global Toast Container -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Navbar from '@/components/common/Navbar.vue'
import Sidebar from '@/components/common/Sidebar.vue'
import ToastContainer from '@/components/common/ToastContainer.vue'

const sidebarOpen = ref(false)
</script>
