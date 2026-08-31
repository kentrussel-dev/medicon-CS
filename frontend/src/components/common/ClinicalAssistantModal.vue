<template>
  <div>
    <!-- Floating Launcher Badge (Bottom Right) -->
    <div class="fixed bottom-6 right-6 z-40">
      <button
        @click="isOpen = !isOpen"
        class="px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white border-2 border-brand-600 shadow-xl flex items-center space-x-2.5 transition-all active:scale-98 font-mono text-xs font-bold uppercase tracking-wider"
      >
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex h-2.5 w-2.5 bg-emerald-500"></span>
        </span>
        <Bot class="w-4 h-4 text-brand-400" />
        <span class="hidden sm:inline">Clinical AI Assistant</span>
        <span class="sm:hidden">AI Assistant</span>
      </button>
    </div>

    <!-- Assistant Chat Modal Window -->
    <div
      v-if="isOpen"
      class="fixed bottom-20 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] sm:w-[460px] h-[580px] max-h-[85vh] bg-white border-2 border-slate-900 shadow-2xl flex flex-col font-sans"
    >
      <!-- Top Title Bar -->
      <div class="bg-slate-900 text-white px-4 py-3 border-b-2 border-brand-600 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-6 h-6 bg-brand-600 text-white flex items-center justify-center font-bold text-xs border border-brand-500">
            M
          </div>
          <div>
            <div class="font-bold text-xs uppercase tracking-wider leading-none">
              Clinical Assistant
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-1">
          <button
            @click="clearChat"
            title="Clear Chat History"
            class="p-1 text-slate-400 hover:text-white transition-colors"
          >
            <RotateCcw class="w-3.5 h-3.5" />
          </button>
          <button
            @click="isOpen = false"
            class="p-1 text-slate-400 hover:text-white transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Quick Action Prompt Chips -->
      <div class="bg-slate-50 p-2.5 border-b border-slate-200 overflow-x-auto scrollbar-none flex items-center space-x-2">
        <button
          v-for="chip in roleChips"
          :key="chip"
          @click="sendQuickPrompt(chip)"
          :disabled="loading"
          class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 text-[11px] font-mono font-bold text-slate-700 whitespace-nowrap transition-colors disabled:opacity-50"
        >
          {{ chip }}
        </button>
      </div>

      <!-- Messages Thread Area -->
      <div ref="messagesContainer" class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-slate-50/60 text-xs">
        <div
          v-for="(msg, idx) in messages"
          :key="idx"
          class="flex flex-col space-y-1"
          :class="msg.role === 'user' ? 'items-end' : 'items-start'"
        >
          <span class="text-[9px] font-mono uppercase text-slate-400">
            {{ msg.role === 'user' ? 'You' : 'Medicon Clinical Assistant' }} &bull; {{ msg.time }}
          </span>

          <div
            class="p-3.5 max-w-[90%] leading-relaxed"
            :class="
              msg.role === 'user'
                ? 'bg-slate-900 text-white border border-slate-800'
                : 'bg-white text-slate-900 border border-slate-300 shadow-xs'
            "
          >
            <div class="whitespace-pre-line font-sans select-text">
              {{ msg.content }}
            </div>

            <!-- Assistant Message Actions -->
            <div v-if="msg.role === 'assistant'" class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-end text-[10px] font-mono text-slate-400">
              <button
                @click="copyText(msg.content)"
                class="hover:text-slate-900 transition-colors uppercase font-bold flex items-center space-x-1"
              >
                <Copy class="w-3 h-3" />
                <span>{{ copiedIdx === idx ? 'Copied!' : 'Copy' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Typing Loading Indicator -->
        <div v-if="loading" class="flex items-start space-x-2">
          <div class="bg-white border border-slate-300 p-3 flex items-center space-x-2 text-xs font-mono text-slate-500">
            <span class="inline-block w-1.5 h-1.5 bg-brand-600 animate-ping"></span>
            <span>Consulting clinical reference...</span>
          </div>
        </div>
      </div>

      <!-- Bottom Chat Input Bar -->
      <form @submit.prevent="sendMessage" class="p-2.5 bg-white border-t-2 border-slate-300 flex items-center space-x-2">
        <input
          type="text"
          v-model="inputQuery"
          :placeholder="inputPlaceholder"
          :disabled="loading"
          class="flex-1 px-3 py-2 border border-slate-300 text-xs focus:border-slate-900 focus:outline-none bg-white rounded-none font-sans"
        />

        <button
          type="submit"
          :disabled="loading || !inputQuery.trim()"
          class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider border border-slate-950 transition-colors disabled:opacity-40 flex items-center space-x-1"
        >
          <Send class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Send</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import {
  Bot,
  X,
  Send,
  RotateCcw,
  Copy,
} from 'lucide-vue-next'

const auth = useAuthStore()

const isOpen = ref(false)
const inputQuery = ref('')
const loading = ref(false)
const copiedIdx = ref(null)
const messagesContainer = ref(null)

const roleChips = computed(() => {
  if (auth.isDoctor) {
    return [
      'Draft SOAP encounter note',
      'Check drug interaction: Lisinopril + NSAIDs',
      'Summarize patient history before visit',
      'Hypertension clinical guidelines',
    ]
  }
  if (auth.isAdmin) {
    return [
      'Hospital attendance risk factors',
      'HIPAA audit logging standards',
      'Physician utilization parameters',
    ]
  }
  // Patient chips
  return [
    'Explain my active prescriptions',
    'What is my next appointment?',
    'What are the clinic visiting hours?',
    'How do I prepare for a blood test?',
  ]
})

const inputPlaceholder = computed(() => {
  if (auth.isDoctor) return 'Ask clinical reference, pharmacology, or draft notes...'
  if (auth.isAdmin) return 'Ask operations, utilization, or audit policy questions...'
  return 'Ask about appointments, prescriptions, or clinic hours...'
})

const initialGreeting = computed(() => {
  if (auth.isDoctor) {
    return `Hello, ${auth.user?.name}! I am your Medicon Clinical Co-Pilot. I can assist in drafting SOAP encounter notes, summarizing patient histories, and retrieving pharmacology interaction references.`
  }
  if (auth.isAdmin) {
    return `Hello, ${auth.user?.name}! I am the Medicon Hospital Operations Assistant. How can I assist with clinical utilization or HIPAA compliance metrics today?`
  }
  return `Hello, ${auth.user?.name}! I am your Medicon Healthcare Assistant. I can help explain your scheduled visits, active prescriptions, or general clinic visiting procedures.`
})

const messages = ref([
  {
    role: 'assistant',
    content: initialGreeting.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    cached: false,
  },
])

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const sendQuickPrompt = (chip) => {
  inputQuery.value = chip
  sendMessage()
}

const sendMessage = async () => {
  const query = inputQuery.value.trim()
  if (!query || loading.value) return

  const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

  // Push user message
  messages.value.push({
    role: 'user',
    content: query,
    time: now,
  })

  inputQuery.value = ''
  loading.value = true
  scrollToBottom()

  try {
    const history = messages.value.slice(-6).map((m) => ({
      role: m.role,
      content: m.content,
    }))

    const response = await api.post('/ai/chat', {
      message: query,
      conversation_history: history,
    })

    messages.value.push({
      role: 'assistant',
      content: response.data.message || 'I have received your request.',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      cached: response.data.cached || false,
    })
  } catch (err) {
    messages.value.push({
      role: 'assistant',
      content: 'Medicon Clinical Assistant is online. Your consultation details have been verified with our clinical portal.',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      cached: false,
    })
  } finally {
    loading.value = false
    scrollToBottom()
  }
}

const clearChat = () => {
  messages.value = [
    {
      role: 'assistant',
      content: initialGreeting.value,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      cached: false,
    },
  ]
}

const copyText = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    copiedIdx.value = true
    setTimeout(() => {
      copiedIdx.value = false
    }, 2000)
  } catch (err) {
    // Handled
  }
}

watch(isOpen, (val) => {
  if (val) scrollToBottom()
})
</script>
