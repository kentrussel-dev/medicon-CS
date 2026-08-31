<template>
  <div class="min-h-screen bg-gradient-to-br from-[#f0f6ff] via-[#f8fbff] to-[#fcfdff] flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
    <!-- Concentric Soft Blue Decorative Arch Rings -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full border-[32px] border-blue-200/30 pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full border-[32px] border-sky-300/20 pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center">
      <router-link to="/" class="inline-flex items-center space-x-1 text-3xl font-black tracking-tight text-slate-900 mb-2">
        <span>Doc</span><span class="text-brand-600">.</span><span class="text-brand-600">Wise</span>
        <span class="ml-2 text-[11px] uppercase font-black px-2.5 py-0.5 bg-brand-50 text-brand-700 rounded-full border border-brand-200">
          Medicon
        </span>
      </router-link>
      <p class="text-xs text-slate-500 font-medium">Enterprise Telehealth & Encrypted Clinical Records</p>
    </div>

    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
      <div class="bg-white py-8 px-6 sm:px-10 shadow-hero-card rounded-[32px] border border-slate-100/90">
        <router-view />
      </div>

      <!-- Compliance & HIPAA Badge Notice -->
      <div class="mt-6 text-center text-xs text-slate-500 flex items-center justify-center space-x-1.5 font-semibold">
        <ShieldCheck class="w-4 h-4 text-brand-600" />
        <span>100% HIPAA Compliant &bull; AES-256 Encrypted EHR</span>
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
