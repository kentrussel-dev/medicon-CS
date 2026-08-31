<template>
  <div class="inline-flex items-center space-x-1 px-2 py-0.5 text-[11px] font-mono font-bold tracking-wider uppercase border" :class="riskClasses">
    <component :is="riskIcon" class="w-3 h-3 flex-shrink-0" />
    <span>{{ label }}</span>
    <span v-if="score !== null && score !== undefined" class="opacity-90">
      [{{ Math.round(score * 100) }}%]
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
      return 'HIGH RISK'
    case 'MEDIUM':
      return 'MEDIUM RISK'
    default:
      return 'LOW RISK'
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
      return 'bg-rose-50 text-rose-800 border-rose-400'
    case 'MEDIUM':
      return 'bg-amber-50 text-amber-900 border-amber-400'
    default:
      return 'bg-emerald-50 text-emerald-800 border-emerald-400'
  }
})
</script>
