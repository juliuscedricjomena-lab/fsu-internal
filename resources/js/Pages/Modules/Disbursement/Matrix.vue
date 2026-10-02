<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Disbursement"
        title="Budget Matrix"
        subtitle="RFU-18 Disbursements — monthly totals by expense class"
        back-to="/modules/disbursement"
        back-label="Back to Disbursement"
      />

      <div class="max-w-full mx-auto px-6 py-10">
        <!-- Controls -->
        <div class="panel no-print mb-6">
          <div class="flex flex-wrap items-end gap-3">
            <div>
              <label class="label">Fund Type</label>
              <select v-model="form.fund_type" class="input" @change="reload">
                <option v-for="(label, key) in fundTypes" :key="key" :value="key">{{ label }}</option>
              </select>
            </div>
            <div>
              <label class="label">Year</label>
              <select v-model.number="form.year" class="input" @change="reload">
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
            <button class="btn-ghost ml-auto" @click="print">Print</button>
          </div>
        </div>

        <!-- Matrix -->
        <div class="report-sheet">
          <div class="report-head">
            <h2 class="report-title">RFU-18 Disbursements</h2>
            <p class="report-period">{{ fundTypes[fundType] }} for the year {{ year }}</p>
            <p class="report-org">Finance Service Unit 18 — Philippine National Police Academy</p>
          </div>

          <div class="overflow-x-auto">
            <table class="matrix">
              <thead>
                <tr>
                  <th class="th-class">Expense Class</th>
                  <th v-for="m in monthLabels" :key="m" class="th-month">{{ m }}</th>
                  <th class="th-total">Total</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="group in groups" :key="group.code">
                  <tr class="group-row">
                    <td class="td-class" :colspan="14">{{ group.label }} ({{ group.code }})</td>
                  </tr>
                  <tr v-for="row in group.classes" :key="group.code + row.class">
                    <td class="td-class indent">{{ row.class }}</td>
                    <td v-for="m in 12" :key="m" class="td-num">{{ money(row.months[m]) }}</td>
                    <td class="td-num td-rowtotal">{{ money(row.total) }}</td>
                  </tr>
                  <tr class="subtotal-row">
                    <td class="td-class">SUB-TOTAL ({{ group.code }})</td>
                    <td v-for="m in 12" :key="m" class="td-num">{{ money(group.subtotal[m]) }}</td>
                    <td class="td-num">{{ money(group.subtotalSum) }}</td>
                  </tr>
                </template>
                <tr class="grand-row">
                  <td class="td-class">GRAND TOTAL</td>
                  <td v-for="m in 12" :key="m" class="td-num">{{ money(grand[m]) }}</td>
                  <td class="td-num">{{ money(grandTotal) }}</td>
                </tr>
              </tbody>
            </table>
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
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

const props = defineProps({
  groups: { type: Array, default: () => [] },
  grand: { type: Object, default: () => ({}) },
  grandTotal: { type: Number, default: 0 },
  fundType: { type: String, default: 'appropriated' },
  fundTypes: { type: Object, default: () => ({}) },
  year: { type: Number, default: () => new Date().getFullYear() },
  preparedBy: { type: String, default: '' },
  generatedAt: { type: String, default: '' },
})

const form = reactive({ fund_type: props.fundType, year: props.year })

const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

const years = computed(() => {
  const current = new Date().getFullYear()
  return [current + 1, current, current - 1, current - 2]
})

const reload = () => {
  router.get('/modules/disbursement/matrix', { fund_type: form.fund_type, year: form.year }, { preserveState: true, preserveScroll: true })
}

const print = () => window.print()

const money = (v) => {
  const n = Number(v || 0)
  return n === 0 ? '—' : n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
const formatDateTime = (d) => (d ? new Date(d.replace(' ', 'T')).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '')
</script>

<style scoped>
.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); }
.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.03em; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; color: #0f172a; font-size: 0.88rem; }
.input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15); }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; }
.btn-ghost:hover { background: #f1f5f9; }

.report-sheet { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 2rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07); }
.report-head { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 1rem; margin-bottom: 1.5rem; }
.report-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
.report-period { font-size: 1rem; font-weight: 600; color: #7c3aed; margin-top: 0.2rem; }
.report-org { font-size: 0.8rem; color: #64748b; margin-top: 0.3rem; }

.matrix { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.matrix th, .matrix td { border: 1px solid #e2e8f0; padding: 0.4rem 0.5rem; }
.th-class { text-align: left; background: #f8fafc; font-weight: 700; min-width: 15rem; position: sticky; left: 0; }
.th-month { text-align: right; background: #f8fafc; font-weight: 700; min-width: 5.5rem; }
.th-total { text-align: right; background: #ede9fe; font-weight: 800; min-width: 6.5rem; }
.td-class { text-align: left; color: #334155; }
.td-class.indent { padding-left: 1.25rem; color: #475569; }
.td-num { text-align: right; color: #475569; font-variant-numeric: tabular-nums; }
.td-rowtotal { font-weight: 700; color: #0f172a; background: #faf5ff; }
.group-row td { background: #f1f5f9; font-weight: 800; color: #0f172a; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.03em; }
.subtotal-row td { background: #f8fafc; font-weight: 700; color: #0f172a; }
.grand-row td { background: #7c3aed; color: #fff; font-weight: 800; }

.report-foot { display: flex; justify-content: space-between; margin-top: 2.5rem; padding-top: 1.25rem; }
.foot-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; font-weight: 600; }
.foot-value { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-top: 0.6rem; border-top: 1px solid #94a3b8; padding-top: 0.3rem; min-width: 12rem; }

@media print {
  .no-print { display: none !important; }
  .report-sheet { box-shadow: none; border: none; padding: 0; }
  .matrix { font-size: 0.6rem; }
}
</style>
