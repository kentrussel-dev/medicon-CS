<template>
  <div class="flex flex-col items-center justify-center p-8 text-center space-y-3">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600"></div>
    <p v-if="text" class="text-xs font-medium text-slate-500">{{ text }}</p>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    default: 'Loading...',
  },
})
</script>
