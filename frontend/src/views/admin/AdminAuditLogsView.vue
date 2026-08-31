<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Admin Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">Compliance</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">HIPAA Access Audit Trail</h1>
      </div>

      <div class="flex items-center space-x-1.5 text-xs font-mono text-emerald-800 bg-emerald-50 px-2.5 py-1 border border-emerald-300">
        <ShieldCheck class="w-3.5 h-3.5 text-emerald-700" />
        <span>IMMUTABLE APPEND-ONLY LOG</span>
      </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white border border-slate-300 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-slate-100 text-slate-600 uppercase font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-2.5">Log ID</th>
              <th class="px-4 py-2.5">User Initiator</th>
              <th class="px-4 py-2.5">Action Code</th>
              <th class="px-4 py-2.5">Entity Modified</th>
              <th class="px-4 py-2.5">IP Address</th>
              <th class="px-4 py-2.5">Timestamp</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-slate-50">
              <td class="px-4 py-3 font-bold text-slate-900">#{{ log.id }}</td>
              <td class="px-4 py-3 font-sans font-bold text-slate-900">{{ log.user_name }}</td>
              <td class="px-4 py-3">
                <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 text-[10px] uppercase font-bold">
                  {{ log.action }}
                </span>
              </td>
              <td class="px-4 py-3 text-slate-600">{{ log.entity_type }} #{{ log.entity_id }}</td>
              <td class="px-4 py-3 text-slate-500">{{ log.ip_address }}</td>
              <td class="px-4 py-3 text-slate-500">
                {{ log.created_at ? new Date(log.created_at).toLocaleString() : '' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import { ShieldCheck } from 'lucide-vue-next'

const adminStore = useAdminStore()
const auditLogs = computed(() => adminStore.auditLogs)

onMounted(() => {
  adminStore.fetchAuditLogs()
})
</script>
