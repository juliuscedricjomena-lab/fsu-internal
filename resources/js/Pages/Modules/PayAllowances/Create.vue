<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Pay & Allowances"
        title="Upload Payslip"
        subtitle="Upload a payslip PDF and compute net pay from days of duty"
        back-to="/modules/pay-allowances"
        back-label="Back to Pay & Allowances"
      />

      <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="panel">
          <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Employee -->
              <div>
                <label class="label">Employee Name <span class="req">*</span></label>
                <input v-model="form.employee_name" type="text" class="input" :class="{ 'input-error': errors.employee_name }" placeholder="Full name" />
                <p v-if="errors.employee_name" class="err">{{ errors.employee_name }}</p>
              </div>
              <!-- Status -->
              <div>
                <label class="label">Status <span class="req">*</span></label>
                <select v-model="form.status" class="input" :class="{ 'input-error': errors.status }">
                  <option v-for="s in statuses" :key="s" :value="s">{{ labelFor(s) }}</option>
                </select>
                <p v-if="errors.status" class="err">{{ errors.status }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Period -->
              <div>
                <label class="label">Period (Month) <span class="req">*</span></label>
                <input v-model="form.period_month" type="month" class="input" :class="{ 'input-error': errors.period_month }" />
                <p v-if="errors.period_month" class="err">{{ errors.period_month }}</p>
              </div>
              <!-- Total -->
              <div>
                <label class="label">Total Payslip Amount <span class="req">*</span></label>
                <div class="amount-wrap">
                  <span class="peso">₱</span>
                  <input v-model="form.total_amount" type="number" step="0.01" min="0" class="input input-amount" :class="{ 'input-error': errors.total_amount }" placeholder="0.00" />
                </div>
                <p v-if="errors.total_amount" class="err">{{ errors.total_amount }}</p>
              </div>
            </div>

            <!-- Days of duty -->
            <div class="duty-box">
              <p class="duty-title">Days of Duty (for computation)</p>
              <p class="duty-hint">Net pay deducts a per-day amount for each day not reported. Leave blank to keep the full total.</p>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                <div>
                  <label class="label">Working Days</label>
                  <input v-model.number="form.working_days" type="number" min="1" max="31" class="input" placeholder="e.g. 22" />
                </div>
                <div>
                  <label class="label">Days Present</label>
                  <input v-model.number="form.days_present" type="number" min="0" max="31" class="input" placeholder="e.g. 20" @input="syncAbsent" />
                </div>
                <div>
                  <label class="label">Days Absent</label>
                  <input v-model.number="form.days_absent" type="number" min="0" max="31" class="input" placeholder="e.g. 2" />
                </div>
              </div>

              <!-- Live computed preview -->
              <div class="compute-preview">
                <div>
                  <span class="compute-label">Daily Rate</span>
                  <span class="compute-value">{{ peso(dailyRate) }}</span>
                </div>
                <div>
                  <span class="compute-label">Deduction</span>
                  <span class="compute-value text-rose-600">− {{ peso(deduction) }}</span>
                </div>
                <div>
                  <span class="compute-label">Computed Net Pay</span>
                  <span class="compute-net">{{ peso(computedNet) }}</span>
                </div>
              </div>
            </div>

            <!-- PDF -->
            <div>
              <label class="label">Payslip PDF <span class="req">*</span></label>
              <label class="file-drop" :class="{ 'has-file': form.pdf }">
                <input type="file" class="hidden" accept=".pdf" @change="onFile" />
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-emerald-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /></svg>
                <span v-if="!form.pdf" class="file-text">Choose PDF file (max 10MB)</span>
                <span v-else class="file-text font-medium text-slate-800">{{ form.pdf.name }}</span>
              </label>
              <p v-if="errors.pdf" class="err">{{ errors.pdf }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <Link href="/modules/pay-allowances" class="btn-ghost">Cancel</Link>
              <button type="submit" :disabled="processing" class="btn-primary">
                {{ processing ? 'Uploading...' : 'Upload Payslip' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

defineProps({
  statuses: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)

const form = reactive({
  employee_name: '',
  status: 'PAYSLIP',
  period_month: new Date().toISOString().slice(0, 7),
  total_amount: '',
  working_days: null,
  days_present: null,
  days_absent: null,
  pdf: null,
})

// If days present is entered and working days known, infer absent.
const syncAbsent = () => {
  if (form.working_days && form.days_present != null) {
    const absent = form.working_days - form.days_present
    form.days_absent = absent >= 0 ? absent : 0
  }
}

const dailyRate = computed(() => {
  const total = Number(form.total_amount || 0)
  const wd = Number(form.working_days || 0)
  return wd > 0 ? total / wd : 0
})

const deduction = computed(() => {
  const wd = Number(form.working_days || 0)
  const absent = Number(form.days_absent || 0)
  if (wd <= 0 || absent <= 0) return 0
  return dailyRate.value * Math.min(absent, wd)
})

const computedNet = computed(() => {
  const total = Number(form.total_amount || 0)
  return Math.max(total - deduction.value, 0)
})

const onFile = (e) => {
  form.pdf = e.target.files?.[0] ?? null
}

const submit = () => {
  processing.value = true
  router.post('/modules/pay-allowances', { ...form }, {
    forceFormData: true,
    onFinish: () => { processing.value = false },
  })
}

const labelFor = (s) => ({ PAYSLIP: 'Payslip', READMITTED: 'Re-admitted', TURNBACK: 'Turnback' }[s] ?? s)
const peso = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
</script>

<style scoped>
.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05); }
.label { display: block; font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem; }
.req { color: #dc2626; }
.input { width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #d1d5db; border-radius: 0.6rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-family: inherit; }
.input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
.input-error { border-color: #dc2626; }
.err { color: #dc2626; font-size: 0.78rem; margin-top: 0.3rem; }

.amount-wrap { position: relative; display: flex; align-items: center; }
.peso { position: absolute; left: 0.85rem; color: #64748b; font-weight: 600; }
.input-amount { padding-left: 1.9rem; }

.duty-box { background: #f8fafc; border: 1px solid #eef0f3; border-radius: 0.8rem; padding: 1.1rem 1.25rem; }
.duty-title { font-size: 0.9rem; font-weight: 700; color: #0f172a; }
.duty-hint { font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; }

.compute-preview { display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #cbd5e1; }
.compute-preview > div { display: flex; flex-direction: column; }
.compute-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; font-weight: 700; }
.compute-value { font-size: 1rem; font-weight: 700; color: #334155; }
.compute-net { font-size: 1.25rem; font-weight: 800; color: #047857; }

.file-drop { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; border: 1.5px dashed #cbd5e1; border-radius: 0.7rem; padding: 1rem 1.1rem; transition: border-color 0.15s, background 0.15s; }
.file-drop:hover, .file-drop.has-file { border-color: #10b981; background: #ecfdf5; }
.file-text { font-size: 0.85rem; color: #64748b; }

.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.6rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; transition: background 0.15s; }
.btn-ghost:hover { background: #f1f5f9; }
.btn-primary { font-size: 0.85rem; font-weight: 700; color: #fff; padding: 0.6rem 1.5rem; border-radius: 0.6rem; background: linear-gradient(135deg, #047857, #10b981); box-shadow: 0 6px 16px rgba(4, 120, 87, 0.35); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
