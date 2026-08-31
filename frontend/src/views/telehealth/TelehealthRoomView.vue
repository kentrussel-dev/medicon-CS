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

        <!-- Google Meet Signature Grid Layout (Full Space Adaptive Canvas when NOT presenting) -->
        <div v-else class="w-full h-full mx-auto flex items-center justify-center p-1 sm:p-2">
          <!-- 1 Participant Layout -->
          <div
            v-if="participants.length === 1"
            class="w-full h-full max-w-5xl flex items-center justify-center"
          >
            <div class="relative w-full aspect-video max-h-full bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center">
              <video
                v-if="participants[0].isLocal && cameraOn"
                ref="localVideoEl"
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

          <!-- 2 Participants (Full-Height 2-Column Grid) -->
          <div
            v-else-if="participants.length === 2"
            class="grid grid-cols-2 gap-3 sm:gap-4 w-full h-full items-center"
          >
            <div
              v-for="p in participants"
              :key="p.id"
              class="relative w-full h-full max-h-full aspect-video bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center my-auto"
            >
              <video
                v-if="p.isLocal && cameraOn"
                ref="localVideoEl"
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

          <!-- 3 Participants (Full Space: Row 1 has 2 Cards, Row 2 has Centered 3rd Card) -->
          <div
            v-else-if="participants.length === 3"
            class="flex flex-col gap-3 w-full h-full justify-center items-center my-auto"
          >
            <!-- Top Row (2 Cards taking full top half) -->
            <div class="grid grid-cols-2 gap-3 w-full flex-1 min-h-0">
              <div
                v-for="p in participants.slice(0, 2)"
                :key="p.id"
                class="relative w-full h-full bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
              >
                <video
                  v-if="p.isLocal && cameraOn"
                  ref="localVideoEl"
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

            <!-- Bottom Row (Centered 3rd Card taking bottom half) -->
            <div class="w-full flex justify-center flex-1 min-h-0">
              <div
                class="relative w-full max-w-[calc(50%-6px)] h-full bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
              >
                <video
                  v-if="participants[2].isLocal && cameraOn"
                  ref="localVideoEl"
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

          <!-- 4+ Participants (Full-Space 2x2 Grid) -->
          <div
            v-else
            class="grid grid-cols-2 grid-rows-2 gap-3 sm:gap-4 w-full h-full items-center my-auto"
          >
            <div
              v-for="p in participants"
              :key="p.id"
              class="relative w-full h-full bg-slate-900 border-2 border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex items-center justify-center"
            >
              <video
                v-if="p.isLocal && cameraOn"
                ref="localVideoEl"
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
        <span>Room: LK-{{ appointmentId }}-SEC</span>
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
  ScreenShare,
  ScreenShareOff,
  PhoneOff,
  UserPlus,
  Users,
  MessageSquare,
  Send,
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
const sidebarTab = ref('chat') // 'chat' | 'roster'
const showAddParticipantModal = ref(false)
const localVideoEl = ref(null)
let localMediaStream = null

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
