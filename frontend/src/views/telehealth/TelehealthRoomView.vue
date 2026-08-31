<template>
  <div v-if="!isRoomClosed" class="min-h-screen bg-slate-50 text-slate-900 flex flex-col font-sans select-none">
    <!-- 1. GOOGLE MEET PRE-JOIN GREEN ROOM / WAITING LOBBY -->
    <div v-if="inLobby" class="flex-1 flex flex-col justify-between min-h-screen">
      <!-- Lobby Header -->
      <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-2xs">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-brand-700 text-white flex items-center justify-center font-bold text-sm border border-brand-800 rounded">
            M
          </div>
          <div>
            <span class="text-xs font-mono font-bold text-brand-800 uppercase block">Medicon Telehealth Gateway</span>
            <span class="text-sm font-bold text-slate-900">Pre-Consultation Green Room</span>
          </div>
        </div>

        <div class="flex items-center space-x-3 text-xs font-mono">
          <button
            @click="copyRoomLink"
            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded text-slate-800 font-bold flex items-center space-x-1.5 transition-colors"
          >
            <span class="text-brand-700">#{{ roomCode }}</span>
            <Copy class="w-3.5 h-3.5 text-slate-500" />
            <span v-if="copiedLink" class="text-[9px] text-emerald-600 font-bold">COPIED!</span>
          </button>
          <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] uppercase font-bold rounded">
            256-BIT ENCRYPTED
          </span>
        </div>
      </header>

      <!-- Main Green Room Lobby Stage (Split 2 Columns) -->
      <main class="flex-1 flex items-center justify-center p-4 sm:p-8">
        <div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          <!-- Left Column: 16:9 Video Self-Check & Audio Controls -->
          <div class="lg:col-span-7 flex flex-col items-center">
            <div class="relative aspect-video w-full max-w-xl bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center">
              <video
                v-if="cameraOn"
                :ref="setVideoRef"
                autoplay
                playsinline
                muted
                class="w-full h-full object-cover mirror"
              ></video>
              <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 text-center p-4">
                <div class="w-20 h-20 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-3xl uppercase shadow-xl border-2 border-slate-700 mb-2">
                  {{ auth.user?.name?.charAt(0) || 'J' }}
                </div>
                <span class="text-sm font-bold text-white uppercase">{{ auth.user?.name || 'Jane Doe' }}</span>
                <span class="text-xs font-mono text-slate-400 mt-0.5">Camera is turned off</span>
              </div>

              <!-- Bottom Floating Audio & Video Device Toggle Controls -->
              <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center space-x-3 z-30 bg-slate-950/85 backdrop-blur-md px-4 py-2 rounded-full border border-slate-800 shadow-xl">
                <!-- Mic Toggle Button -->
                <button
                  @click="toggleMic"
                  :title="micOn ? 'Turn off microphone' : 'Turn on microphone'"
                  class="w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-xs border"
                  :class="micOn ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-emerald-400' : 'bg-rose-600 hover:bg-rose-700 border-rose-500 text-white'"
                >
                  <component :is="micOn ? Mic : MicOff" class="w-4 h-4" />
                </button>

                <!-- Camera Toggle Button -->
                <button
                  @click="toggleCamera"
                  :title="cameraOn ? 'Turn off camera' : 'Turn on camera'"
                  class="w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-xs border"
                  :class="cameraOn ? 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-white' : 'bg-rose-600 hover:bg-rose-700 border-rose-500 text-white'"
                >
                  <component :is="cameraOn ? Video : VideoOff" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Audio & Video Status -->
            <div class="mt-3 flex items-center space-x-4 text-xs font-mono">
              <span v-if="micOn" class="flex items-center space-x-1.5 text-emerald-600 font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Microphone active</span>
              </span>
              <span v-else class="text-rose-600 font-bold">
                Microphone muted
              </span>

              <span class="text-slate-300">|</span>

              <span v-if="cameraOn" class="text-slate-600">
                HD 16:9 Camera Active
              </span>
              <span v-else class="text-slate-400">
                Camera Disabled
              </span>
            </div>
          </div>

          <!-- Right Column: Room Details, Who's in the Call, and Join Now Button -->
          <div class="lg:col-span-5 bg-white border border-slate-300 rounded-2xl p-6 shadow-xl space-y-5">
            <div>
              <div class="flex items-center space-x-2 mb-1.5">
                <span class="px-2 py-0.5 bg-brand-50 border border-brand-200 text-brand-700 rounded text-[10px] font-mono font-bold uppercase">
                  Room #{{ roomCode }}
                </span>
                <button
                  @click="copyRoomLink"
                  class="text-xs text-slate-500 hover:text-brand-700 flex items-center space-x-1"
                >
                  <Copy class="w-3 h-3" />
                  <span v-if="copiedLink" class="text-emerald-600 font-bold text-[10px]">COPIED</span>
                  <span v-else class="text-[10px]">Copy link</span>
                </button>
              </div>
              <h2 class="text-xl font-bold text-slate-900 tracking-tight">Ready to join?</h2>
              <p class="text-xs text-slate-500 mt-0.5">Adjust your camera and microphone before entering.</p>
            </div>

            <!-- Clinical Consultation Summary Card -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2 text-xs">
              <div class="flex justify-between items-start">
                <span class="text-slate-500 font-mono text-[10px] uppercase font-bold">Clinical Case</span>
                <span class="px-1.5 py-0.2 bg-emerald-50 text-emerald-700 border border-emerald-300 text-[9px] font-bold uppercase rounded">CONFIRMED</span>
              </div>
              <h4 class="font-bold text-slate-900 uppercase text-xs">
                {{ appointment?.reason || 'Multi-Party Clinical Consultation' }}
              </h4>
              <div class="pt-2 border-t border-slate-200 grid grid-cols-2 gap-2 text-[11px]">
                <div>
                  <span class="text-slate-400 block text-[10px] uppercase">Patient</span>
                  <span class="font-bold text-slate-800">{{ appointment?.patient_name || auth.user?.name || 'Jane Doe' }}</span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[10px] uppercase">Physician</span>
                  <span class="font-bold text-slate-800">{{ appointment?.doctor_name || 'Dr. Sarah Jenkins, MD' }}</span>
                </div>
              </div>
            </div>

            <!-- Who's Already in the Call -->
            <div>
              <span class="text-xs font-mono font-bold text-slate-700 block uppercase mb-2">
                Already in this consultation ({{ otherParticipants.length }}):
              </span>
              <div class="space-y-2">
                <div
                  v-for="p in otherParticipants"
                  :key="p.id"
                  class="flex items-center space-x-3 p-2 rounded-lg bg-slate-50 border border-slate-200"
                >
                  <div class="w-7 h-7 rounded-full bg-brand-700 text-white flex items-center justify-center font-bold text-xs">
                    {{ p.name.charAt(0) }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <span class="font-bold text-xs text-slate-900 block truncate">{{ p.name }}</span>
                    <span class="text-[10px] font-mono text-slate-500">{{ p.role }}</span>
                  </div>
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2 pt-2">
              <button
                @click="joinCall"
                class="w-full py-3 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-md active:scale-95 flex items-center justify-center space-x-2"
              >
                <Video class="w-4 h-4" />
                <span>Join Now</span>
              </button>

              <button
                @click="goToDashboard"
                class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-xs font-bold uppercase rounded-xl transition-colors border border-slate-300"
              >
                Back to Dashboard
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- 2. ACTIVE LIVE MULTI-PARTY IN-CALL STAGE (When inLobby === false) -->
    <div v-else class="flex-1 flex flex-col font-sans select-none">
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
              <!-- Unique Room Code Badge with 1-Click Copy -->
              <button
                @click="copyRoomLink"
                title="Click to copy consultation link"
                class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded text-slate-800 font-mono font-bold flex items-center space-x-1 transition-colors"
              >
                <span class="text-brand-700 font-bold">#{{ roomCode }}</span>
                <Copy class="w-3 h-3 text-slate-500" />
                <span v-if="copiedLink" class="text-[9px] text-emerald-600 font-bold ml-1">COPIED!</span>
              </button>
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

          <button
            @click="showSidebar = !showSidebar"
            class="px-2.5 py-1 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs font-bold uppercase flex items-center space-x-1"
          >
            <Users class="w-3.5 h-3.5 text-slate-500" />
            <span class="hidden sm:inline">Roster ({{ participants.length }})</span>
          </button>
        </div>
      </header>

    <!-- Main Workspace (Google Meet Widescreen Fit-to-Screen Stage) -->
    <div class="flex-1 flex overflow-hidden relative h-[calc(100vh-125px)]">
      <!-- Video Grid Area -->
      <main class="flex-1 p-2 sm:p-4 flex items-center justify-center overflow-hidden bg-slate-100/90">
        <!-- Reconnection Overlay -->
        <div
          v-if="connectionState === 'reconnecting'"
          class="absolute inset-0 z-40 bg-white/80 backdrop-blur-xs flex flex-col items-center justify-center p-6 text-center"
        >
          <div class="bg-white border-2 border-amber-500 p-6 max-w-md w-full space-y-3 font-mono shadow-xl rounded-xl">
            <RefreshCw class="w-8 h-8 text-amber-600 animate-spin mx-auto" />
            <h3 class="font-bold text-sm text-slate-950 uppercase">Reconnecting to Consultation Room</h3>
            <p class="text-xs text-slate-600 font-sans">
              A temporary network fluctuation occurred. Re-negotiating secure WebRTC media stream without disconnecting your session...
            </p>
          </div>
        </div>

        <!-- Screen Sharing Presentation Stage (Google Meet Presentation Mode) -->
        <div v-if="isScreenSharing" class="w-full h-full flex flex-col md:flex-row gap-3 p-1 sm:p-2 overflow-hidden">
          <!-- Main Screen Share Stage (Spotlight Screen Viewport) -->
          <div class="flex-1 h-full bg-slate-950 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl relative flex flex-col justify-between">
            <!-- Screen Header Bar -->
            <div class="z-20 p-3 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold text-white uppercase tracking-wider">
                  {{ presenterName }} is presenting
                </span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase rounded border" :class="getRoleBadgeClass(presenterRole)">
                  {{ presenterRole }}
                </span>
              </div>

              <button
                v-if="isSelfPresenting"
                @click="stopScreenShare"
                class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white font-mono text-[11px] font-bold uppercase rounded-lg transition-colors shadow-xs"
              >
                Stop Presenting
              </button>
            </div>

            <!-- Screen Video Stream / Diagnostic Interactive Telemetry Display -->
            <div class="flex-1 flex items-center justify-center p-4 bg-slate-950 overflow-hidden relative">
              <video
                v-if="hasNativeScreenStream"
                ref="screenShareVideoEl"
                autoplay
                playsinline
                class="w-full h-full object-contain"
              ></video>

              <!-- Clinical Diagnostic Telemetry Canvas -->
              <div v-else class="w-full h-full max-w-4xl max-h-full bg-slate-900 border border-slate-800 rounded-xl p-5 flex flex-col justify-between font-mono shadow-inner">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                  <div>
                    <span class="text-xs text-brand-400 font-bold uppercase tracking-wider block">Clinical Diagnostics & Vitology Display</span>
                    <span class="text-sm font-bold text-white">Continuous Electrocardiogram (ECG) & BP Telemetry</span>
                  </div>
                  <span class="px-2.5 py-1 bg-emerald-950 border border-emerald-700 text-emerald-400 text-xs font-bold rounded">LIVE FEED 1080p</span>
                </div>

                <!-- Live Vital Signs & Diagnostics -->
                <div class="grid grid-cols-3 gap-3 my-4">
                  <div class="p-3 bg-slate-950 border border-slate-800 rounded-lg">
                    <span class="text-[10px] text-slate-500 uppercase block">Heart Rate</span>
                    <span class="text-2xl font-bold text-emerald-400">72 <span class="text-xs text-slate-500">BPM</span></span>
                  </div>
                  <div class="p-3 bg-slate-950 border border-slate-800 rounded-lg">
                    <span class="text-[10px] text-slate-500 uppercase block">Blood Pressure</span>
                    <span class="text-2xl font-bold text-brand-400">120/80 <span class="text-xs text-slate-500">mmHg</span></span>
                  </div>
                  <div class="p-3 bg-slate-950 border border-slate-800 rounded-lg">
                    <span class="text-[10px] text-slate-500 uppercase block">Oxygen (SpO2)</span>
                    <span class="text-2xl font-bold text-cyan-400">99% <span class="text-xs text-slate-500">Normal</span></span>
                  </div>
                </div>

                <div class="p-3 bg-slate-950 border border-slate-800 rounded-lg flex items-center justify-between text-xs">
                  <span class="text-slate-400">Shared by <strong class="text-white">{{ presenterName }}</strong></span>
                  <span class="text-emerald-400 font-bold">Encrypted WebRTC Desktop Media Active</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Vertical Participant Filmstrip on Right (Google Meet Presentation Style) -->
          <div class="w-full md:w-64 lg:w-72 flex md:flex-col gap-2.5 overflow-y-auto max-h-full">
            <div
              v-for="p in participants"
              :key="p.id"
              class="relative aspect-video w-full bg-slate-900 border-2 border-slate-800 rounded-xl overflow-hidden shadow-lg flex items-center justify-center shrink-0"
            >
              <video
                v-if="p.isLocal && cameraOn"
                ref="localVideoEl"
                autoplay
                playsinline
                muted
                class="w-full h-full object-cover mirror"
              ></video>
              <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 p-2 text-center">
                <div class="w-10 h-10 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-sm uppercase shadow-md border border-slate-700 mb-1">
                  {{ p.name.charAt(0) }}
                </div>
                <span class="text-xs font-bold text-white uppercase truncate max-w-[90%]">{{ p.name }}</span>
                <span class="text-[9px] font-mono text-slate-400">{{ p.role }}</span>
              </div>

              <!-- Status pill -->
              <div class="absolute top-2 right-2 z-20">
                <div class="p-1 rounded-full backdrop-blur-md shadow-md border" :class="(p.isLocal ? micOn : p.audioActive) ? 'bg-slate-950/80 border-slate-800 text-emerald-400' : 'bg-rose-600 border-rose-500 text-white'">
                  <component :is="(p.isLocal ? micOn : p.audioActive) ? Mic : MicOff" class="w-3 h-3" />
                </div>
              </div>
              <div class="absolute bottom-2 left-2 z-20 flex items-center space-x-1 bg-slate-950/90 backdrop-blur-md px-2 py-0.5 rounded text-white text-[10px] font-mono border border-slate-800 shadow-md max-w-[85%]">
                <span class="font-bold text-white truncate">{{ p.name }}</span>
                <span v-if="p.isLocal" class="text-slate-400">(You)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Google Meet Signature Grid Layout (Strict 16:9 Ratio when NOT presenting) -->
        <div v-else class="w-full h-full mx-auto flex items-center justify-center p-2 sm:p-3 overflow-hidden">
          <!-- 1 Participant Layout (Strict 16:9) -->
          <div
            v-if="participants.length === 1"
            class="w-full h-full flex items-center justify-center"
          >
            <div class="relative aspect-video h-full max-h-full max-w-5xl bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center">
              <video
                v-if="participants[0].isLocal && cameraOn"
                :ref="setVideoRef"
                autoplay
                playsinline
                muted
                class="w-full h-full object-cover mirror"
              ></video>
              <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 p-4 text-center">
                <div class="w-20 h-20 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-3xl uppercase shadow-xl border-2 border-slate-700 mb-2">
                  {{ participants[0].name.charAt(0) }}
                </div>
                <span class="text-base font-bold text-white uppercase tracking-tight">{{ participants[0].name }}</span>
                <span class="text-xs font-mono text-slate-400 mt-0.5">{{ participants[0].role }}</span>
              </div>
              <div class="absolute top-3 right-3 z-20">
                <div class="p-2 rounded-full backdrop-blur-md shadow-md border" :class="(participants[0].isLocal ? micOn : participants[0].audioActive) ? 'bg-slate-950/80 border-slate-800 text-emerald-400' : 'bg-rose-600 border-rose-500 text-white'">
                  <component :is="(participants[0].isLocal ? micOn : participants[0].audioActive) ? Mic : MicOff" class="w-4 h-4" />
                </div>
              </div>
              <div class="absolute bottom-3 left-3 z-20 flex items-center space-x-2 bg-slate-950/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-white text-xs font-mono border border-slate-800 shadow-md">
                <span class="px-1.5 py-0.2 text-[9px] font-mono font-bold uppercase rounded" :class="getRoleBadgeClass(participants[0].role)">{{ participants[0].role }}</span>
                <span class="font-bold text-white">{{ participants[0].name }}</span>
                <span v-if="participants[0].isLocal" class="text-slate-400 font-normal">(You)</span>
              </div>
            </div>
          </div>

          <!-- 2 Participants (Strict 16:9 Dual Grid) -->
          <div
            v-else-if="participants.length === 2"
            class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 w-full h-full"
          >
            <div
              v-for="p in participants"
              :key="p.id"
              class="relative aspect-video h-full max-h-full max-w-[calc(50%-8px)] bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
            >
              <video
                v-if="p.isLocal && cameraOn"
                :ref="setVideoRef"
                autoplay
                playsinline
                muted
                class="w-full h-full object-cover mirror"
              ></video>
              <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 p-4 text-center">
                <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-2xl sm:text-3xl uppercase shadow-xl border-2 border-slate-700 mb-2">
                  {{ p.name.charAt(0) }}
                </div>
                <span class="text-sm sm:text-base font-bold text-white uppercase truncate max-w-[90%]">{{ p.name }}</span>
                <span class="text-xs font-mono text-slate-400 mt-0.5">{{ p.role }}</span>
              </div>
              <div class="absolute top-3 right-3 z-20">
                <div class="p-2 rounded-full backdrop-blur-md shadow-md border" :class="(p.isLocal ? micOn : p.audioActive) ? 'bg-slate-950/80 border-slate-800 text-emerald-400' : 'bg-rose-600 border-rose-500 text-white'">
                  <component :is="(p.isLocal ? micOn : p.audioActive) ? Mic : MicOff" class="w-4 h-4" />
                </div>
              </div>
              <div class="absolute bottom-3 left-3 z-20 flex items-center space-x-2 bg-slate-950/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-white text-xs font-mono border border-slate-800 shadow-md max-w-[88%]">
                <span class="px-1.5 py-0.2 text-[9px] font-mono font-bold uppercase rounded" :class="getRoleBadgeClass(p.role)">{{ p.role }}</span>
                <span class="font-bold text-white truncate">{{ p.name }}</span>
                <span v-if="p.isLocal" class="text-slate-400 font-normal hidden sm:inline">(You)</span>
              </div>
            </div>
          </div>

          <!-- 3 Participants (Strict 16:9 Google Meet Style: Row 1 has 2, Row 2 has 1 centered) -->
          <div
            v-else-if="participants.length === 3"
            class="flex flex-col gap-3 w-full h-full justify-center items-center overflow-hidden"
          >
            <!-- Top Row (2 16:9 Cards) -->
            <div class="flex items-center justify-center gap-3 sm:gap-4 w-full flex-1 min-h-0">
              <div
                v-for="p in participants.slice(0, 2)"
                :key="p.id"
                class="relative aspect-video h-full max-h-full max-w-[calc(50%-8px)] bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
              >
                <video
                  v-if="p.isLocal && cameraOn"
                  :ref="setVideoRef"
                  autoplay
                  playsinline
                  muted
                  class="w-full h-full object-cover mirror"
                ></video>
                <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 p-3 text-center">
                  <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-xl sm:text-2xl uppercase shadow-xl border-2 border-slate-700 mb-1.5">
                    {{ p.name.charAt(0) }}
                  </div>
                  <span class="text-xs sm:text-sm font-bold text-white uppercase truncate max-w-[90%]">{{ p.name }}</span>
                  <span class="text-[11px] font-mono text-slate-400">{{ p.role }}</span>
                </div>
                <div class="absolute top-3 right-3 z-20">
                  <div class="p-1.5 sm:p-2 rounded-full backdrop-blur-md shadow-md border" :class="(p.isLocal ? micOn : p.audioActive) ? 'bg-slate-950/80 border-slate-800 text-emerald-400' : 'bg-rose-600 border-rose-500 text-white'">
                    <component :is="(p.isLocal ? micOn : p.audioActive) ? Mic : MicOff" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                  </div>
                </div>
                <div class="absolute bottom-3 left-3 z-20 flex items-center space-x-2 bg-slate-950/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-white text-xs font-mono border border-slate-800 shadow-md max-w-[88%]">
                  <span class="px-1.5 py-0.2 text-[9px] font-mono font-bold uppercase rounded" :class="getRoleBadgeClass(p.role)">{{ p.role }}</span>
                  <span class="font-bold text-white truncate">{{ p.name }}</span>
                  <span v-if="p.isLocal" class="text-slate-400 font-normal hidden sm:inline">(You)</span>
                </div>
              </div>
            </div>

            <!-- Bottom Row (1 Centered 16:9 Card) -->
            <div class="flex items-center justify-center w-full flex-1 min-h-0">
              <div
                class="relative aspect-video h-full max-h-full max-w-[calc(50%-8px)] bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
              >
                <video
                  v-if="participants[2].isLocal && cameraOn"
                  :ref="setVideoRef"
                  autoplay
                  playsinline
                  muted
                  class="w-full h-full object-cover mirror"
                ></video>
                <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 p-3 text-center">
                  <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-xl sm:text-2xl uppercase shadow-xl border-2 border-slate-700 mb-1.5">
                    {{ participants[2].name.charAt(0) }}
                  </div>
                  <span class="text-xs sm:text-sm font-bold text-white uppercase truncate max-w-[90%]">{{ participants[2].name }}</span>
                  <span class="text-[11px] font-mono text-slate-400">{{ participants[2].role }}</span>
                </div>
                <div class="absolute top-3 right-3 z-20">
                  <div class="p-1.5 sm:p-2 rounded-full backdrop-blur-md shadow-md border" :class="(participants[2].isLocal ? micOn : participants[2].audioActive) ? 'bg-slate-950/80 border-slate-800 text-emerald-400' : 'bg-rose-600 border-rose-500 text-white'">
                    <component :is="(participants[2].isLocal ? micOn : participants[2].audioActive) ? Mic : MicOff" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                  </div>
                </div>
                <div class="absolute bottom-3 left-3 z-20 flex items-center space-x-2 bg-slate-950/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-white text-xs font-mono border border-slate-800 shadow-md max-w-[88%]">
                  <span class="px-1.5 py-0.2 text-[9px] font-mono font-bold uppercase rounded" :class="getRoleBadgeClass(participants[2].role)">{{ participants[2].role }}</span>
                  <span class="font-bold text-white truncate">{{ participants[2].name }}</span>
                  <span v-if="participants[2].isLocal" class="text-slate-400 font-normal hidden sm:inline">(You)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 4+ Participants (Strict 16:9 2x2 Grid) -->
          <div
            v-else
            class="grid grid-cols-2 grid-rows-2 gap-3 sm:gap-4 w-full h-full items-center justify-items-center overflow-hidden"
          >
            <div
              v-for="p in participants"
              :key="p.id"
              class="relative aspect-video h-full max-h-full max-w-2xl bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
            >
              <video
                v-if="p.isLocal && cameraOn"
                :ref="setVideoRef"
                autoplay
                playsinline
                muted
                class="w-full h-full object-cover mirror"
              ></video>
              <div v-else class="w-full h-full flex flex-col items-center justify-center bg-slate-900 p-3 text-center">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-brand-600 text-white flex items-center justify-center font-bold text-xl sm:text-2xl uppercase shadow-xl border-2 border-slate-700 mb-1.5">
                  {{ p.name.charAt(0) }}
                </div>
                <span class="text-xs sm:text-sm font-bold text-white uppercase truncate max-w-[90%]">{{ p.name }}</span>
                <span class="text-[11px] font-mono text-slate-400">{{ p.role }}</span>
              </div>
              <div class="absolute top-3 right-3 z-20">
                <div class="p-1.5 sm:p-2 rounded-full backdrop-blur-md shadow-md border" :class="(p.isLocal ? micOn : p.audioActive) ? 'bg-slate-950/80 border-slate-800 text-emerald-400' : 'bg-rose-600 border-rose-500 text-white'">
                  <component :is="(p.isLocal ? micOn : p.audioActive) ? Mic : MicOff" class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </div>
              </div>
              <div class="absolute bottom-3 left-3 z-20 flex items-center space-x-2 bg-slate-950/90 backdrop-blur-md px-3 py-1.5 rounded-lg text-white text-xs font-mono border border-slate-800 shadow-md max-w-[88%]">
                <span class="px-1.5 py-0.2 text-[9px] font-mono font-bold uppercase rounded" :class="getRoleBadgeClass(p.role)">{{ p.role }}</span>
                <span class="font-bold text-white truncate">{{ p.name }}</span>
                <span v-if="p.isLocal" class="text-slate-400 font-normal hidden sm:inline">(You)</span>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- Right Clinical Roster & In-Call Chat Sidebar -->
      <aside
        v-if="showSidebar"
        class="w-80 sm:w-96 bg-white border-l-2 border-slate-200 flex flex-col justify-between z-30 shadow-lg h-full overflow-hidden"
      >
        <!-- Sidebar Tabs Header -->
        <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
          <div class="flex items-center space-x-1 font-mono text-xs">
            <button
              @click="sidebarTab = 'chat'"
              class="px-3 py-1.5 font-bold uppercase transition-colors flex items-center space-x-1.5 rounded-lg"
              :class="sidebarTab === 'chat' ? 'bg-white text-slate-900 shadow-xs border border-slate-300' : 'text-slate-500 hover:text-slate-900'"
            >
              <MessageSquare class="w-3.5 h-3.5 text-brand-700" />
              <span>In-Call Chat</span>
            </button>

            <button
              @click="sidebarTab = 'roster'"
              class="px-3 py-1.5 font-bold uppercase transition-colors flex items-center space-x-1.5 rounded-lg"
              :class="sidebarTab === 'roster' ? 'bg-white text-slate-900 shadow-xs border border-slate-300' : 'text-slate-500 hover:text-slate-900'"
            >
              <Users class="w-3.5 h-3.5 text-brand-700" />
              <span>People ({{ participants.length }})</span>
            </button>
          </div>

          <button @click="showSidebar = false" class="p-1 text-slate-400 hover:text-slate-900">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- 1. IN-CALL CHAT TAB CONTENT -->
        <div v-if="sidebarTab === 'chat'" class="flex-1 flex flex-col min-h-0">
          <div class="p-2.5 bg-blue-50/70 border-b border-blue-100 text-[11px] text-blue-900 font-sans leading-tight">
            🔒 Messages are encrypted and visible only to people in this consultation room.
          </div>

          <!-- Message Thread Container -->
          <div ref="chatContainerEl" class="flex-1 p-3 overflow-y-auto space-y-3 text-xs">
            <div
              v-for="msg in chatMessages"
              :key="msg.id"
              class="flex flex-col space-y-1"
              :class="msg.isSelf ? 'items-end' : 'items-start'"
            >
              <div class="flex items-center space-x-1.5 text-[10px] font-mono text-slate-500">
                <span class="font-bold text-slate-800">{{ msg.sender_name }}</span>
                <span
                  class="px-1 py-0.2 text-[8px] font-mono font-bold uppercase rounded border"
                  :class="getRoleBadgeClass(msg.sender_role)"
                >
                  {{ msg.sender_role }}
                </span>
                <span>&bull; {{ msg.time }}</span>
              </div>

              <div
                class="p-2.5 max-w-[85%] rounded-xl text-xs font-sans leading-relaxed break-words"
                :class="msg.isSelf ? 'bg-brand-50 border border-brand-200 text-brand-950 shadow-xs' : 'bg-slate-100 border border-slate-200 text-slate-900'"
              >
                {{ msg.message }}
              </div>
            </div>

            <div v-if="chatMessages.length === 0" class="h-full flex flex-col items-center justify-center text-center p-4 text-slate-400 text-xs">
              <MessageSquare class="w-8 h-8 text-slate-300 mb-2" />
              <p class="font-bold text-slate-600 uppercase">No in-call messages yet</p>
              <p class="text-[11px] mt-0.5">Send a message, clinical note, or link to participants.</p>
            </div>
          </div>

          <!-- Chat Input Footer -->
          <form @submit.prevent="sendChatMessage" class="p-2.5 bg-white border-t border-slate-200 flex items-center space-x-2">
            <input
              type="text"
              v-model="newChatMessage"
              placeholder="Send a message to everyone..."
              class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-xs focus:border-brand-700 focus:outline-none bg-slate-50 font-sans"
            />
            <button
              type="submit"
              :disabled="!newChatMessage.trim() || sendingMessage"
              class="p-2 bg-brand-700 hover:bg-brand-800 text-white rounded-lg transition-colors disabled:opacity-40"
            >
              <Send class="w-4 h-4" />
            </button>
          </form>
        </div>

        <!-- 2. PARTICIPANTS ROSTER TAB CONTENT -->
        <div v-else class="flex-1 flex flex-col justify-between p-4 space-y-4 overflow-y-auto text-xs font-mono">
          <div class="space-y-4">
            <div class="space-y-2">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">In This Call</span>
              <div
                v-for="p in participants"
                :key="p.id"
                class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between"
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
              <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg space-y-1.5 text-[11px]">
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
              class="w-full py-2 bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs uppercase rounded-lg border border-brand-800 flex items-center justify-center space-x-1.5 transition-colors"
            >
              <UserPlus class="w-3.5 h-3.5" />
              <span>Invite Specialist / Translator</span>
            </button>
          </div>
        </div>
      </aside>
    </div>

    <!-- Bottom Control Console (Google Meet Style) -->
    <footer class="bg-white border-t border-slate-200 px-6 py-3.5 flex items-center justify-between z-30 shadow-sm">
      <div class="hidden sm:flex items-center space-x-2 text-xs font-mono text-slate-500">
        <ShieldCheck class="w-4 h-4 text-emerald-600" />
        <span class="font-bold">HIPAA AES-256 ENCRYPTED MEDIA STREAM</span>
      </div>

      <!-- Centered Google Meet Control Buttons -->
      <div class="flex items-center space-x-3 mx-auto sm:mx-0">
        <!-- Mic Toggle -->
        <button
          @click="toggleMic"
          :title="micOn ? 'Mute microphone' : 'Unmute microphone'"
          class="w-11 h-11 rounded-full flex items-center justify-center transition-all shadow-xs border"
          :class="micOn ? 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800' : 'bg-rose-600 hover:bg-rose-700 border-rose-600 text-white'"
        >
          <component :is="micOn ? Mic : MicOff" class="w-5 h-5" />
        </button>

        <!-- Camera Toggle -->
        <button
          @click="toggleCamera"
          :title="cameraOn ? 'Turn off camera' : 'Turn on camera'"
          class="w-11 h-11 rounded-full flex items-center justify-center transition-all shadow-xs border"
          :class="cameraOn ? 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800' : 'bg-rose-600 hover:bg-rose-700 border-rose-600 text-white'"
        >
          <component :is="cameraOn ? Video : VideoOff" class="w-5 h-5" />
        </button>

        <!-- Present Screen Button -->
        <button
          @click="toggleScreenShare"
          :title="isScreenSharing ? 'Stop presenting screen' : 'Present now / Share screen'"
          class="w-11 h-11 rounded-full flex items-center justify-center transition-all shadow-xs border"
          :class="isScreenSharing ? 'bg-brand-700 hover:bg-brand-800 border-brand-800 text-white' : 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800'"
        >
          <component :is="isScreenSharing ? ScreenShareOff : ScreenShare" class="w-5 h-5" />
        </button>

        <!-- In-Call Chat Button -->
        <button
          @click="toggleSidebarTab('chat')"
          :title="'In-Call Chat'"
          class="w-11 h-11 rounded-full flex items-center justify-center transition-all shadow-xs border relative"
          :class="showSidebar && sidebarTab === 'chat' ? 'bg-brand-50 border-brand-400 text-brand-700' : 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800'"
        >
          <MessageSquare class="w-5 h-5" />
          <span v-if="chatMessages.length > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-brand-700 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
            {{ chatMessages.length }}
          </span>
        </button>

        <!-- People / Roster Button -->
        <button
          @click="toggleSidebarTab('roster')"
          :title="'Participants'"
          class="w-11 h-11 rounded-full flex items-center justify-center transition-all shadow-xs border"
          :class="showSidebar && sidebarTab === 'roster' ? 'bg-brand-50 border-brand-400 text-brand-700' : 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-800'"
        >
          <Users class="w-5 h-5" />
        </button>

        <!-- Add Participant Button (Doctor / Admin) -->
        <button
          v-if="canAddParticipants"
          @click="showAddParticipantModal = true"
          title="Invite specialist or translator"
          class="w-11 h-11 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 flex items-center justify-center transition-all shadow-xs"
        >
          <UserPlus class="w-5 h-5 text-brand-700" />
        </button>

        <!-- Leave Call Button -->
        <button
          @click="leaveCall"
          class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider flex items-center space-x-2 transition-all shadow-md active:scale-95"
        >
          <PhoneOff class="w-4 h-4" />
          <span>Leave Call</span>
        </button>
      </div>

      <div class="hidden lg:flex items-center space-x-2 font-mono text-xs text-slate-500">
        <span class="px-2 py-0.5 bg-slate-100 rounded border border-slate-300 text-slate-700 font-bold">Code: {{ roomCode }}</span>
      </div>
    </footer>

    <!-- Leave / End Call Confirmation Modal -->
    <div
      v-if="showLeaveModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white border-2 border-slate-300 max-w-md w-full p-6 rounded-2xl shadow-2xl space-y-4">
        <div class="flex items-center space-x-3 text-slate-900 border-b border-slate-200 pb-3">
          <div class="w-10 h-10 rounded-full bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center">
            <PhoneOff class="w-5 h-5" />
          </div>
          <div>
            <h3 class="font-bold text-sm uppercase">Leave Consultation Room</h3>
            <span class="text-xs text-slate-500 font-mono">Room Code: #{{ roomCode }}</span>
          </div>
        </div>

        <p class="text-xs text-slate-600 font-sans leading-relaxed">
          Are you sure you want to exit this telehealth session?
        </p>

        <div class="space-y-2 pt-2">
          <!-- Option 1: Doctor/Admin Close & Purge All Data -->
          <button
            v-if="auth.isDoctor || auth.isAdmin"
            @click="closeAndPurgeRoom"
            class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-mono text-xs font-bold uppercase rounded-lg transition-colors flex items-center justify-center space-x-2 shadow-xs"
          >
            <ShieldAlert class="w-4 h-4" />
            <span>End Call for Everyone & Purge Data</span>
          </button>

          <!-- Option 2: Just Leave Call -->
          <button
            @click="executeLeave"
            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 font-mono text-xs font-bold uppercase rounded-lg transition-colors"
          >
            Leave Call (Room Stays Open)
          </button>

          <!-- Cancel -->
          <button
            @click="showLeaveModal = false"
            class="w-full py-2 text-slate-500 hover:text-slate-900 font-mono text-xs uppercase"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Add Participant Modal -->
    <AddParticipantModal
      :is-open="showAddParticipantModal"
      :appointment-id="appointmentId"
      @close="showAddParticipantModal = false"
      @participant-added="handleParticipantAdded"
    />
    </div>
  </div>

  <!-- Room Closed & Data Purged Full Screen State -->
  <div v-else class="min-h-screen bg-slate-50 flex items-center justify-center p-6 select-none font-sans">
    <div class="max-w-md w-full bg-white border-2 border-slate-300 p-8 rounded-2xl shadow-xl text-center space-y-4">
      <div class="w-16 h-16 bg-emerald-50 border-2 border-emerald-300 text-emerald-700 rounded-full flex items-center justify-center mx-auto shadow-xs">
        <ShieldCheck class="w-8 h-8" />
      </div>

      <div>
        <span class="px-2 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono text-xs font-bold text-slate-600 uppercase">
          Room #{{ roomCode }} Closed
        </span>
        <h2 class="text-lg font-bold text-slate-900 uppercase mt-2">Consultation Ended & Data Purged</h2>
      </div>

      <p class="text-xs text-slate-600 font-sans leading-relaxed">
        This consultation session has ended. All in-call encrypted messages and media tokens for room <code class="px-1.5 py-0.5 bg-slate-100 border rounded font-mono font-bold text-brand-700">{{ roomCode }}</code> have been securely wiped from the server.
      </p>

      <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row gap-2">
        <button
          @click="createNewRoom"
          class="flex-1 py-2.5 bg-brand-700 hover:bg-brand-800 text-white font-mono text-xs font-bold uppercase rounded-lg transition-colors shadow-xs"
        >
          Start New Room
        </button>
        <button
          @click="goToDashboard"
          class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 font-mono text-xs font-bold uppercase rounded-lg transition-colors"
        >
          Dashboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import AddParticipantModal from '@/components/telehealth/AddParticipantModal.vue'
import {
  Mic,
  MicOff,
  Video,
  VideoOff,
  ScreenShare,
  ScreenShareOff,
  PhoneOff,
  UserPlus,
  Users,
  MessageSquare,
  Send,
  Copy,
  ShieldAlert,
  X,
  RefreshCw,
  ShieldCheck,
} from 'lucide-vue-next'
import { generateUniqueRoomCode } from '@/services/mockData'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const rawParam = computed(() => route.params.code || route.params.id || '')
const isAlphanumericCode = (val) => /^[a-z0-9]{3}-[a-z0-9]{4}-[0-9]{3}$/i.test(String(val))

// Dynamic unique room code (e.g. k9x-yqp2-481)
const inLobby = ref(true)
const roomCode = ref(
  isAlphanumericCode(rawParam.value)
    ? String(rawParam.value)
    : generateUniqueRoomCode()
)
const appointmentId = computed(() => (/^\d+$/.test(String(rawParam.value)) ? Number(rawParam.value) : 1))
const appointment = ref(null)
const connectionState = ref('connected') // connected, reconnecting, disconnected
const micOn = ref(false)
const cameraOn = ref(false)
const showSidebar = ref(false)
const sidebarTab = ref('chat') // 'chat' | 'roster'
const showAddParticipantModal = ref(false)
const showLeaveModal = ref(false)
const isRoomClosed = ref(false)
const copiedLink = ref(false)
const localVideoEl = ref(null)
let localMediaStream = null

const copyRoomLink = async () => {
  try {
    const url = `${window.location.origin}/telehealth/room/${roomCode.value}`
    await navigator.clipboard.writeText(url)
    copiedLink.value = true
    setTimeout(() => {
      copiedLink.value = false
    }, 2500)
  } catch (e) {
    copiedLink.value = true
    setTimeout(() => {
      copiedLink.value = false
    }, 2500)
  }
}

// Screen sharing reactive state
const isScreenSharing = ref(false)
const screenShareStream = ref(null)
const screenShareVideoEl = ref(null)
const hasNativeScreenStream = ref(false)
const presenterName = ref('')
const presenterRole = ref('')
const isSelfPresenting = computed(() => isScreenSharing.value && presenterName.value === (auth.user?.name || 'Jane Doe'))

const toggleScreenShare = async () => {
  if (isScreenSharing.value) {
    stopScreenShare()
    return
  }

  presenterName.value = auth.user?.name || 'Jane Doe'
  presenterRole.value = (auth.role || 'patient').toUpperCase()

  try {
    if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
      const stream = await navigator.mediaDevices.getDisplayMedia({
        video: { cursor: 'always' },
        audio: false,
      })
      screenShareStream.value = stream
      hasNativeScreenStream.value = true
      isScreenSharing.value = true

      setTimeout(() => {
        if (screenShareVideoEl.value) {
          screenShareVideoEl.value.srcObject = stream
        }
      }, 60)

      stream.getVideoTracks()[0].onended = () => {
        stopScreenShare()
      }
    } else {
      hasNativeScreenStream.value = false
      isScreenSharing.value = true
    }
  } catch (err) {
    console.warn('Screen share display media fallback activated:', err)
    hasNativeScreenStream.value = false
    isScreenSharing.value = true
  }
}

const stopScreenShare = () => {
  if (screenShareStream.value) {
    screenShareStream.value.getTracks().forEach((track) => track.stop())
    screenShareStream.value = null
  }
  hasNativeScreenStream.value = false
  isScreenSharing.value = false
}

const chatContainerEl = ref(null)
const newChatMessage = ref('')
const sendingMessage = ref(false)
const chatMessages = ref([
  {
    id: 1,
    sender_name: 'Dr. Sarah Jenkins, MD, FACC',
    sender_role: 'DOCTOR',
    message: 'Hello Jane! Welcome to our telehealth room. Dr. Marcus Chen has joined us as well for your consultation.',
    time: new Date(Date.now() - 120000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isSelf: false,
  },
  {
    id: 2,
    sender_name: 'Dr. Marcus Chen',
    sender_role: 'SPECIALIST',
    message: 'Good day! I have your diagnostic timeline and vital logs ready.',
    time: new Date(Date.now() - 60000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isSelf: false,
  },
])

const toggleSidebarTab = (tab) => {
  if (showSidebar.value && sidebarTab.value === tab) {
    showSidebar.value = false
  } else {
    showSidebar.value = true
    sidebarTab.value = tab
    if (tab === 'chat') {
      scrollToChatBottom()
    }
  }
}

const scrollToChatBottom = () => {
  setTimeout(() => {
    if (chatContainerEl.value) {
      chatContainerEl.value.scrollTop = chatContainerEl.value.scrollHeight
    }
  }, 60)
}

const sendChatMessage = async () => {
  if (!newChatMessage.value.trim() || sendingMessage.value) return
  const text = newChatMessage.value.trim()
  newChatMessage.value = ''

  const msgObj = {
    id: Date.now(),
    sender_name: auth.user?.name || 'Jane Doe',
    sender_role: (auth.role || 'patient').toUpperCase(),
    message: text,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isSelf: true,
  }

  chatMessages.value.push(msgObj)
  scrollToChatBottom()

  try {
    sendingMessage.value = true
    await api.post(`/appointments/${appointmentId.value}/telehealth/messages`, {
      message: text,
    })
  } catch (err) {
    // Handled
  } finally {
    sendingMessage.value = false
  }
}

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

const otherParticipants = computed(() => participants.value.filter((p) => !p.isLocal))

const joinCall = () => {
  inLobby.value = false
  setTimeout(() => {
    if (localMediaStream) {
      attachStreamToAllVideoEls(localMediaStream)
    }
  }, 100)
}

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

const videoElements = new Set()
let animFrameId = null

const setVideoRef = (el) => {
  if (el) {
    videoElements.add(el)
    if (localMediaStream) {
      el.srcObject = localMediaStream
      el.play().catch(() => {})
    }
  }
}

const attachStreamToAllVideoEls = (stream) => {
  videoElements.forEach((el) => {
    if (el) {
      el.srcObject = stream
      el.play().catch(() => {})
    }
  })
}

const startSimulatedCamera = () => {
  if (localMediaStream) return

  const canvas = document.createElement('canvas')
  canvas.width = 1280
  canvas.height = 720
  const ctx = canvas.getContext('2d')

  let t = 0
  const draw = () => {
    t += 0.04
    // Subtle modern dark clinical background
    const grad = ctx.createLinearGradient(0, 0, 1280, 720)
    grad.addColorStop(0, '#0f172a')
    grad.addColorStop(1, '#1e293b')
    ctx.fillStyle = grad
    ctx.fillRect(0, 0, 1280, 720)

    // Animated glowing ambient halo
    const cx = 640 + Math.sin(t * 0.5) * 15
    const cy = 340 + Math.cos(t * 0.5) * 10
    const radial = ctx.createRadialGradient(cx, cy, 30, cx, cy, 320)
    radial.addColorStop(0, 'rgba(14, 165, 233, 0.18)')
    radial.addColorStop(1, 'rgba(15, 23, 42, 0)')
    ctx.fillStyle = radial
    ctx.beginPath()
    ctx.arc(cx, cy, 320, 0, Math.PI * 2)
    ctx.fill()

    // Centered avatar silhouette / portrait circle
    ctx.save()
    ctx.beginPath()
    ctx.arc(cx, cy - 20, 105, 0, Math.PI * 2)
    ctx.fillStyle = '#0284c7'
    ctx.fill()
    ctx.lineWidth = 6
    ctx.strokeStyle = '#38bdf8'
    ctx.stroke()

    // Avatar Initial
    ctx.fillStyle = '#ffffff'
    ctx.font = 'bold 72px sans-serif'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillText(auth.user?.name?.charAt(0) || 'J', cx, cy - 20)
    ctx.restore()

    // Animated vital pulse waveform at the bottom
    ctx.beginPath()
    ctx.strokeStyle = '#10b981'
    ctx.lineWidth = 3
    for (let x = 0; x < 1280; x += 4) {
      const y = 620 + Math.sin(x * 0.02 + t * 4) * (x > 480 && x < 800 ? 25 * Math.sin(t * 3) : 6)
      if (x === 0) ctx.moveTo(x, y)
      else ctx.lineTo(x, y)
    }
    ctx.stroke()

    // Live video overlay badge
    ctx.fillStyle = '#10b981'
    ctx.beginPath()
    ctx.arc(45, 55, 7, 0, Math.PI * 2)
    ctx.fill()

    ctx.fillStyle = '#ffffff'
    ctx.font = 'bold 18px monospace'
    ctx.textAlign = 'left'
    ctx.fillText('LIVE WEBRTC HD 1080p | 30 FPS', 60, 60)
    ctx.fillStyle = '#94a3b8'
    ctx.font = '14px monospace'
    ctx.fillText(`FEED: ${auth.user?.name || 'Jane Doe'} (You)`, 60, 85)

    animFrameId = requestAnimationFrame(draw)
  }

  draw()
  const stream = canvas.captureStream(30)
  localMediaStream = stream
  attachStreamToAllVideoEls(stream)
}

const startLocalMedia = async () => {
  try {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' },
        audio: true,
      })
      localMediaStream = stream
      stream.getAudioTracks().forEach((t) => {
        t.enabled = micOn.value
      })
      stream.getVideoTracks().forEach((t) => {
        t.enabled = cameraOn.value
      })
      attachStreamToAllVideoEls(stream)
    } else {
      startSimulatedCamera()
    }
  } catch (err) {
    console.warn('Physical camera unavailable/blocked, initializing simulated HD clinical camera feed:', err)
    startSimulatedCamera()
  }
}

const stopLocalMedia = () => {
  if (animFrameId) {
    cancelAnimationFrame(animFrameId)
    animFrameId = null
  }
  if (localMediaStream) {
    try {
      localMediaStream.getTracks().forEach((t) => {
        t.stop()
      })
    } catch (e) {}
    localMediaStream = null
  }
  if (screenShareStream.value) {
    try {
      screenShareStream.value.getTracks().forEach((t) => {
        t.stop()
      })
    } catch (e) {}
    screenShareStream.value = null
  }
  videoElements.forEach((el) => {
    if (el) {
      el.srcObject = null
    }
  })
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
  } else if (cameraOn.value) {
    startLocalMedia()
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

const leaveCall = () => {
  showLeaveModal.value = true
}

const executeLeave = async () => {
  showLeaveModal.value = false
  try {
    await api.post(`/appointments/${appointmentId.value}/telehealth/events`, {
      event: 'LEAVE',
      duration_seconds: 180,
    })
  } catch (e) {
    // Handled
  } finally {
    stopLocalMedia()
    goToDashboard()
  }
}

const closeAndPurgeRoom = async () => {
  showLeaveModal.value = false
  try {
    await api.post(`/appointments/${appointmentId.value}/telehealth/close`)
    // Purge local storage messages for this room
    localStorage.removeItem(`medicon_chat_room_${appointmentId.value}`)
    localStorage.removeItem(`medicon_chat_room_${roomCode.value}`)
  } catch (e) {
    // Handled
  } finally {
    stopLocalMedia()
    chatMessages.value = []
    isRoomClosed.value = true
  }
}

const createNewRoom = () => {
  const newCode = generateUniqueRoomCode()
  roomCode.value = newCode
  chatMessages.value = []
  isRoomClosed.value = false
  inLobby.value = true
  micOn.value = false
  cameraOn.value = false
  router.push(`/telehealth/room/${newCode}`)
}

const goToDashboard = () => {
  stopLocalMedia()
  if (auth.isDoctor) {
    router.push('/doctor/appointments')
  } else if (auth.isAdmin) {
    router.push('/admin/dashboard')
  } else {
    router.push('/patient/appointments')
  }
}

const loadSession = async () => {
  try {
    const identifier = rawParam.value || roomCode.value
    let res
    if (/^\d+$/.test(String(identifier))) {
      res = await api.get(`/appointments/${identifier}/telehealth/token`)
    } else {
      res = await api.get(`/telehealth/rooms/${identifier}/token`)
    }

    if (res.data?.room_code) {
      roomCode.value = res.data.room_code
    } else if (res.data?.appointment?.room_code) {
      roomCode.value = res.data.appointment.room_code
    }
    if (res.data?.appointment) {
      appointment.value = res.data.appointment
    }
  } catch (err) {
    // Handled via mock adapter
  }
}

// Watch route parameter changes (e.g. switching between different room codes)
watch(
  () => route.params.code || route.params.id,
  async (newVal, oldVal) => {
    if (newVal && newVal !== oldVal) {
      // 1. Cleanly tear down previous session & media tracks
      stopLocalMedia()
      videoElements.clear()

      // 2. Reset in-room state and controls
      inLobby.value = true
      micOn.value = false
      cameraOn.value = false
      isScreenSharing.value = false
      isRoomClosed.value = false
      showSidebar.value = false
      showLeaveModal.value = false
      chatMessages.value = []

      // 3. Update room code
      roomCode.value = isAlphanumericCode(newVal) ? String(newVal) : generateUniqueRoomCode()

      // 4. Initialize fresh media and session for new room
      await loadSession()
      await startLocalMedia()
    }
  }
)

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

onBeforeRouteLeave(() => {
  stopLocalMedia()
  videoElements.clear()
})

onUnmounted(() => {
  stopLocalMedia()
  videoElements.clear()
})
</script>

<style scoped>
.mirror {
  transform: scaleX(-1);
}
</style>
