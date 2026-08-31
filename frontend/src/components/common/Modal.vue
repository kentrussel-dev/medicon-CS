<template>
  <teleport to="body">
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">
        <div
          class="relative w-full bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all flex flex-col max-h-[90vh]"
          :class="sizeClasses"
          @click.stop
        >
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <div>
              <h3 class="text-lg font-bold text-slate-900 leading-6">{{ title }}</h3>
              <p v-if="subtitle" class="text-xs text-slate-500 mt-0.5">{{ subtitle }}</p>
            </div>
            <button
              @click="close"
              class="rounded-xl p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Body -->
          <div class="px-6 py-5 overflow-y-auto flex-1">
            <slot></slot>
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-3">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </transition>
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
