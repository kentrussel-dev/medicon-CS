<template>
  <div class="bg-white border border-slate-300 p-5 shadow-crisp relative">
    <div class="absolute top-0 left-0 right-0 h-1" :class="accentClass"></div>
    <div class="flex items-start justify-between">
      <div>
        <p class="text-[11px] font-mono font-bold uppercase tracking-wider text-slate-500">{{ title }}</p>
        <h4 class="text-2xl font-bold text-slate-900 font-mono mt-1.5">{{ value }}</h4>
        <p v-if="subtitle" class="text-xs text-slate-600 mt-1">{{ subtitle }}</p>
      </div>
      <div v-if="icon" class="p-2 border border-slate-200 bg-slate-50 text-slate-700">
        <component :is="icon" class="w-5 h-5" />
      </div>
    </div>
    <div v-if="trend" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center text-xs">
      <span
        :class="trendPositive ? 'text-emerald-800 bg-emerald-50 border border-emerald-300' : 'text-rose-800 bg-rose-50 border border-rose-300'"
        class="font-mono font-bold px-1 py-0.2 mr-1.5"
      >
        {{ trend }}
      </span>
      <span class="text-slate-500">vs last reporting period</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  value: [String, Number],
  subtitle: String,
  icon: Object,
  color: {
    type: String,
    default: 'blue',
  },
  trend: String,
  trendPositive: {
    type: Boolean,
    default: true,
  },
})

const accentClass = computed(() => {
  switch (props.color) {
    case 'emerald':
      return 'bg-emerald-600'
    case 'amber':
      return 'bg-amber-600'
    case 'rose':
      return 'bg-rose-600'
    case 'purple':
      return 'bg-slate-800'
    default:
      return 'bg-blue-700'
  }
})
</script>
