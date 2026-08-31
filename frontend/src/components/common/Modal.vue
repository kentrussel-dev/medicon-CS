<template>
  <teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4 sm:p-6">
      <div
        class="relative w-full bg-white border-2 border-slate-700 shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
        :class="sizeClasses"
        @click.stop
      >
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-3.5 border-b-2 border-slate-200 bg-slate-100 text-slate-900">
          <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 leading-snug">{{ title }}</h3>
            <p v-if="subtitle" class="text-xs text-slate-600 mt-0.5">{{ subtitle }}</p>
          </div>
          <button
            @click="close"
            class="p-1 border border-slate-300 bg-white text-slate-700 hover:bg-slate-200 hover:text-slate-900 transition-colors focus:outline-none"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 overflow-y-auto flex-1 bg-white text-slate-900">
          <slot></slot>
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="px-6 py-3.5 bg-slate-100 border-t border-slate-300 flex items-center justify-end space-x-3">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
import { X } from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg, xl, full
  },
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'max-w-md'
    case 'lg':
      return 'max-w-3xl'
    case 'xl':
      return 'max-w-5xl'
    case 'full':
      return 'max-w-6xl'
    default:
      return 'max-w-xl'
  }
})
</script>
