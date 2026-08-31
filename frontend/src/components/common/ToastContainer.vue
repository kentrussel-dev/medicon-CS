<template>
  <div class="fixed bottom-5 right-5 z-50 flex flex-col space-y-3 max-w-sm w-full pointer-events-none">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start p-4 rounded-xl shadow-lg border backdrop-blur-sm text-sm"
        :class="toastClasses(toast.type)"
      >
        <div class="flex-shrink-0 mr-3 mt-0.5">
          <component :is="getIcon(toast.type)" class="w-5 h-5" />
        </div>
        <div class="flex-1 font-medium leading-5">
          {{ toast.message }}
        </div>
        <button
          @click="removeToast(toast.id)"
          class="ml-3 flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useNotificationStore } from '@/stores/notifications'
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next'

const store = useNotificationStore()
const toasts = computed(() => store.toasts)
const removeToast = store.removeToast

const toastClasses = (type) => {
  switch (type) {
    case 'success':
      return 'bg-emerald-50/95 border-emerald-200 text-emerald-900'
    case 'error':
      return 'bg-rose-50/95 border-rose-200 text-rose-900'
    case 'warning':
      return 'bg-amber-50/95 border-amber-200 text-amber-900'
    default:
      return 'bg-slate-900/95 border-slate-700 text-white'
  }
}

const getIcon = (type) => {
  switch (type) {
    case 'success':
      return CheckCircle2
    case 'error':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    default:
      return Info
  }
}
</script>
