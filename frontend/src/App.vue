<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 font-sans">
    <router-view />
    <!-- Global Notifications -->
    <ToastContainer />
    <!-- Global Clinical AI Assistant (Accessible to Guests & Authenticated Users) -->
    <ClinicalAssistantModal />
  </div>
</template>

<script setup>
import ToastContainer from '@/components/common/ToastContainer.vue'
import ClinicalAssistantModal from '@/components/common/ClinicalAssistantModal.vue'
</script>
