<template>
  <div class="min-h-screen bg-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
    <!-- Top System Strip -->
    <div class="fixed top-0 left-0 right-0 bg-slate-900 text-slate-300 text-[11px] font-mono px-4 sm:px-8 py-1.5 border-b border-slate-800 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <span class="inline-block w-2 h-2 bg-emerald-500"></span>
        <span class="font-bold uppercase tracking-wider text-slate-100">Official Clinical Healthcare Platform</span>
        <span class="hidden md:inline text-slate-500">|</span>
        <span class="hidden md:inline text-slate-400">HIPAA Certified &bull; Encrypted Record System</span>
      </div>
      <router-link to="/" class="underline text-slate-400 hover:text-white">Public Portal</router-link>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mt-6">
      <router-link to="/" class="inline-flex items-center space-x-2 text-2xl font-black tracking-tight text-slate-950 uppercase">
        <div class="w-8 h-8 bg-brand-600 text-white flex items-center justify-center font-bold text-base border border-brand-700">
          M
        </div>
        <span>Medicon Systems</span>
      </router-link>
      <p class="text-xs text-slate-600 font-mono uppercase tracking-wider mt-1">Authorized Personnel & Patient Sign-In</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
      <div class="bg-white py-8 px-6 sm:px-8 border-2 border-slate-300 shadow-sm">
        <router-view />
      </div>

      <!-- Compliance & HIPAA Badge Notice -->
      <div class="mt-4 text-center text-xs text-slate-600 font-mono flex items-center justify-center space-x-1.5">
        <ShieldCheck class="w-4 h-4 text-emerald-700" />
        <span>HIPAA COMPLIANT &bull; 256-BIT ENCRYPTION AT REST</span>
      </div>
    </div>

    <!-- Global Toast Container -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ShieldCheck } from 'lucide-vue-next'
import ToastContainer from '@/components/common/ToastContainer.vue'
</script>
