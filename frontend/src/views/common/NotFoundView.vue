<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center text-center p-8">
    <div class="p-4 rounded-3xl bg-slate-100 text-slate-500 mb-4">
      <FileQuestion class="w-12 h-12" />
    </div>
    <h2 class="text-3xl font-black text-slate-900">404 - Page Not Found</h2>
    <p class="text-sm text-slate-500 mt-2 max-w-md">
      The clinical resource, appointment page, or record you requested does not exist or has been relocated.
    </p>
    <router-link
      to="/"
      class="mt-6 px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition-all"
    >
      Return to Home
    </router-link>
  </div>
</template>

<script setup>
import { FileQuestion } from 'lucide-vue-next'
</script>
