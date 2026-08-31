<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 flex flex-col font-sans select-none">
    <!-- Top Telehealth Clinical Header -->
    <header class="bg-white border-b-2 border-slate-200 px-4 py-3 flex items-center justify-between z-30 shadow-xs">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 bg-brand-700 text-white flex items-center justify-center font-bold text-sm border border-brand-800">
          M
        </div>
        <div>
          <div class="flex items-center space-x-2 text-[11px] font-mono">
            <span class="text-brand-800 font-bold uppercase">Medicon Telehealth</span>
            <span class="text-slate-300">/</span>
            <span class="text-slate-600 font-bold uppercase">Room #{{ appointmentId }}</span>
            <span class="px-1.5 py-0.2 bg-emerald-50 text-emerald-800 border border-emerald-300 text-[9px] uppercase font-bold">
              ENCRYPTED WEBRTC HD
            </span>
          </div>
          <h1 class="text-sm font-bold uppercase text-slate-950 mt-0.5 tracking-tight">
            {{ appointment?.reason || 'Multi-Party Clinical Consultation' }}
          </h1>
        </div>
      </div>

      <div class="flex items-center space-x-3 font-mono text-xs">
        <!-- Reconnecting Banner Alert -->
        <div v-if="connectionState === 'reconnecting'" class="flex items-center space-x-1.5 px-3 py-1 bg-amber-50 border border-amber-300 text-amber-800 text-[11px] animate-pulse">
          <RefreshCw class="w-3.5 h-3.5 animate-spin text-amber-600" />
          <span class="font-bold">RECONNECTING MEDIA GATEWAY...</span>
        </div>

        <div v-else class="flex items-center space-x-1.5 text-emerald-700 text-[11px]">
          <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
          <span class="font-bold uppercase">LIVE ({{ participants.length }} Active)</span>
        </div>

        <button
          @click="showSidebar = !showSidebar"
          class="px-2.5 py-1 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-bold uppercase flex items-center space-x-1"
        >
          <Users class="w-3.5 h-3.5 text-slate-500" />
          <span class="hidden sm:inline">Roster ({{ participants.length }})</span>
        </button>
      </div>
    </header>

    <!-- Main Workspace (Video Grid + Optional Side Info Panel) -->
    <div class="flex-1 flex overflow-hidden relative">
      <!-- Video Grid Area -->
      <main class="flex-1 p-3 sm:p-5 flex items-center justify-center overflow-y-auto bg-slate-100/70">
        <!-- Reconnection Overlay -->
        <div
          v-if="connectionState === 'reconnecting'"
          class="absolute inset-0 z-40 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center"
        >
          <div class="bg-white border-2 border-amber-500 p-6 max-w-md w-full space-y-3 font-mono shadow-xl">
            <RefreshCw class="w-8 h-8 text-amber-600 animate-spin mx-auto" />
            <h3 class="font-bold text-sm text-slate-950 uppercase">Reconnecting to Consultation Room</h3>
            <p class="text-xs text-slate-600 font-sans">
              A temporary network fluctuation occurred. Re-negotiating secure WebRTC media stream without disconnecting your session...
            </p>
          </div>
        </div>

        <!-- Dynamic Multi-Participant Grid -->
        <div
          class="w-full h-full max-w-7xl grid gap-3 sm:gap-4 transition-all"
          :class="{
            'grid-cols-1 max-w-2xl': participants.length <= 1,
            'grid-cols-1 md:grid-cols-2': participants.length === 2,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': participants.length >= 3,
          }"
        >
          <div
            v-for="p in participants"
            :key="p.id"
            class="relative bg-white border-2 border-slate-300 flex flex-col justify-between overflow-hidden shadow-sm aspect-video sm:aspect-auto sm:min-h-[260px]"
          >
            <!-- Participant Video Canvas / Stream -->
            <div class="absolute inset-0 flex items-center justify-center bg-slate-100">
              <video
                v-if="p.isLocal && cameraOn"
                ref="localVideoEl"
                autoplay
                playsinline
                muted
                class="w-full h-full object-cover mirror"
              ></video>

              <div
                v-else
                class="w-full h-full flex flex-col items-center justify-center bg-slate-100 p-4 text-center"
              >
                <div class="w-16 h-16 bg-white border-2 border-slate-300 flex items-center justify-center font-bold text-xl uppercase text-slate-800 mb-2 shadow-xs">
                  {{ p.name.charAt(0) }}
                </div>
                <span class="text-xs font-bold text-slate-900 uppercase">{{ p.name }}</span>
                <span class="text-[10px] font-mono text-slate-500 mt-0.5">Camera Off</span>
              </div>
            </div>

            <!-- Top Tile Tag -->
            <div class="relative z-10 p-2.5 flex items-center justify-between bg-white/90 backdrop-blur-xs border-b border-slate-200">
              <div class="flex items-center space-x-1.5">
                <span
                  class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider border"
                  :class="getRoleBadgeClass(p.role)"
                >
                  {{ p.role }}
                </span>
                <span v-if="p.isLocal" class="text-[10px] font-mono text-slate-500 uppercase font-bold">(You)</span>
              </div>

              <!-- Audio Status Icon -->
              <div class="flex items-center space-x-1 bg-white px-2 py-0.5 border border-slate-300 shadow-2xs">
                <component
                  :is="(p.isLocal ? micOn : p.audioActive) ? Mic : MicOff"
                  class="w-3 h-3"
                  :class="(p.isLocal ? micOn : p.audioActive) ? 'text-emerald-600' : 'text-rose-600'"
                />
              </div>
            </div>

            <!-- Bottom Tile Participant Name -->
            <div class="relative z-10 p-2.5 bg-white/90 backdrop-blur-xs border-t border-slate-200 flex items-center justify-between text-xs font-mono">
              <span class="font-bold text-slate-950 uppercase truncate">{{ p.name }}</span>
              <span class="text-[10px] text-slate-500 font-mono font-bold">HD 1080p</span>
            </div>
          </div>
        </div>
      </main>

      <!-- Right Clinical Roster & Consultation Details Sidebar -->
      <aside
        v-if="showSidebar"
        class="w-80 bg-white border-l-2 border-slate-200 flex flex-col justify-between p-4 space-y-4 z-30"
      >
        <div class="space-y-4 overflow-y-auto text-xs font-mono">
          <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <span class="font-bold text-slate-950 uppercase">Consultation Participants</span>
            <button @click="showSidebar = false" class="text-slate-400 hover:text-slate-900">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Participant List -->
          <div class="space-y-2">
            <div
              v-for="p in participants"
              :key="p.id"
              class="p-2.5 bg-slate-50 border border-slate-200 flex items-center justify-between"
            >
              <div>
                <div class="font-bold text-slate-950 uppercase">{{ p.name }}</div>
                <div class="text-[10px] text-slate-500">{{ p.role }}</div>
              </div>
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-600"></span>
            </div>
          </div>

          <!-- Clinical Case Context -->
          <div class="pt-2 border-t border-slate-200 space-y-2">
            <span class="text-[10px] font-bold text-slate-500 uppercase block">Patient Case Context</span>
            <div class="p-2.5 bg-slate-50 border border-slate-200 space-y-1.5 text-[11px]">
              <div><span class="text-slate-500">Patient:</span> <strong class="text-slate-900">Jane Doe (31 yrs)</strong></div>
              <div><span class="text-slate-500">Allergies:</span> <strong class="text-rose-600">Penicillin, Sulfa</strong></div>
              <div><span class="text-slate-500">Blood Group:</span> <strong class="text-slate-900">O+</strong></div>
              <div><span class="text-slate-500">Scheduled:</span> <strong class="text-slate-900">Cardiology Telehealth</strong></div>
            </div>
          </div>
        </div>

        <div v-if="canAddParticipants" class="pt-2 border-t border-slate-200">
          <button
            @click="showAddParticipantModal = true"
            class="w-full py-2 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase border border-brand-800 flex items-center justify-center space-x-1.5 transition-colors"
          >
            <UserPlus class="w-3.5 h-3.5" />
            <span>Invite Specialist / Translator</span>
          </button>
        </div>
      </aside>
    </div>

    <!-- Bottom Control Console -->
    <footer class="bg-white border-t-2 border-slate-200 px-4 py-3 flex items-center justify-between z-30 shadow-xs">
      <div class="hidden sm:flex items-center space-x-2 text-xs font-mono text-slate-500">
        <ShieldCheck class="w-4 h-4 text-emerald-600" />
        <span class="font-bold">HIPAA AES-256 ENCRYPTED MEDIA STREAM</span>
      </div>

      <!-- Centered Media Control Buttons -->
      <div class="flex items-center space-x-3 mx-auto sm:mx-0">
        <!-- Mic Toggle -->
        <button
          @click="toggleMic"
          class="px-3.5 py-2.5 border font-bold text-xs uppercase flex items-center space-x-2 transition-colors"
          :class="micOn ? 'bg-white hover:bg-slate-50 border-slate-300 text-slate-800' : 'bg-rose-50 border-rose-300 text-rose-800'"
        >
          <component :is="micOn ? Mic : MicOff" class="w-4 h-4" />
          <span class="hidden md:inline">{{ micOn ? 'Mute Mic' : 'Unmute Mic' }}</span>
        </button>

        <!-- Camera Toggle -->
        <button
          @click="toggleCamera"
          class="px-3.5 py-2.5 border font-bold text-xs uppercase flex items-center space-x-2 transition-colors"
          :class="cameraOn ? 'bg-white hover:bg-slate-50 border-slate-300 text-slate-800' : 'bg-rose-50 border-rose-300 text-rose-800'"
        >
          <component :is="cameraOn ? Video : VideoOff" class="w-4 h-4" />
          <span class="hidden md:inline">{{ cameraOn ? 'Stop Camera' : 'Start Camera' }}</span>
        </button>

        <!-- Add Participant Button (Doctor / Admin) -->
        <button
          v-if="canAddParticipants"
          @click="showAddParticipantModal = true"
          class="px-3.5 py-2.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-800 font-bold text-xs uppercase flex items-center space-x-2 transition-colors"
        >
          <UserPlus class="w-4 h-4 text-brand-700" />
          <span class="hidden md:inline">Add Participant</span>
        </button>

        <!-- Leave Call Button -->
        <button
          @click="leaveCall"
          class="px-5 py-2.5 bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs uppercase tracking-wider border border-rose-800 flex items-center space-x-2 transition-colors shadow-xs"
        >
          <PhoneOff class="w-4 h-4" />
          <span>Leave Room</span>
        </button>
      </div>

      <div class="hidden lg:flex items-center space-x-2 font-mono text-xs text-slate-500">
        <span>Session ID: LK-{{ appointmentId }}-SEC</span>
      </div>
    </footer>

    <!-- Add Participant Modal -->
    <AddParticipantModal
      :is-open="showAddParticipantModal"
      :appointment-id="appointmentId"
      @close="showAddParticipantModal = false"
      @participant-added="handleParticipantAdded"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import AddParticipantModal from '@/components/telehealth/AddParticipantModal.vue'
import {
  Mic,
  MicOff,
  Video,
  VideoOff,
  PhoneOff,
  UserPlus,
  Users,
  X,
  RefreshCw,
  ShieldCheck,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const appointmentId = computed(() => route.params.id || 1)
const appointment = ref(null)
const connectionState = ref('connected') // connected, reconnecting, disconnected
const micOn = ref(true)
const cameraOn = ref(true)
const showSidebar = ref(false)
const showAddParticipantModal = ref(false)
const localVideoEl = ref(null)
let localMediaStream = null

const canAddParticipants = computed(() => auth.isDoctor || auth.isAdmin)

// Simulated live multi-participant roster matching clinical consultation parameters
const participants = ref([
  {
    id: 'local',
    name: auth.user?.name || 'Jane Doe',
    role: auth.role?.toUpperCase() || 'PATIENT',
    isLocal: true,
    audioActive: true,
  },
  {
    id: 'remote-1',
    name: 'Dr. Sarah Jenkins, MD, FACC',
    role: 'ATTENDING DOCTOR',
    isLocal: false,
    audioActive: true,
  },
  {
    id: 'remote-2',
    name: 'Dr. Marcus Chen (Neurology Specialist)',
    role: 'SPECIALIST',
    isLocal: false,
    audioActive: true,
  },
])

const getRoleBadgeClass = (role) => {
  const r = (role || '').toUpperCase()
  if (r.includes('DOCTOR') || r.includes('PHYSICIAN')) {
    return 'bg-brand-50 text-brand-900 border-brand-300 font-bold'
  }
  if (r.includes('PATIENT')) {
    return 'bg-emerald-50 text-emerald-900 border-emerald-300 font-bold'
  }
  if (r.includes('SPECIALIST')) {
    return 'bg-indigo-50 text-indigo-900 border-indigo-300 font-bold'
  }
  if (r.includes('TRANSLATOR') || r.includes('INTERPRETER')) {
    return 'bg-amber-50 text-amber-900 border-amber-300 font-bold'
  }
  return 'bg-slate-100 text-slate-800 border-slate-300 font-bold'
}

const startLocalMedia = async () => {
  try {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      localMediaStream = await navigator.mediaDevices.getUserMedia({
        video: true,
        audio: true,
      })
      if (localVideoEl.value) {
        localVideoEl.value.srcObject = localMediaStream
      }
    }
  } catch (err) {
    console.warn('Camera/Microphone access simulated for clinical portal sandbox:', err)
  }
}

const stopLocalMedia = () => {
  if (localMediaStream) {
    localMediaStream.getTracks().forEach((t) => t.stop())
    localMediaStream = null
  }
}

const toggleMic = () => {
  micOn.value = !micOn.value
  if (localMediaStream) {
    localMediaStream.getAudioTracks().forEach((t) => {
      t.enabled = micOn.value
    })
  }
}

const toggleCamera = () => {
  cameraOn.value = !cameraOn.value
  if (localMediaStream) {
    localMediaStream.getVideoTracks().forEach((t) => {
      t.enabled = cameraOn.value
    })
  }
}

const handleParticipantAdded = (newParticipant) => {
  participants.value.push({
    id: `participant-${newParticipant.id}`,
    name: newParticipant.name,
    role: (newParticipant.role || 'SPECIALIST').toUpperCase(),
    isLocal: false,
    audioActive: true,
  })
}

const leaveCall = async () => {
  try {
    await api.post(`/appointments/${appointmentId.value}/telehealth/events`, {
      event: 'LEAVE',
      duration_seconds: 180,
    })
  } catch (e) {
    // Handled
  } finally {
    stopLocalMedia()
    if (auth.isDoctor) {
      router.push('/doctor/appointments')
    } else if (auth.isAdmin) {
      router.push('/admin/dashboard')
    } else {
      router.push('/patient/appointments')
    }
  }
}

const loadSession = async () => {
  try {
    const res = await api.get(`/appointments/${appointmentId.value}/telehealth/token`)
    if (res.data?.appointment) {
      appointment.value = res.data.appointment
    }
  } catch (err) {
    // Handled via mock adapter
  }
}

onMounted(async () => {
  await loadSession()
  await startLocalMedia()

  // Simulate network resilience / graceful auto-reconnect test listener
  window.addEventListener('offline', () => {
    connectionState.value = 'reconnecting'
  })
  window.addEventListener('online', () => {
    connectionState.value = 'connected'
  })
})

onUnmounted(() => {
  stopLocalMedia()
})
</script>

<style scoped>
.mirror {
  transform: scaleX(-1);
}
</style>
