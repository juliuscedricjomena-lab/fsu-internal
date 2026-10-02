<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Disbursement"
        title="Capital Outlay Particulars"
        subtitle="Project-level disbursement detail by month"
        back-to="/modules/disbursement"
        back-label="Back to Disbursement"
      />

      <div class="max-w-5xl mx-auto px-6 py-10">
        <!-- Controls -->
        <div class="panel no-print mb-6">
          <div class="flex flex-wrap items-end gap-3">
            <div>
              <label class="label">Year</label>
              <select v-model.number="year" class="input" @change="reload">
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
            <button class="btn-ghost ml-auto" @click="print">Print</button>
          </div>
        </div>

        <div class="report-sheet">
          <div class="report-head">
            <h2 class="report-title">Capital Outlay — Particulars</h2>
            <p class="report-period">For the year {{ year }}</p>
            <p class="report-org">Finance Service Unit 18 — Philippine National Police Academy</p>
          </div>

          <div v-if="months.length === 0" class="empty">
            No Capital Outlay disbursements recorded for {{ year }}.
          </div>

          <div v-for="month in months" :key="month" class="month-block">
            <h3 class="month-title">{{ month }}</h3>
            <table class="tbl">
              <thead>
                <tr>
                  <th class="th-left">Projects / Particulars</th>
                  <th class="th-left">Payee</th>
                  <th class="th-right">Amount</th>
                  <th class="th-left">Mode of Disbursement</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in byMonth[month].rows" :key="i">
                  <td class="td">{{ row.particular || '—' }}</td>
                  <td class="td">{{ row.payee }}</td>
                  <td class="td td-right">{{ money(row.amount) }}</td>
                  <td class="td">{{ row.mode || '—' }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="total-row">
                  <td class="td" colspan="2">TOTAL</td>
                  <td class="td td-right">{{ money(byMonth[month].total) }}</td>
                  <td class="td"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div v-if="months.length" class="grand">
            <span>GRAND TOTAL</span>
            <span>{{ money(grandTotal) }}</span>
          </div>

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
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

const props = defineProps({
  byMonth: { type: Object, default: () => ({}) },
  grandTotal: { type: Number, default: 0 },
  year: { type: Number, default: () => new Date().getFullYear() },
  preparedBy: { type: String, default: '' },
  generatedAt: { type: String, default: '' },
})

const year = ref(props.year)
const months = computed(() => Object.keys(props.byMonth))

const yearOptions = computed(() => {
  const current = new Date().getFullYear()
  return [current + 1, current, current - 1, current - 2]
})
const years = yearOptions

const reload = () => {
  router.get('/modules/disbursement/particulars', { year: year.value }, { preserveState: true, preserveScroll: true })
}

const print = () => window.print()

const money = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatDateTime = (d) => (d ? new Date(d.replace(' ', 'T')).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '')
</script>

<style scoped>
.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); }
.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.03em; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; color: #0f172a; font-size: 0.88rem; }
.input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15); }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; }
.btn-ghost:hover { background: #f1f5f9; }

.report-sheet { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 2.5rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07); }
.report-head { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 1rem; margin-bottom: 1.5rem; }
.report-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.report-period { font-size: 1rem; font-weight: 600; color: #7c3aed; margin-top: 0.2rem; }
.report-org { font-size: 0.8rem; color: #64748b; margin-top: 0.3rem; }
.empty { padding: 2.5rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }

.month-block { margin-bottom: 1.75rem; }
.month-title { font-size: 0.95rem; font-weight: 800; color: #7c3aed; margin-bottom: 0.5rem; }
.tbl { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.tbl th, .tbl td { border: 1px solid #e2e8f0; padding: 0.5rem 0.6rem; }
.th-left { text-align: left; background: #f8fafc; font-weight: 700; color: #334155; }
.th-right { text-align: right; background: #f8fafc; font-weight: 700; color: #334155; }
.td { color: #475569; }
.td-right { text-align: right; font-variant-numeric: tabular-nums; }
.total-row td { background: #f8fafc; font-weight: 700; color: #0f172a; }

.grand { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding: 0.75rem 1rem; background: #7c3aed; color: #fff; font-weight: 800; border-radius: 0.5rem; }

.report-foot { display: flex; justify-content: space-between; margin-top: 2.5rem; padding-top: 1.25rem; }
.foot-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; font-weight: 600; }
.foot-value { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-top: 0.6rem; border-top: 1px solid #94a3b8; padding-top: 0.3rem; min-width: 12rem; }

@media print {
  .no-print { display: none !important; }
  .report-sheet { box-shadow: none; border: none; padding: 0; }
}
</style>
