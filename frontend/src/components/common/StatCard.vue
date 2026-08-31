<template>
  <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ title }}</p>
        <h4 class="text-2xl font-extrabold text-slate-900 mt-1">{{ value }}</h4>
        <p v-if="subtitle" class="text-xs text-slate-500 mt-1">{{ subtitle }}</p>
      </div>
      <div v-if="icon" class="p-3 rounded-2xl" :class="iconBgClass">
        <component :is="icon" class="w-6 h-6" :class="iconColorClass" />
      </div>
    </div>
    <div v-if="trend" class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs">
      <span
        :class="trendPositive ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'"
        class="font-bold px-1.5 py-0.5 rounded mr-1.5"
      >
        {{ trend }}
      </span>
      <span class="text-slate-400">vs last month</span>
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
    default: 'emerald', // emerald, blue, purple, amber, rose
  },
  trend: String,
  trendPositive: {
    type: Boolean,
    default: true,
  },
})

const iconBgClass = computed(() => {
  switch (props.color) {
    case 'blue':
      return 'bg-sky-50'
    case 'purple':
      return 'bg-purple-50'
    case 'amber':
      return 'bg-amber-50'
    case 'rose':
      return 'bg-rose-50'
    default:
      return 'bg-emerald-50'
  }
})

const iconColorClass = computed(() => {
  switch (props.color) {
    case 'blue':
      return 'text-sky-600'
    case 'purple':
      return 'text-purple-600'
    case 'amber':
      return 'text-amber-600'
    case 'rose':
      return 'text-rose-600'
    default:
      return 'text-emerald-600'
  }
})
</script>
