<template>
  <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="riskClasses">
    <component :is="riskIcon" class="w-3.5 h-3.5 flex-shrink-0" />
    <span>{{ label }}</span>
    <span v-if="score !== null && score !== undefined" class="opacity-80 font-mono text-[10px]">
      ({{ Math.round(score * 100) }}%)
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { AlertTriangle, ShieldCheck, AlertCircle } from 'lucide-vue-next'

const props = defineProps({
  level: {
    type: String,
    default: 'LOW',
  },
  score: {
    type: Number,
    default: null,
  },
})

const normalizedLevel = computed(() => (props.level || 'LOW').toUpperCase())

const label = computed(() => {
  switch (normalizedLevel.value) {
    case 'HIGH':
      return 'High Risk'
    case 'MEDIUM':
      return 'Medium Risk'
    default:
      return 'Low Risk'
  }
})

const riskIcon = computed(() => {
  switch (normalizedLevel.value) {
    case 'HIGH':
      return AlertCircle
    case 'MEDIUM':
      return AlertTriangle
    default:
      return ShieldCheck
  }
})

const riskClasses = computed(() => {
  switch (normalizedLevel.value) {
    case 'HIGH':
      return 'bg-rose-50 text-rose-700 border border-rose-200 shadow-sm shadow-rose-100'
    case 'MEDIUM':
      return 'bg-amber-50 text-amber-700 border border-amber-200'
    default:
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
  }
})
</script>
