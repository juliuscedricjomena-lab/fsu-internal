<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Pay & Allowances"
        title="Pay & Allowances"
        subtitle="Upload payslips, filter by month, and view computed pay"
      />

      <div class="max-w-6xl mx-auto px-6 py-10">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-end gap-3 mb-6">
          <div>
            <label class="label">Monthly Filter</label>
            <input v-model="localMonth" type="month" class="input" @change="applyMonth" />
          </div>
          <button v-if="month" class="btn-ghost" @click="clearMonth">Show All</button>
          <div class="stat-pill">
            <span class="stat-pill-label">Payslips</span>
            <span class="stat-pill-value">{{ counts.total }}</span>
          </div>
          <div class="stat-pill">
            <span class="stat-pill-label">Computed Total</span>
            <span class="stat-pill-value">{{ peso(counts.computedTotal) }}</span>
          </div>
          <Link href="/modules/pay-allowances/create" class="btn-primary ml-auto">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 5v14M5 12h14" /></svg>
            Upload Payslip
          </Link>
        </div>

        <!-- Status tabs -->
        <div class="tabs">
          <button
            v-for="s in statuses"
            :key="s"
            class="tab"
            :class="{ 'tab-active': activeTab === s }"
            @click="activeTab = s"
          >
            {{ label(s) }}
            <span class="tab-count">{{ grouped[s]?.length ?? 0 }}</span>
          </button>
        </div>

        <!-- Files for active tab -->
        <div class="panel">
          <div v-if="(grouped[activeTab]?.length ?? 0) === 0" class="empty">
            No {{ label(activeTab) }} files for {{ month ? monthLabel : 'any period' }}.
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="th">Employee</th>
                <th class="th">Period</th>
                <th class="th text-right">Total</th>
                <th class="th text-center">Days (W/P/A)</th>
                <th class="th text-right">Computed</th>
                <th class="th">Uploaded By</th>
                <th class="th text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in grouped[activeTab]" :key="p.id" class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="td font-medium text-slate-800">{{ p.employee_name }}</td>
                <td class="td">{{ formatMonth(p.period_month) }}</td>
                <td class="td text-right">{{ peso(p.total_amount) }}</td>
                <td class="td text-center text-slate-500">{{ p.working_days ?? '—' }} / {{ p.days_present ?? '—' }} / {{ p.days_absent ?? '—' }}</td>
                <td class="td text-right font-semibold text-emerald-700">{{ peso(p.computed_amount ?? p.total_amount) }}</td>
                <td class="td">{{ p.uploader?.name ?? '—' }}</td>
                <td class="td">
                  <div class="action-group">
                    <button class="icon-btn icon-preview" title="Preview" @click="openPreview(p)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" /></svg>
                    </button>
                    <a :href="`/modules/pay-allowances/${p.id}/download`" class="icon-btn icon-download" title="Download">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" /></svg>
                    </a>
                    <button class="icon-btn icon-discard" title="Discard" @click="remove(p)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" /><path d="M10 11v6M14 11v6" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Preview modal -->
      <Teleport to="body">
        <div v-if="preview.open" class="preview-backdrop" @click.self="closePreview">
          <div class="preview-dialog">
            <div class="preview-head">
              <div class="min-w-0">
                <p class="preview-name">{{ preview.file?.employee_name }}</p>
                <p class="preview-meta">{{ preview.file?.status }} • {{ formatMonth(preview.file?.period_month) }}</p>
              </div>
              <div class="flex items-center gap-2">
                <a :href="`/modules/pay-allowances/${preview.file?.id}/download`" class="preview-dl" title="Download">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" /></svg>
                </a>
                <button class="preview-close" @click="closePreview" title="Close">✕</button>
              </div>
            </div>
            <div class="preview-body">
              <iframe :src="previewUrl" class="preview-frame" title="Payslip preview"></iframe>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

const props = defineProps({
  payslips: { type: Array, default: () => [] },
  grouped: { type: Object, default: () => ({}) },
  statuses: { type: Array, default: () => [] },
  month: { type: String, default: '' },
  counts: { type: Object, default: () => ({ total: 0, computedTotal: 0 }) },
})

const localMonth = ref(props.month)
const activeTab = ref(props.statuses[0] ?? 'PAYSLIP')

const monthLabel = computed(() => {
  if (!props.month) return ''
  const [y, m] = props.month.split('-')
  return new Date(Number(y), Number(m) - 1, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const applyMonth = () => {
  router.get('/modules/pay-allowances', localMonth.value ? { month: localMonth.value } : {}, { preserveState: true, preserveScroll: true, replace: true })
}
const clearMonth = () => {
  localMonth.value = ''
  router.get('/modules/pay-allowances', {}, { preserveState: true, replace: true })
}

// Preview
const preview = reactive({ open: false, file: null })
const openPreview = (p) => { preview.file = p; preview.open = true }
const closePreview = () => { preview.open = false; preview.file = null }
const previewUrl = computed(() => (preview.file ? `/modules/pay-allowances/${preview.file.id}/preview` : ''))

const remove = (p) => {
  if (!confirm(`Discard payslip for "${p.employee_name}"? This cannot be undone.`)) return
  router.delete(`/modules/pay-allowances/${p.id}`, { preserveScroll: true })
}

const label = (s) => ({ PAYSLIP: 'Payslip', READMITTED: 'Re-admitted', TURNBACK: 'Turnback' }[s] ?? s)
const peso = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatMonth = (d) => (d ? new Date(d).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : '')
</script>

<style scoped>
.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.03em; }
.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; color: #0f172a; font-size: 0.88rem; }
.input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }

.btn-primary { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; font-weight: 700; color: #fff; background: linear-gradient(135deg, #047857, #10b981); padding: 0.55rem 1.1rem; border-radius: 0.6rem; box-shadow: 0 6px 16px rgba(4, 120, 87, 0.3); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; }
.btn-ghost:hover { background: #f1f5f9; }

.stat-pill { display: flex; flex-direction: column; padding: 0.3rem 0.9rem; background: #fff; border: 1px solid #eef0f3; border-radius: 0.7rem; box-shadow: 0 4px 12px rgba(15,23,42,0.04); }
.stat-pill-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; font-weight: 700; }
.stat-pill-value { font-size: 1rem; font-weight: 800; color: #0f172a; }

.tabs { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap; }
.tab { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.6rem; font-weight: 700; font-size: 0.88rem; color: #64748b; background: #fff; border: 1px solid #e2e8f0; transition: all 0.15s ease; }
.tab:hover { border-color: #6ee7b7; }
.tab-active { color: #047857; background: #ecfdf5; border-color: #10b981; }
.tab-count { font-size: 0.72rem; font-weight: 800; padding: 0.1rem 0.5rem; border-radius: 9999px; background: #e2e8f0; color: #475569; }
.tab-active .tab-count { background: #10b981; color: #fff; }

.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 0.5rem 1.25rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); overflow-x: auto; }
.empty { padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }
.th { padding: 0.75rem 0.5rem; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em; }
.td { padding: 0.75rem 0.5rem; color: #475569; }

.action-group { display: inline-flex; align-items: center; gap: 0.4rem; }
.icon-btn { display: inline-flex; align-items: center; justify-content: center; height: 2rem; width: 2rem; border-radius: 0.55rem; border: 1px solid #e2e8f0; background: #fff; color: #64748b; transition: all 0.15s ease; cursor: pointer; }
.icon-btn svg { height: 1.05rem; width: 1.05rem; }
.icon-preview:hover { color: #047857; border-color: #6ee7b7; background: #ecfdf5; }
.icon-download:hover { color: #15803d; border-color: #86efac; background: #f0fdf4; }
.icon-discard:hover { color: #dc2626; border-color: #fca5a5; background: #fef2f2; }

.preview-backdrop { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 1.5rem; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px); }
.preview-dialog { display: flex; flex-direction: column; width: 100%; max-width: 900px; height: 85vh; background: #fff; border-radius: 1rem; overflow: hidden; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5); }
.preview-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid #eef0f3; }
.preview-name { font-weight: 700; color: #0f172a; font-size: 0.95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.preview-meta { font-size: 0.78rem; color: #94a3b8; }
.preview-dl { display: inline-flex; align-items: center; justify-content: center; height: 2rem; width: 2rem; border-radius: 0.55rem; border: 1px solid #e2e8f0; color: #15803d; }
.preview-dl:hover { background: #f0fdf4; border-color: #86efac; }
.preview-dl svg { height: 1.05rem; width: 1.05rem; }
.preview-close { height: 2rem; width: 2rem; border-radius: 0.55rem; color: #94a3b8; font-size: 1rem; }
.preview-close:hover { background: #f1f5f9; color: #475569; }
.preview-body { flex: 1; min-height: 0; background: #f1f5f9; }
.preview-frame { width: 100%; height: 100%; border: 0; }
</style>
