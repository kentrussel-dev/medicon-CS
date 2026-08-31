<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Abstract Background Blur Rings -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-500 to-emerald-400 text-white shadow-xl shadow-brand-500/25 mb-4">
        <HeartPulse class="w-8 h-8" />
      </div>
      <h2 class="text-3xl font-black text-white tracking-tight">Medicon Telehealth</h2>
      <p class="mt-1 text-sm text-slate-400">Enterprise Clinical Care & Telemedicine Platform</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white/95 backdrop-blur-xl py-8 px-6 sm:px-10 shadow-2xl rounded-3xl border border-white/20">
        <router-view />
      </div>

      <!-- Compliance & HIPAA Badge Notice -->
      <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center space-x-1.5">
        <ShieldCheck class="w-4 h-4 text-emerald-400" />
        <span>HIPAA Compliant &bull; End-to-End Encrypted Clinical Records</span>
      </div>
    </div>

    <!-- Global Toast Container -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { HeartPulse, ShieldCheck } from 'lucide-vue-next'
import ToastContainer from '@/components/common/ToastContainer.vue'
</script>
