<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Disbursement"
        title="Disbursement System"
        subtitle="Manage fund disbursements and payments"
      />

      <div class="max-w-7xl mx-auto px-6 py-10">
        <!-- Stat tiles -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
          <div class="stat">
            <p class="stat-label">Disbursed This Year</p>
            <p class="stat-value">{{ peso(stats.year) }}</p>
          </div>
          <div class="stat">
            <p class="stat-label">This Month</p>
            <p class="stat-value">{{ peso(stats.month) }}</p>
          </div>
          <div class="stat">
            <p class="stat-label">Total Records</p>
            <p class="stat-value">{{ stats.count }}</p>
          </div>
        </div>

        <!-- Main navigation: Records + Budget Matrix + Particulars -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          <Link href="/modules/disbursement/records" class="nav-card nav-records">
            <div class="nav-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18" /></svg>
            </div>
            <div>
              <h3 class="nav-title">Records</h3>
              <p class="nav-desc">Record and browse disbursements</p>
            </div>
          </Link>

          <Link href="/modules/disbursement/matrix" class="nav-card nav-matrix">
            <div class="nav-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M3 9h18M3 15h18M9 3v18M15 3v18" /></svg>
            </div>
            <div>
              <h3 class="nav-title">Budget Matrix</h3>
              <p class="nav-desc">Monthly totals by expense class</p>
            </div>
          </Link>

          <Link href="/modules/disbursement/particulars" class="nav-card nav-particulars">
            <div class="nav-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6M8 13h8M8 17h8" /></svg>
            </div>
            <div>
              <h3 class="nav-title">CO Particulars</h3>
              <p class="nav-desc">Capital Outlay project detail</p>
            </div>
          </Link>
        </div>

        <!-- Recent records -->
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold text-slate-900">Recent Disbursements</h2>
          <Link href="/modules/disbursement/records/create" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 5v14M5 12h14" /></svg>
            New Disbursement
          </Link>
        </div>

        <div class="panel">
          <div v-if="recent.length === 0" class="empty">
            No disbursement records yet. Create your first record to get started.
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="th">Date</th>
                <th class="th">Payee</th>
                <th class="th">Expense Class</th>
                <th class="th">Reference #</th>
                <th class="th text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in recent" :key="r.id" class="border-b border-slate-100 last:border-0">
                <td class="td">{{ formatDate(r.disbursement_date) }}</td>
                <td class="td font-medium text-slate-800">{{ r.payee }}</td>
                <td class="td"><span class="chip">{{ r.expense_class }}</span></td>
                <td class="td">{{ r.reference_no }}</td>
                <td class="td text-right font-semibold">{{ peso(r.amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

defineProps({
  recent: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ today: 0, month: 0, count: 0 }) },
})

const peso = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatDate = (d) => (d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '')
</script>

<style scoped>
.stat { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.1rem 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); }
.stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; font-weight: 700; }
.stat-value { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem; }

.nav-card { position: relative; display: flex; align-items: center; gap: 1rem; background: #fff; border: 1px solid #eef0f3; border-radius: 1.1rem; padding: 1.5rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05); transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease; text-decoration: none; }
.nav-card:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(124, 58, 237, 0.18); border-color: #7c3aed; }
.nav-icon { display: inline-flex; align-items: center; justify-content: center; height: 3.2rem; width: 3.2rem; border-radius: 1rem; color: #fff; flex-shrink: 0; }
.nav-icon svg { height: 1.6rem; width: 1.6rem; }
.nav-records .nav-icon { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.nav-matrix .nav-icon { background: linear-gradient(135deg, #4f46e5, #6366f1); }
.nav-particulars .nav-icon { background: linear-gradient(135deg, #d97706, #f59e0b); }
.nav-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; }
.nav-desc { font-size: 0.82rem; color: #64748b; }

.btn-primary { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 700; color: #fff; background: linear-gradient(135deg, #7c3aed, #a855f7); padding: 0.55rem 1rem; border-radius: 0.6rem; box-shadow: 0 6px 16px rgba(124, 58, 237, 0.35); transition: opacity 0.15s ease; }
.btn-primary:hover { opacity: 0.92; }

.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 0.5rem 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); overflow-x: auto; }
.empty { padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }
.th { padding: 0.75rem 0.5rem; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; }
.td { padding: 0.75rem 0.5rem; color: #475569; }
.chip { font-size: 0.72rem; font-weight: 700; color: #6b21a8; background: #f3e8ff; padding: 0.2rem 0.55rem; border-radius: 9999px; }
</style>
