<template>
  <span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide capitalize"
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
  const v = props.variant.toLowerCase()
  switch (v) {
    case 'confirmed':
    case 'success':
    case 'active':
    case 'completed':
      return 'bg-emerald-100 text-emerald-800 border border-emerald-200'
    case 'pending':
    case 'warning':
    case 'medium':
      return 'bg-amber-100 text-amber-800 border border-amber-200'
    case 'cancelled':
    case 'danger':
    case 'error':
    case 'no_show':
    case 'inactive':
    case 'high':
      return 'bg-rose-100 text-rose-800 border border-rose-200'
    case 'in_progress':
    case 'telehealth':
    case 'info':
    case 'low':
      return 'bg-sky-100 text-sky-800 border border-sky-200'
    case 'doctor':
      return 'bg-indigo-100 text-indigo-800 border border-indigo-200'
    case 'admin':
      return 'bg-purple-100 text-purple-800 border border-purple-200'
    default:
      return 'bg-slate-100 text-slate-700 border border-slate-200'
  }
})
</script>
