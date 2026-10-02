<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Disbursement"
        title="Disbursement Records"
        subtitle="Browse and search disbursement records"
        back-to="/modules/disbursement"
        back-label="Back to Disbursement"
      />

      <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-end gap-3 mb-5">
          <div class="grow min-w-[200px]">
            <label class="label">Search</label>
            <input v-model="local.search" type="text" class="input" placeholder="Payee, reference, or purpose" @keyup.enter="applyFilters" />
          </div>
          <div>
            <label class="label">Fund Type</label>
            <select v-model="local.fund_type" class="input">
              <option value="">All</option>
              <option v-for="(label, key) in fundTypes" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="label">Group</label>
            <select v-model="local.expense_group" class="input">
              <option value="">All</option>
              <option v-for="(label, key) in expenseGroups" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="label">From</label>
            <input v-model="local.from" type="date" class="input" />
          </div>
          <div>
            <label class="label">To</label>
            <input v-model="local.to" type="date" class="input" />
          </div>
          <button class="btn-primary" @click="applyFilters">Filter</button>
          <button v-if="hasFilters" class="btn-ghost" @click="clearFilters">Clear</button>
          <Link href="/modules/disbursement/records/create" class="btn-primary ml-auto">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 5v14M5 12h14" /></svg>
            New Disbursement
          </Link>
        </div>

        <!-- Table -->
        <div class="panel">
          <div v-if="records.data.length === 0" class="empty">
            No records match your filters.
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="th">Date</th>
                <th class="th">Payee</th>
                <th class="th">Expense Class</th>
                <th class="th">Particular</th>
                <th class="th">Reference #</th>
                <th class="th">Mode</th>
                <th class="th text-right">Amount</th>
                <th class="th text-center">Doc</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in records.data" :key="r.id" class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="td">{{ formatDate(r.disbursement_date) }}</td>
                <td class="td font-medium text-slate-800">{{ r.payee }}</td>
                <td class="td"><span class="chip">{{ r.expense_class }}</span></td>
                <td class="td max-w-xs truncate" :title="r.project_particular">{{ r.project_particular || '—' }}</td>
                <td class="td">{{ r.reference_no }}</td>
                <td class="td">{{ r.mode_of_disbursement || '—' }}</td>
                <td class="td text-right font-semibold">{{ peso(r.amount) }}</td>
                <td class="td text-center">
                  <a v-if="r.attachment_path" :href="`/modules/disbursement/records/${r.id}/attachment`" target="_blank" class="link">View</a>
                  <span v-else class="text-slate-300">—</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="records.links && records.data.length" class="flex flex-wrap gap-1 mt-5 justify-center">
          <Link
            v-for="(link, i) in records.links"
            :key="i"
            :href="link.url || ''"
            class="page-link"
            :class="{ 'page-active': link.active, 'page-disabled': !link.url }"
            v-html="link.label"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

const props = defineProps({
  records: { type: Object, required: true },
  fundTypes: { type: Object, default: () => ({}) },
  expenseGroups: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
})

const local = reactive({
  search: props.filters.search ?? '',
  fund_type: props.filters.fund_type ?? '',
  expense_group: props.filters.expense_group ?? '',
  from: props.filters.from ?? '',
  to: props.filters.to ?? '',
})

const hasFilters = computed(() => local.search || local.fund_type || local.expense_group || local.from || local.to)

const applyFilters = () => {
  const query = Object.fromEntries(Object.entries(local).filter(([, v]) => v))
  router.get('/modules/disbursement/records', query, { preserveState: true, preserveScroll: true, replace: true })
}

const clearFilters = () => {
  local.search = ''
  local.fund_type = ''
  local.expense_group = ''
  local.from = ''
  local.to = ''
  router.get('/modules/disbursement/records', {}, { preserveState: true, replace: true })
}

const peso = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatDate = (d) => (d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '')
</script>

<style scoped>
.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.03em; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-size: 0.88rem; }
.input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15); }

.btn-primary { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 700; color: #fff; background: linear-gradient(135deg, #7c3aed, #a855f7); padding: 0.55rem 1.1rem; border-radius: 0.6rem; box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; transition: background 0.15s; }
.btn-ghost:hover { background: #f1f5f9; }

.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 0.5rem 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); overflow-x: auto; }
.empty { padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }
.th { padding: 0.75rem 0.5rem; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em; }
.td { padding: 0.75rem 0.5rem; color: #475569; }
.chip { font-size: 0.72rem; font-weight: 700; color: #6b21a8; background: #f3e8ff; padding: 0.2rem 0.55rem; border-radius: 9999px; white-space: nowrap; }
.link { color: #7c3aed; font-weight: 600; }
.link:hover { text-decoration: underline; }

.page-link { min-width: 2rem; text-align: center; padding: 0.4rem 0.6rem; font-size: 0.8rem; border: 1px solid #e2e8f0; border-radius: 0.5rem; color: #475569; background: #fff; }
.page-link:hover { background: #f1f5f9; }
.page-active { background: #7c3aed; border-color: #7c3aed; color: #fff; }
.page-disabled { opacity: 0.4; pointer-events: none; }
</style>
