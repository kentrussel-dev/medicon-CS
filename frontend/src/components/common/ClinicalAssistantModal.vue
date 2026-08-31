<template>
  <div>
    <!-- Floating Launcher Button (Bottom Right) -->
    <div class="fixed bottom-6 right-6 z-40">
      <button
        @click="isOpen = !isOpen"
        class="px-4 py-3 bg-brand-700 hover:bg-brand-800 text-white border-2 border-brand-800 shadow-xl flex items-center space-x-2.5 transition-all active:scale-98 font-mono text-xs font-bold uppercase tracking-wider"
      >
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full bg-emerald-300 opacity-75"></span>
          <span class="relative inline-flex h-2.5 w-2.5 bg-emerald-400"></span>
        </span>
        <Bot class="w-4 h-4 text-white" />
        <span class="hidden sm:inline">Clinical AI Assistant</span>
        <span class="sm:hidden">AI Assistant</span>
      </button>
    </div>

    <!-- Assistant Chat Modal Window -->
    <div
      v-if="isOpen"
      class="fixed bottom-20 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] sm:w-[480px] h-[610px] max-h-[85vh] bg-white border-2 border-slate-300 shadow-2xl flex flex-col font-sans animate-in fade-in zoom-in-95 duration-150"
    >
      <!-- Top Title Bar -->
      <div class="bg-slate-900 text-white px-4 py-3 border-b-2 border-brand-600 flex items-center justify-between">
        <div class="flex items-center space-x-2.5">
          <div class="w-6 h-6 bg-brand-600 text-white flex items-center justify-center font-bold text-xs border border-brand-700">
            M
          </div>
          <div>
            <div class="font-bold text-xs uppercase tracking-wider leading-none text-white flex items-center space-x-1.5">
              <span>Medicon AI Navigator</span>
              <span class="px-1.5 py-0.2 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[9px] font-mono font-bold uppercase">
                Online
              </span>
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
            title="Close Assistant"
            class="p-1 text-slate-400 hover:text-white transition-colors"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Live Screen Context Awareness Banner -->
      <div class="bg-slate-100 border-b border-slate-300 px-3.5 py-2 flex items-center justify-between text-[11px] font-mono">
        <div class="flex items-center space-x-2 truncate">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
          <span class="text-slate-500 uppercase font-bold text-[10px] shrink-0">Live Screen:</span>
          <span class="font-bold text-slate-900 truncate" :title="screenContext.title">{{ screenContext.title }}</span>
        </div>
        <span class="text-[9px] text-brand-700 uppercase font-bold px-1.5 py-0.5 bg-brand-50 border border-brand-200 shrink-0 ml-2">
          In Focus
        </span>
      </div>

      <!-- Guest Account Notice Strip -->
      <div
        v-if="!auth.isAuthenticated"
        class="bg-amber-50 border-b border-amber-200 px-3.5 py-2 flex items-center justify-between text-xs font-mono"
      >
        <div class="flex items-center space-x-1.5 text-amber-900 text-[11px]">
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
          <span>Browsing as <strong>Guest</strong></span>
        </div>
        <div class="flex items-center space-x-1.5">
          <router-link
            to="/login"
            @click="isOpen = false"
            class="px-2.5 py-0.5 bg-brand-700 hover:bg-brand-800 text-white font-bold text-[10px] uppercase transition-colors"
          >
            Sign In
          </router-link>
          <router-link
            to="/register"
            @click="isOpen = false"
            class="px-2 py-0.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 font-bold text-[10px] uppercase transition-colors"
          >
            Register
          </router-link>
        </div>
      </div>

      <!-- Dynamic Screen-Aware Quick Action Prompt Chips -->
      <div class="bg-slate-50 p-2.5 border-b border-slate-200 overflow-x-auto scrollbar-none flex items-center space-x-2">
        <button
          v-for="chip in dynamicPromptChips"
          :key="chip"
          @click="sendQuickPrompt(chip)"
          :disabled="loading"
          class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 text-[11px] font-mono font-bold text-slate-700 whitespace-nowrap transition-colors disabled:opacity-50"
        >
          {{ chip }}
        </button>
      </div>

      <!-- Messages Thread Area -->
      <div
        ref="messagesContainer"
        class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-slate-50/60 text-xs"
        @click="handleChatLinkClick"
      >
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
            class="p-3.5 max-w-[92%] leading-relaxed"
            :class="
              msg.role === 'user'
                ? 'bg-brand-700 text-white border border-brand-800 shadow-xs'
                : 'bg-white text-slate-900 border border-slate-300 shadow-xs'
            "
          >
            <!-- User Message (Plain Text) -->
            <div
              v-if="msg.role === 'user'"
              class="font-sans select-text whitespace-pre-wrap text-white"
            >
              {{ msg.content }}
            </div>

            <!-- Assistant Message (Parsed Markdown with GFM & Links) -->
            <div
              v-else
              class="prose-chat select-text"
              v-html="renderMarkdown(msg.content)"
            ></div>

            <!-- Contextual Quick Action Buttons for Guest Prompts -->
            <div
              v-if="msg.role === 'assistant' && !auth.isAuthenticated && msg.hasAuthNotice"
              class="mt-3 pt-2.5 border-t border-slate-200 flex flex-wrap gap-1.5"
            >
              <router-link
                to="/login"
                @click="isOpen = false"
                class="px-2.5 py-1 bg-brand-700 hover:bg-brand-800 text-white font-mono text-[10px] font-bold uppercase transition-colors"
              >
                Sign In to Portal
              </router-link>
              <router-link
                to="/register"
                @click="isOpen = false"
                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 font-mono text-[10px] font-bold uppercase transition-colors"
              >
                Create Account
              </router-link>
            </div>

            <!-- Assistant Message Actions -->
            <div v-if="msg.role === 'assistant'" class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-end text-[10px] font-mono text-slate-400">
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
            <span class="inline-block w-1.5 h-1.5 bg-brand-700 animate-ping"></span>
            <span>Consulting clinical gateway...</span>
          </div>
        </div>
      </div>

      <!-- Bottom Chat Input Bar -->
      <form @submit.prevent="sendMessage" class="p-2.5 bg-white border-t-2 border-slate-200 flex items-center space-x-2">
        <input
          type="text"
          v-model="inputQuery"
          :placeholder="inputPlaceholder"
          :disabled="loading"
          class="flex-1 px-3 py-2 border border-slate-300 text-xs focus:border-brand-700 focus:outline-none bg-white rounded-none font-sans"
        />

        <button
          type="submit"
          :disabled="loading || !inputQuery.trim()"
          class="px-4 py-2 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase tracking-wider border border-brand-800 transition-colors disabled:opacity-40 flex items-center space-x-1"
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
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { marked } from 'marked'
import {
  Bot,
  X,
  Send,
  RotateCcw,
  Copy,
} from 'lucide-vue-next'

marked.setOptions({
  breaks: true,
  gfm: true,
})

const renderMarkdown = (content) => {
  if (!content) return ''
  try {
    return marked.parse(content)
  } catch (e) {
    return content
  }
}

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const isOpen = ref(false)
const inputQuery = ref('')
const loading = ref(false)
const copiedIdx = ref(null)
const messagesContainer = ref(null)

// Dynamic Live Screen Context Resolver
const screenContext = computed(() => {
  const path = route.path || '/'
  const name = route.name || ''
  const params = route.params || {}

  if (path.includes('/patient/checkout')) {
    return {
      path,
      name,
      title: 'Checkout & Payment Gateway',
      description: 'Patient is authorizing consultation fee (PayMongo / Stripe, GCash, Maya, Cards in Philippine Peso).',
      details: { appointmentId: params.appointmentId, currency: 'PHP' },
    }
  }

  if (path.includes('/telehealth/room')) {
    return {
      path,
      name,
      title: `Telehealth Video Consultation (#${params.roomCode || params.roomId || 'Active'})`,
      description: 'Live encrypted WebRTC video conference with attending medical specialist.',
      details: { roomCode: params.roomCode || params.roomId },
    }
  }

  if (path === '/patient/appointments') {
    return {
      path,
      name,
      title: 'Patient Appointments Schedule',
      description: 'Upcoming and past doctor consultations, with options to book, join video, or reschedule.',
      details: {},
    }
  }

  if (path === '/patient/doctors') {
    return {
      path,
      name,
      title: 'Doctor Directory & Specialists',
      description: 'Browsing board-certified physicians, clinical bios, and Philippine consultation fees.',
      details: {},
    }
  }

  if (path === '/patient/prescriptions') {
    return {
      path,
      name,
      title: 'Active Prescriptions & Medications',
      description: 'Active pharmacy orders, dosage schedules, and remaining authorized refills.',
      details: {},
    }
  }

  if (path === '/patient/records') {
    return {
      path,
      name,
      title: 'Electronic Health Records (EHR)',
      description: 'Past encounter diagnoses, vitals telemetry (BP, HR), and doctor clinical notes.',
      details: {},
    }
  }

  if (path === '/patient/dashboard') {
    return {
      path,
      name,
      title: 'Patient Health Overview Dashboard',
      description: 'Patient portal hub with quick access to vitals, upcoming visits, and active medications.',
      details: {},
    }
  }

  if (path === '/doctor/dashboard') {
    return {
      path,
      name,
      title: 'Doctor Clinical Control Panel',
      description: 'Physician workstation displaying daily patient queue and scheduled telehealth visits.',
      details: {},
    }
  }

  if (path === '/doctor/schedule') {
    return {
      path,
      name,
      title: 'Doctor Availability & Booking Slots',
      description: 'Managing available consultation hours and telehealth appointment slots.',
      details: {},
    }
  }

  if (path === '/doctor/patients') {
    return {
      path,
      name,
      title: 'Doctor Clinical Patient Rosters',
      description: 'Reviewing assigned patient medical histories and filing encounter summaries.',
      details: {},
    }
  }

  if (path === '/admin/dashboard') {
    return {
      path,
      name,
      title: 'Admin Operations & ML Risk Analytics',
      description: 'Hospital occupancy metrics, no-show ML prediction tiers, and active triage queues.',
      details: {},
    }
  }

  if (path === '/profile') {
    return {
      path,
      name,
      title: 'Account Security & Compliance',
      description: 'Two-Factor Authentication (2FA TOTP), password change, and JSON health data export.',
      details: {},
    }
  }

  if (path === '/login') {
    return {
      path,
      name,
      title: 'Portal Sign In',
      description: 'Authentication screen for Patients, Doctors, and Administrators.',
      details: {},
    }
  }

  return {
    path: '/',
    name: 'landing',
    title: 'Medicon Medical Center (Home)',
    description: 'Hospital landing page with specialties, physician directory, telehealth gateway, and clinic hours.',
    details: {},
  }
})

// Dynamic Screen-Aware Action Chips
const dynamicPromptChips = computed(() => {
  const path = route.path || '/'

  if (path.includes('/patient/checkout')) {
    return [
      'What payment methods are supported?',
      'What is the cancellation refund policy?',
      'Apply promo code MEDICON10',
      'Is my payment data encrypted?',
    ]
  }

  if (path.includes('/telehealth/room')) {
    return [
      'How to share my screen or camera?',
      'Troubleshoot microphone & audio',
      'Where will my prescription be saved?',
      'How do I test my connection speed?',
    ]
  }

  if (path === '/patient/appointments') {
    return [
      'How do I reschedule a visit?',
      'Join my scheduled telehealth call',
      'What is the cancellation deadline?',
      'Book a new doctor appointment',
    ]
  }

  if (path === '/patient/prescriptions') {
    return [
      'Explain my dosage instructions',
      'Check drug interactions',
      'How do I request a prescription refill?',
    ]
  }

  if (path === '/patient/records') {
    return [
      'Explain my latest diagnosis summary',
      'What are healthy blood pressure levels?',
      'How do I download my health records?',
    ]
  }

  if (path === '/profile') {
    return [
      'How does 2FA TOTP authentication work?',
      'Download my complete health data export',
      'Explain the Right to be Forgotten',
    ]
  }

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

  if (!auth.isAuthenticated) {
    return [
      'How to book an appointment',
      'Browse doctors & fees (₱500-₱1800)',
      'How does Telehealth work?',
      'Clinic hours & emergency hotline',
    ]
  }

  return [
    'Explain my active prescriptions',
    'What is my next appointment?',
    'What are the clinic visiting hours?',
    'How do I prepare for a blood test?',
  ]
})

const inputPlaceholder = computed(() => {
  const title = screenContext.value.title
  return `Ask anything about ${title}...`
})

const initialGreeting = computed(() => {
  const title = screenContext.value.title

  if (!auth.isAuthenticated) {
    return `Hello there! Welcome to Medicon Medical Center. I am your Virtual Health Assistant.\n\nI can see you are currently viewing **${title}**. How can I help guide your visit or answer questions today?`
  }
  if (auth.isDoctor) {
    return `Hello Dr. ${auth.user?.name || 'Physician'}! I am your Medicon Clinical Co-Pilot. Currently monitoring **${title}**. How can I assist with your patient encounters, SOAP drafting, or pharmacology references?`
  }
  if (auth.isAdmin) {
    return `Hello ${auth.user?.name}! I am the Medicon Hospital Operations Assistant. Currently tracking **${title}**. How can I assist with clinical utilization, risk analytics, or audit policies?`
  }
  return `Hi ${auth.user?.name?.split(' ')[0] || 'there'}! I am your Medicon Healthcare Assistant. I am tracking your active screen (**${title}**). How can I assist you right now?`
})

const messages = ref([
  {
    role: 'assistant',
    content: initialGreeting.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    hasAuthNotice: false,
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
      screen_context: screenContext.value,
    })

    const ans = response.data.message || 'I have received your request.'
    const mentionsAuthLinks = ans.includes('[/login]') || ans.includes('[/register]')

    messages.value.push({
      role: 'assistant',
      content: ans,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      hasAuthNotice: !auth.isAuthenticated && mentionsAuthLinks,
    })
  } catch (err) {
    messages.value.push({
      role: 'assistant',
      content: !auth.isAuthenticated
        ? `I am aware you are viewing **${screenContext.value.title}**! You can ask me about specialists, clinic hours, or how to book a visit.`
        : `Medicon Clinical Assistant is online for **${screenContext.value.title}**. How can I assist you right now?`,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      hasAuthNotice: false,
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
      hasAuthNotice: false,
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

const handleChatLinkClick = (e) => {
  const anchor = e.target.closest('a')
  if (!anchor) return
  const href = anchor.getAttribute('href')
  if (href && href.startsWith('/')) {
    e.preventDefault()
    isOpen.value = false
    router.push(href)
  }
}

watch(isOpen, (val) => {
  if (val) scrollToBottom()
})

watch(() => auth.isAuthenticated, () => {
  clearChat()
})
</script>

<style scoped>
:deep(.prose-chat) {
  font-family: inherit;
  font-size: 0.775rem;
  line-height: 1.5;
  color: #1e293b;
}

:deep(.prose-chat strong) {
  font-weight: 700;
  color: #0f172a;
}

:deep(.prose-chat p) {
  margin-bottom: 0.5rem;
}

:deep(.prose-chat p:last-child) {
  margin-bottom: 0;
}

:deep(.prose-chat ul) {
  list-style-type: disc;
  padding-left: 1.25rem;
  margin-top: 0.35rem;
  margin-bottom: 0.35rem;
}

:deep(.prose-chat ol) {
  list-style-type: decimal;
  padding-left: 1.25rem;
  margin-top: 0.35rem;
  margin-bottom: 0.35rem;
}

:deep(.prose-chat li) {
  margin-bottom: 0.25rem;
}

:deep(.prose-chat code) {
  background-color: #f1f5f9;
  color: #0369a1;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.725rem;
  font-weight: 600;
  padding: 0.1rem 0.3rem;
  border-radius: 0.25rem;
  border: 1px solid #e2e8f0;
}

:deep(.prose-chat a) {
  color: #0369a1;
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
}

:deep(.prose-chat a:hover) {
  color: #075985;
}
</style>
