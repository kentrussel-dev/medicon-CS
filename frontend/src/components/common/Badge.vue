<template>
  <span
    class="inline-flex items-center px-2 py-0.5 text-[11px] font-mono font-bold tracking-wider uppercase border"
    :class="colorClasses"
  >
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
  },
  text: {
    type: String,
    default: '',
  },
})

const colorClasses = computed(() => {
  const v = (props.variant || '').toLowerCase()
  switch (v) {
    case 'confirmed':
    case 'success':
    case 'active':
    case 'completed':
      return 'bg-emerald-50 text-emerald-800 border-emerald-400'
    case 'pending':
    case 'warning':
    case 'medium':
      return 'bg-amber-50 text-amber-900 border-amber-400'
    case 'cancelled':
    case 'danger':
    case 'error':
    case 'no_show':
    case 'inactive':
    case 'high':
      return 'bg-rose-50 text-rose-800 border-rose-400'
    case 'in_progress':
    case 'telehealth':
    case 'info':
    case 'low':
      return 'bg-blue-50 text-blue-800 border-blue-400'
    case 'doctor':
      return 'bg-slate-100 text-slate-800 border-slate-400'
    case 'admin':
      return 'bg-slate-900 text-white border-slate-900'
    default:
      return 'bg-slate-50 text-slate-700 border-slate-300'
  }
})
</script>
