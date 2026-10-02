<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Collection"
        title="Collection Reports"
        subtitle="Generate daily, monthly, or date-range reports"
        back-to="/modules/collection"
        back-label="Back to Collection"
      />

      <div class="max-w-5xl mx-auto px-6 py-10">
        <!-- Report controls (hidden when printing) -->
        <div class="panel no-print mb-6">
          <div class="flex flex-wrap items-end gap-3">
            <div>
              <label class="label">Report Type</label>
              <select v-model="form.type" class="input">
                <option value="daily">Daily</option>
                <option value="monthly">Monthly</option>
                <option value="range">Date Range</option>
              </select>
            </div>

            <div v-if="form.type === 'daily'">
              <label class="label">Date</label>
              <input v-model="form.date" type="date" class="input" />
            </div>

            <div v-if="form.type === 'monthly'">
              <label class="label">Month</label>
              <input v-model="form.month" type="month" class="input" />
            </div>

            <template v-if="form.type === 'range'">
              <div>
                <label class="label">From</label>
                <input v-model="form.from" type="date" class="input" />
              </div>
              <div>
                <label class="label">To</label>
                <input v-model="form.to" type="date" class="input" />
              </div>
            </template>

            <button class="btn-primary" @click="generate">Generate Report</button>
            <button v-if="results" class="btn-ghost" @click="print">Print</button>
          </div>
        </div>

        <!-- Report output -->
        <div v-if="results" class="report-sheet">
          <div class="report-head">
            <h2 class="report-title">Collection Report</h2>
            <p class="report-period">{{ period }}</p>
            <p class="report-org">Finance Service Unit 18 — Philippine National Police Academy</p>
          </div>

          <!-- Summary by account type -->
          <div v-if="accountTypeRows.length" class="summary">
            <h3 class="summary-title">Summary by Account Type</h3>
            <table class="w-full text-sm">
              <thead>
                <tr class="text-left border-b border-slate-300">
                  <th class="th">Account Type</th>
                  <th class="th text-right">Count</th>
                  <th class="th text-right">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in accountTypeRows" :key="row.name" class="border-b border-slate-100">
                  <td class="td">{{ row.name }}</td>
                  <td class="td text-right">{{ row.count }}</td>
                  <td class="td text-right font-semibold">{{ peso(row.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Detail -->
          <h3 class="summary-title mt-6">Transactions</h3>
          <table v-if="results.rows.length" class="w-full text-sm">
            <thead>
              <tr class="text-left border-b border-slate-300">
                <th class="th">Date</th>
                <th class="th">Payor</th>
                <th class="th">Account Type</th>
                <th class="th">OR #</th>
                <th class="th text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in results.rows" :key="t.id" class="border-b border-slate-100">
                <td class="td">{{ formatDate(t.transaction_date) }}</td>
                <td class="td">{{ t.payor_name }}</td>
                <td class="td">{{ t.account_type }}</td>
                <td class="td">{{ t.or_number }}</td>
                <td class="td text-right">{{ peso(t.amount) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="border-t-2 border-slate-400">
                <td class="td font-bold" colspan="4">TOTAL COLLECTIONS</td>
                <td class="td text-right font-extrabold">{{ peso(results.total) }}</td>
              </tr>
            </tfoot>
          </table>
          <p v-else class="empty">No transactions found for this period.</p>

          <!-- Footer: Prepared by + Time Stamp (Slide 17) -->
          <div class="report-foot">
            <div>
              <p class="foot-label">Prepared by</p>
              <p class="foot-value">{{ preparedBy }}</p>
            </div>
            <div class="text-right">
              <p class="foot-label">Time Stamp</p>
              <p class="foot-value">{{ formatDateTime(generatedAt) }}</p>
            </div>
          </div>
        </div>

        <div v-else class="placeholder no-print">
          <p>Choose a report type and generate to view collection totals.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

const props = defineProps({
  type: { type: String, default: null },
  period: { type: String, default: null },
  results: { type: Object, default: null },
  preparedBy: { type: String, default: '' },
  generatedAt: { type: String, default: '' },
  filters: { type: Object, default: () => ({}) },
})

const today = new Date().toISOString().slice(0, 10)
const form = reactive({
  type: props.type ?? 'daily',
  date: props.filters.date ?? today,
  month: props.filters.month ?? today.slice(0, 7),
  from: props.filters.from ?? today,
  to: props.filters.to ?? today,
})

const accountTypeRows = computed(() => {
  if (!props.results?.byAccountType) return []
  return Object.entries(props.results.byAccountType).map(([name, v]) => ({ name, count: v.count, total: v.total }))
})

const generate = () => {
  const query = { type: form.type }
  if (form.type === 'daily') query.date = form.date
  if (form.type === 'monthly') query.month = form.month
  if (form.type === 'range') {
    query.from = form.from
    query.to = form.to
  }
  router.get('/modules/collection/reports', query, { preserveState: true, preserveScroll: true })
}

const print = () => window.print()

const peso = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatDate = (d) => (d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '')
const formatDateTime = (d) => (d ? new Date(d.replace(' ', 'T')).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '')
</script>

<style scoped>
.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); }
.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.03em; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-size: 0.88rem; }
.input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15); }

.btn-primary { font-size: 0.85rem; font-weight: 700; color: #fff; background: linear-gradient(135deg, #0d9488, #14b8a6); padding: 0.55rem 1.2rem; border-radius: 0.6rem; box-shadow: 0 6px 16px rgba(13, 148, 136, 0.3); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; }
.btn-ghost:hover { background: #f1f5f9; }

.report-sheet { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07); }
.report-head { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 1rem; margin-bottom: 1.5rem; }
.report-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.report-period { font-size: 1rem; font-weight: 600; color: #0d9488; margin-top: 0.2rem; }
.report-org { font-size: 0.8rem; color: #64748b; margin-top: 0.3rem; }

.summary { margin-bottom: 1.5rem; }
.summary-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.6rem; }
.th { padding: 0.5rem 0.4rem; font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em; color: #334155; }
.td { padding: 0.5rem 0.4rem; color: #475569; }
.empty { padding: 2rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }

.report-foot { display: flex; justify-content: space-between; margin-top: 3rem; padding-top: 1.25rem; }
.foot-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; font-weight: 600; }
.foot-value { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-top: 0.6rem; border-top: 1px solid #94a3b8; padding-top: 0.3rem; min-width: 12rem; }

.placeholder { background: #fff; border: 1px dashed #cbd5e1; border-radius: 1rem; padding: 3rem; text-align: center; color: #64748b; font-size: 0.9rem; }

@media print {
  .no-print { display: none !important; }
  .report-sheet { box-shadow: none; border: none; padding: 0; }
}
</style>
