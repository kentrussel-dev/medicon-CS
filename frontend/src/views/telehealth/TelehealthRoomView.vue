<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans select-none">
    <!-- Top Telehealth Clinical Header -->
    <header class="bg-slate-900 border-b border-slate-800 px-4 py-3 flex items-center justify-between z-30">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 bg-brand-700 text-white flex items-center justify-center font-bold text-sm border border-brand-600">
          M
        </div>
        <div>
          <div class="flex items-center space-x-2 text-[11px] font-mono">
            <span class="text-brand-400 font-bold uppercase">Medicon Telehealth</span>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold uppercase">Room #{{ appointmentId }}</span>
            <span class="px-1.5 py-0.2 bg-emerald-950 text-emerald-400 border border-emerald-800 text-[9px] uppercase font-bold">
              ENCRYPTED WEBRTC HD
            </span>
          </div>
          <h1 class="text-sm font-bold uppercase text-white mt-0.5 tracking-tight">
            {{ appointment?.reason || 'Multi-Party Clinical Consultation' }}
          </h1>
        </div>
      </div>

      <div class="flex items-center space-x-3 font-mono text-xs">
        <!-- Reconnecting Banner Alert -->
        <div v-if="connectionState === 'reconnecting'" class="flex items-center space-x-1.5 px-3 py-1 bg-amber-950 border border-amber-600 text-amber-300 text-[11px] animate-pulse">
          <RefreshCw class="w-3.5 h-3.5 animate-spin" />
          <span>RECONNECTING MEDIA GATEWAY...</span>
        </div>

        <div v-else class="flex items-center space-x-1.5 text-emerald-400 text-[11px]">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
          <span class="font-bold">LIVE ({{ participants.length }} Active)</span>
        </div>

        <button
          @click="showSidebar = !showSidebar"
          class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-xs flex items-center space-x-1"
        >
          <Users class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Roster ({{ participants.length }})</span>
        </button>
      </div>
    </header>

    <!-- Main Workspace (Video Grid + Optional Side Info Panel) -->
    <div class="flex-1 flex overflow-hidden relative">
      <!-- Video Grid Area -->
      <main class="flex-1 p-3 sm:p-5 flex items-center justify-center overflow-y-auto">
        <!-- Reconnection Overlay -->
        <div
          v-if="connectionState === 'reconnecting'"
          class="absolute inset-0 z-40 bg-slate-950/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center"
        >
          <div class="bg-slate-900 border-2 border-amber-500 p-6 max-w-md w-full space-y-3 font-mono">
            <RefreshCw class="w-8 h-8 text-amber-400 animate-spin mx-auto" />
            <h3 class="font-bold text-sm text-white uppercase">Reconnecting to Consultation Room</h3>
            <p class="text-xs text-slate-400 font-sans">
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
            class="relative bg-slate-900 border-2 border-slate-800 flex flex-col justify-between overflow-hidden shadow-lg group aspect-video sm:aspect-auto sm:min-h-[260px]"
          >
            <!-- Participant Video Canvas / Stream -->
            <div class="absolute inset-0 flex items-center justify-center bg-slate-950">
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
                class="w-full h-full flex flex-col items-center justify-center bg-radial from-slate-900 to-slate-950 p-4 text-center"
              >
                <div class="w-16 h-16 bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xl uppercase text-slate-300 mb-2">
                  {{ p.name.charAt(0) }}
                </div>
                <span class="text-xs font-bold text-slate-300 uppercase">{{ p.name }}</span>
                <span class="text-[10px] font-mono text-slate-500 mt-0.5">Camera Muted</span>
              </div>
            </div>

            <!-- Top Tile Tag -->
            <div class="relative z-10 p-2.5 flex items-center justify-between bg-gradient-to-b from-slate-950/90 to-transparent">
              <div class="flex items-center space-x-1.5">
                <span
                  class="px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider border"
                  :class="getRoleBadgeClass(p.role)"
                >
                  {{ p.role }}
                </span>
                <span v-if="p.isLocal" class="text-[10px] font-mono text-slate-400 uppercase font-bold">(You)</span>
              </div>

              <!-- Audio Status Icon -->
              <div class="flex items-center space-x-1 bg-slate-950/80 px-2 py-0.5 border border-slate-800">
                <component
                  :is="(p.isLocal ? micOn : p.audioActive) ? Mic : MicOff"
                  class="w-3 h-3"
                  :class="(p.isLocal ? micOn : p.audioActive) ? 'text-emerald-400' : 'text-rose-400'"
                />
              </div>
            </div>

            <!-- Bottom Tile Participant Name -->
            <div class="relative z-10 p-2.5 bg-gradient-to-t from-slate-950/90 to-transparent flex items-center justify-between text-xs font-mono">
              <span class="font-bold text-white uppercase truncate">{{ p.name }}</span>
              <span class="text-[10px] text-slate-400 font-mono">HD 1080p</span>
            </div>
          </div>
        </div>
      </main>

      <!-- Right Clinical Roster & Consultation Details Sidebar -->
      <aside
        v-if="showSidebar"
        class="w-80 bg-slate-900 border-l border-slate-800 flex flex-col justify-between p-4 space-y-4 z-30"
      >
        <div class="space-y-4 overflow-y-auto text-xs font-mono">
          <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <span class="font-bold text-slate-200 uppercase">Consultation Participants</span>
            <button @click="showSidebar = false" class="text-slate-400 hover:text-white">
              <X class="w-4 h-4" />
            </button>
          </div>

          <!-- Participant List -->
          <div class="space-y-2">
            <div
              v-for="p in participants"
              :key="p.id"
              class="p-2.5 bg-slate-950 border border-slate-800 flex items-center justify-between"
            >
              <div>
                <div class="font-bold text-slate-100 uppercase">{{ p.name }}</div>
                <div class="text-[10px] text-slate-400">{{ p.role }}</div>
              </div>
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
          </div>

          <!-- Clinical Case Context -->
          <div class="pt-2 border-t border-slate-800 space-y-2">
            <span class="text-[10px] font-bold text-slate-400 uppercase block">Patient Case Context</span>
            <div class="p-2.5 bg-slate-950 border border-slate-800 space-y-1.5 text-[11px]">
              <div><span class="text-slate-500">Patient:</span> <strong class="text-slate-200">Jane Doe (31 yrs)</strong></div>
              <div><span class="text-slate-500">Allergies:</span> <strong class="text-rose-400">Penicillin, Sulfa</strong></div>
              <div><span class="text-slate-500">Blood Group:</span> <strong class="text-slate-200">O+</strong></div>
              <div><span class="text-slate-500">Scheduled:</span> <strong class="text-slate-200">Cardiology Telehealth</strong></div>
            </div>
          </div>
        </div>

        <div v-if="canAddParticipants" class="pt-2 border-t border-slate-800">
          <button
            @click="showAddParticipantModal = true"
            class="w-full py-2 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase border border-brand-600 flex items-center justify-center space-x-1.5 transition-colors"
          >
            <UserPlus class="w-3.5 h-3.5" />
            <span>Invite Specialist / Translator</span>
          </button>
        </div>
      </aside>
    </div>

    <!-- Bottom Control Console -->
    <footer class="bg-slate-900 border-t border-slate-800 px-4 py-3 flex items-center justify-between z-30">
      <div class="hidden sm:flex items-center space-x-2 text-xs font-mono text-slate-400">
        <ShieldCheck class="w-4 h-4 text-emerald-400" />
        <span>HIPAA AES-256 ENCRYPTED MEDIA STREAM</span>
      </div>

      <!-- Centered Media Control Buttons -->
      <div class="flex items-center space-x-3 mx-auto sm:mx-0">
        <!-- Mic Toggle -->
        <button
          @click="toggleMic"
          class="p-3 border font-bold text-xs uppercase flex items-center space-x-2 transition-colors"
          :class="micOn ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-white' : 'bg-rose-950 border-rose-600 text-rose-300'"
        >
          <component :is="micOn ? Mic : MicOff" class="w-4 h-4" />
          <span class="hidden md:inline">{{ micOn ? 'Mute Mic' : 'Unmute Mic' }}</span>
        </button>

        <!-- Camera Toggle -->
        <button
          @click="toggleCamera"
          class="p-3 border font-bold text-xs uppercase flex items-center space-x-2 transition-colors"
          :class="cameraOn ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-white' : 'bg-rose-950 border-rose-600 text-rose-300'"
        >
          <component :is="cameraOn ? Video : VideoOff" class="w-4 h-4" />
          <span class="hidden md:inline">{{ cameraOn ? 'Stop Camera' : 'Start Camera' }}</span>
        </button>

        <!-- Add Participant Button (Doctor / Admin) -->
        <button
          v-if="canAddParticipants"
          @click="showAddParticipantModal = true"
          class="p-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs uppercase flex items-center space-x-2 transition-colors"
        >
          <UserPlus class="w-4 h-4 text-brand-400" />
          <span class="hidden md:inline">Add Participant</span>
        </button>

        <!-- Leave Call Button -->
        <button
          @click="leaveCall"
          class="px-5 py-3 bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs uppercase tracking-wider border border-rose-600 flex items-center space-x-2 transition-colors shadow-md"
        >
          <PhoneOff class="w-4 h-4" />
          <span>Leave Room</span>
        </button>
      </div>

      <div class="hidden lg:flex items-center space-x-2 font-mono text-xs text-slate-400">
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
    return 'bg-brand-950 text-brand-300 border-brand-700'
  }
  if (r.includes('PATIENT')) {
    return 'bg-emerald-950 text-emerald-300 border-emerald-700'
  }
  if (r.includes('SPECIALIST')) {
    return 'bg-indigo-950 text-indigo-300 border-indigo-700'
  }
  if (r.includes('TRANSLATOR') || r.includes('INTERPRETER')) {
    return 'bg-amber-950 text-amber-300 border-amber-700'
  }
  return 'bg-slate-800 text-slate-300 border-slate-700'
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
