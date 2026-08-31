<template>
  <div class="min-h-[60vh] flex flex-col items-center justify-center text-center p-8">
    <div class="p-4 rounded-3xl bg-rose-50 text-rose-600 mb-4">
      <ShieldAlert class="w-12 h-12" />
    </div>
    <h2 class="text-2xl font-black text-slate-900">403 - Access Forbidden</h2>
    <p class="text-sm text-slate-500 mt-2 max-w-md">
      You do not have the required role permissions to access this clinical or administrative area.
    </p>
    <router-link
      to="/"
      class="mt-6 px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-all"
    >
      Return to My Dashboard
    </router-link>
  </div>
</template>

<script setup>
import { ShieldAlert } from 'lucide-vue-next'
</script>
