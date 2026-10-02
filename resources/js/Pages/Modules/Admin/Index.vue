<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Admin"
        title="Administrator"
        subtitle="Manage users, roles, and system access"
      />

      <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Role summary cards -->
        <div class="role-grid mb-10">
          <button
            v-for="role in roles"
            :key="role.id"
            class="role-card"
            :class="{ 'role-active': filters.role_id === role.id }"
            @click="filterByRole(role.id)"
          >
            <p class="role-name">{{ role.name }}</p>
            <p class="role-count">{{ role.users_count }}</p>
            <p class="role-label">users</p>
          </button>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-wrap items-end gap-3 mb-5">
          <div class="grow min-w-[220px]">
            <label class="label">Search</label>
            <input v-model="search" type="text" class="input" placeholder="Name, email, or designation" @keyup.enter="applySearch" />
          </div>
          <button class="btn-ghost" @click="applySearch">Search</button>
          <button v-if="filters.search || filters.role_id" class="btn-ghost" @click="clearFilters">Clear</button>
          <button class="btn-primary ml-auto" @click="openCreate">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 5v14M5 12h14" /></svg>
            Add User
          </button>
        </div>

        <!-- Users table -->
        <div class="panel">
          <div v-if="users.length === 0" class="empty">No users match your filters.</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="th">Name</th>
                <th class="th">Email</th>
                <th class="th">Designation</th>
                <th class="th">Role</th>
                <th class="th">Status</th>
                <th class="th text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in users" :key="u.id" class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="td font-medium text-slate-800">{{ u.name }}</td>
                <td class="td">{{ u.email }}</td>
                <td class="td">{{ u.designation || '—' }}</td>
                <td class="td"><span class="chip">{{ u.role?.name ?? '—' }}</span></td>
                <td class="td"><span class="status" :class="statusClass(u.status)">{{ cap(u.status) }}</span></td>
                <td class="td text-center whitespace-nowrap">
                  <button class="link" @click="openEdit(u)">Edit</button>
                  <button class="link ml-3" @click="openPassword(u)">Reset Password</button>
                  <button class="link-danger ml-3" @click="remove(u)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create/Edit user modal -->
      <UserFormModal
        :show="modal.open"
        :mode="modal.mode"
        :user="modal.user"
        :roles="roles"
        @close="modal.open = false"
      />

      <!-- Reset password modal -->
      <PasswordModal
        :show="pwModal.open"
        :user="pwModal.user"
        @close="pwModal.open = false"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'
import UserFormModal from './UserFormModal.vue'
import PasswordModal from './PasswordModal.vue'

const props = defineProps({
  users: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search ?? '')

const modal = reactive({ open: false, mode: 'create', user: null })
const pwModal = reactive({ open: false, user: null })

const openCreate = () => {
  modal.mode = 'create'
  modal.user = null
  modal.open = true
}
const openEdit = (user) => {
  modal.mode = 'edit'
  modal.user = user
  modal.open = true
}
const openPassword = (user) => {
  pwModal.user = user
  pwModal.open = true
}

const applySearch = () => {
  const query = {}
  if (search.value) query.search = search.value
  if (props.filters.role_id) query.role_id = props.filters.role_id
  router.get('/modules/admin', query, { preserveState: true, preserveScroll: true, replace: true })
}

const filterByRole = (roleId) => {
  const query = {}
  if (search.value) query.search = search.value
  // Toggle the role filter off if the same card is clicked again.
  if (props.filters.role_id !== roleId) query.role_id = roleId
  router.get('/modules/admin', query, { preserveState: true, preserveScroll: true, replace: true })
}

const clearFilters = () => {
  search.value = ''
  router.get('/modules/admin', {}, { preserveState: true, replace: true })
}

const remove = (user) => {
  if (!confirm(`Delete user "${user.name}"? This cannot be undone.`)) return
  router.delete(`/modules/admin/users/${user.id}`, { preserveScroll: true })
}

const cap = (s) => (s ? s.charAt(0).toUpperCase() + s.slice(1) : '—')
const statusClass = (s) => ({
  active: 'status-active',
  inactive: 'status-inactive',
  suspended: 'status-suspended',
}[s] || 'status-inactive')
</script>

<style scoped>
.role-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
@media (min-width: 768px) { .role-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
@media (min-width: 1024px) { .role-grid { grid-template-columns: repeat(7, minmax(0, 1fr)); } }
.role-card { text-align: left; background: #fff; border: 1px solid #eef0f3; border-radius: 0.9rem; padding: 0.9rem 1rem; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04); transition: all 0.15s ease; cursor: pointer; }
.role-card:hover { border-color: #9f1239; transform: translateY(-2px); }
.role-active { border-color: #9f1239; background: #fff1f2; }
.role-name { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: #9f1239; line-height: 1.2; min-height: 1.6rem; }
.role-count { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.role-label { font-size: 0.7rem; color: #94a3b8; }

.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.03em; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-size: 0.88rem; width: 100%; }
.input:focus { border-color: #9f1239; box-shadow: 0 0 0 3px rgba(159, 18, 57, 0.15); }

.btn-primary { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 700; color: #fff; background: linear-gradient(135deg, #9f1239, #6d1a1a); padding: 0.55rem 1.1rem; border-radius: 0.6rem; box-shadow: 0 6px 16px rgba(159, 18, 57, 0.3); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; transition: background 0.15s; }
.btn-ghost:hover { background: #f1f5f9; }

.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 0.5rem 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); overflow-x: auto; }
.empty { padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }
.th { padding: 0.75rem 0.5rem; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em; }
.td { padding: 0.75rem 0.5rem; color: #475569; }
.chip { font-size: 0.72rem; font-weight: 700; color: #9f1239; background: #ffe4e6; padding: 0.2rem 0.55rem; border-radius: 9999px; white-space: nowrap; }
.status { font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 9999px; }
.status-active { color: #15803d; background: #dcfce7; }
.status-inactive { color: #475569; background: #e2e8f0; }
.status-suspended { color: #b91c1c; background: #fee2e2; }
.link { color: #9f1239; font-weight: 600; }
.link:hover { text-decoration: underline; }
.link-danger { color: #dc2626; font-weight: 600; }
.link-danger:hover { text-decoration: underline; }
</style>
