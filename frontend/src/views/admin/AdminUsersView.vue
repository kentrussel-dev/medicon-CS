<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-black text-slate-900">User Account & Permission Management</h2>
        <p class="text-xs text-slate-500 mt-0.5">Role management, practitioner license verification, and account activation</p>
      </div>

      <button
        @click="showUserModal = true"
        class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition-all shadow-xs flex items-center space-x-1.5"
      >
        <UserPlus class="w-4 h-4" />
        <span>Provision User</span>
      </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-100">
            <tr>
              <th class="px-6 py-4">User</th>
              <th class="px-6 py-4">Assigned Role</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Contact Phone</th>
              <th class="px-6 py-4">Registered Date</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
            <tr v-for="user in usersList" :key="user.id" class="hover:bg-slate-50/50">
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <img
                    :src="user.avatar_url || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'"
                    alt="Avatar"
                    class="w-8 h-8 rounded-full object-cover border border-slate-200"
                  />
                  <div>
                    <div class="font-bold text-slate-900 text-sm">{{ user.name }}</div>
                    <div class="text-slate-400 text-[11px]">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <Badge :variant="user.role">{{ user.role }}</Badge>
              </td>
              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold"
                  :class="user.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                >
                  {{ user.is_active ? 'Active' : 'Deactivated' }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-600">{{ user.phone || 'N/A' }}</td>
              <td class="px-6 py-4 text-slate-400">{{ user.created_at?.split('T')[0] || 'Recent' }}</td>
              <td class="px-6 py-4 text-right">
                <button
                  @click="toggleStatus(user.id)"
                  class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-colors"
                  :class="user.is_active ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50'"
                >
                  {{ user.is_active ? 'Deactivate' : 'Reactivate' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Provision Modal -->
    <UserModal :is-open="showUserModal" @close="showUserModal = false" @created="loadUsers" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import Badge from '@/components/common/Badge.vue'
import UserModal from '@/components/admin/UserModal.vue'
import { UserPlus } from 'lucide-vue-next'

const adminStore = useAdminStore()
const showUserModal = ref(false)

const usersList = computed(() => {
  if (adminStore.users?.length) return adminStore.users
  return [
    { id: 1, name: 'Dr. Eleanor Vance', email: 'admin@medicon.health', role: 'admin', is_active: true, phone: '+1 555-0100', created_at: '2026-01-01' },
    { id: 2, name: 'Dr. Sarah Jenkins', email: 'sarah.jenkins@medicon.health', role: 'doctor', is_active: true, phone: '+1 555-0101', created_at: '2026-01-02' },
    { id: 3, name: 'Dr. Marcus Chen', email: 'marcus.chen@medicon.health', role: 'doctor', is_active: true, phone: '+1 555-0102', created_at: '2026-01-02' },
    { id: 4, name: 'John Doe', email: 'patient@medicon.health', role: 'patient', is_active: true, phone: '+1 555-0103', created_at: '2026-01-05' },
  ]
})

const toggleStatus = async (id) => {
  await adminStore.toggleUserStatus(id)
}

const loadUsers = async () => {
  await adminStore.fetchUsers()
}

onMounted(() => {
  loadUsers()
})
</script>
