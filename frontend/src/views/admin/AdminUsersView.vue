<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="bg-white border border-slate-300 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center space-x-2 text-[11px] font-mono text-slate-500 uppercase">
          <span>Admin Portal</span>
          <span>/</span>
          <span class="font-bold text-slate-900">User Registry</span>
        </div>
        <h1 class="text-xl font-bold uppercase tracking-tight text-slate-950 mt-0.5">User Directory & RBAC Permissions</h1>
      </div>

      <div class="relative w-full sm:w-64">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Filter by name or email..."
          class="w-full pl-9 pr-3 py-1.5 border border-slate-300 text-xs focus:border-slate-800 bg-white rounded-none font-mono"
        />
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-300 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-slate-100 text-slate-600 uppercase font-bold border-b border-slate-200">
            <tr>
              <th class="px-4 py-2.5">User Details</th>
              <th class="px-4 py-2.5">Assigned Role</th>
              <th class="px-4 py-2.5">Status</th>
              <th class="px-4 py-2.5">Created Date</th>
              <th class="px-4 py-2.5 text-right">RBAC Role Modification</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-slate-50">
              <td class="px-4 py-3">
                <div class="font-bold font-sans text-slate-950 text-sm uppercase">{{ user.name }}</div>
                <div class="text-slate-400 text-[11px]">{{ user.email }}</div>
              </td>
              <td class="px-4 py-3">
                <Badge :variant="user.role">{{ user.role }}</Badge>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] uppercase font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                  Active
                </span>
              </td>
              <td class="px-4 py-3 text-slate-500">
                {{ user.created_at }}
              </td>
              <td class="px-4 py-3 text-right">
                <select
                  :value="user.role"
                  @change="handleRoleChange(user, $event.target.value)"
                  class="px-2 py-1 border border-slate-300 bg-white text-xs uppercase font-mono"
                >
                  <option value="patient">patient</option>
                  <option value="doctor">doctor</option>
                  <option value="admin">admin</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import Badge from '@/components/common/Badge.vue'
import { Search } from 'lucide-vue-next'

const adminStore = useAdminStore()
const searchQuery = ref('')

const users = computed(() => adminStore.users)

const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const q = searchQuery.value.toLowerCase()
  return users.value.filter((u) => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
})

const handleRoleChange = async (user, newRole) => {
  await adminStore.updateUserRole(user.id, newRole)
}

onMounted(() => {
  adminStore.fetchUsers()
})
</script>
