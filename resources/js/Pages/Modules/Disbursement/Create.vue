<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Disbursement"
        title="New Disbursement"
        subtitle="Record a disbursement under its fund and expense class"
        back-to="/modules/disbursement/records"
        back-label="Back to Records"
      />

      <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="panel">
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Fund type -->
            <div>
              <label class="label">Fund Type <span class="req">*</span></label>
              <select v-model="form.fund_type" class="input" :class="{ 'input-error': errors.fund_type }" @change="onFundChange">
                <option v-for="(label, key) in fundTypes" :key="key" :value="key">{{ label }}</option>
              </select>
              <p v-if="errors.fund_type" class="err">{{ errors.fund_type }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Expense group (filtered by fund type) -->
              <div>
                <label class="label">Expense Group <span class="req">*</span></label>
                <select v-model="form.expense_group" class="input" :class="{ 'input-error': errors.expense_group }" @change="onGroupChange">
                  <option value="" disabled>Select group</option>
                  <option v-for="g in availableGroups" :key="g" :value="g">{{ expenseGroups[g] }}</option>
                </select>
                <p v-if="errors.expense_group" class="err">{{ errors.expense_group }}</p>
              </div>

              <!-- Expense class (cascades from group) -->
              <div>
                <label class="label">Expense Class <span class="req">*</span></label>
                <select v-model="form.expense_class" class="input" :class="{ 'input-error': errors.expense_class }" :disabled="!form.expense_group">
                  <option value="" disabled>Select class</option>
                  <option v-for="c in availableClasses" :key="c" :value="c">{{ c }}</option>
                </select>
                <p v-if="errors.expense_class" class="err">{{ errors.expense_class }}</p>
              </div>
            </div>

            <!-- Project / particular (esp. for Capital Outlay) -->
            <div>
              <label class="label">
                Project / Particular
                <span v-if="form.expense_group === 'CO'" class="req">*</span>
              </label>
              <textarea v-model="form.project_particular" rows="2" class="input" :class="{ 'input-error': errors.project_particular }" placeholder="e.g. 10% 3rd Progress Payment for Completion of PNPA New Cadet Battalion Bldg"></textarea>
              <p v-if="errors.project_particular" class="err">{{ errors.project_particular }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Payee -->
              <div>
                <label class="label">Payee <span class="req">*</span></label>
                <input v-model="form.payee" type="text" class="input" :class="{ 'input-error': errors.payee }" placeholder="Name of payee" />
                <p v-if="errors.payee" class="err">{{ errors.payee }}</p>
              </div>
              <!-- Reference No -->
              <div>
                <label class="label">Reference Number <span class="req">*</span></label>
                <input v-model="form.reference_no" type="text" class="input" :class="{ 'input-error': errors.reference_no }" placeholder="DV / ADA No." />
                <p v-if="errors.reference_no" class="err">{{ errors.reference_no }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
              <!-- Date -->
              <div>
                <label class="label">Date <span class="req">*</span></label>
                <input v-model="form.disbursement_date" type="date" class="input" :class="{ 'input-error': errors.disbursement_date }" />
                <p v-if="errors.disbursement_date" class="err">{{ errors.disbursement_date }}</p>
              </div>
              <!-- Amount -->
              <div>
                <label class="label">Amount <span class="req">*</span></label>
                <div class="amount-wrap">
                  <span class="peso">₱</span>
                  <input v-model="form.amount" type="number" step="0.01" min="0" class="input input-amount" :class="{ 'input-error': errors.amount }" placeholder="0.00" />
                </div>
                <p v-if="errors.amount" class="err">{{ errors.amount }}</p>
              </div>
              <!-- Mode of disbursement -->
              <div>
                <label class="label">Mode of Disbursement</label>
                <select v-model="form.mode_of_disbursement" class="input">
                  <option value="">—</option>
                  <option v-for="m in modes" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
            </div>

            <!-- Attachment -->
            <div>
              <label class="label">Supporting Document</label>
              <label class="file-drop" :class="{ 'has-file': form.attachment }">
                <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onFile" />
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-violet-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M17 8l-5-5-5 5" /><path d="M12 3v12" /></svg>
                <span v-if="!form.attachment" class="file-text">Choose File — PDF, JPG, or PNG (max 5MB)</span>
                <span v-else class="file-text font-medium text-slate-800">{{ form.attachment.name }}</span>
              </label>
              <p v-if="errors.attachment" class="err">{{ errors.attachment }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <Link href="/modules/disbursement/records" class="btn-ghost">Cancel</Link>
              <button type="submit" :disabled="processing" class="btn-primary">
                {{ processing ? 'Submitting...' : 'Submit' }}
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

const props = defineProps({
  fundTypes: { type: Object, default: () => ({}) },
  expenseGroups: { type: Object, default: () => ({}) },
  expenseClasses: { type: Object, default: () => ({}) },
  groupsByFund: { type: Object, default: () => ({}) },
  modes: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)

const form = reactive({
  fund_type: 'appropriated',
  expense_group: '',
  expense_class: '',
  project_particular: '',
  payee: '',
  reference_no: '',
  disbursement_date: new Date().toISOString().slice(0, 10),
  amount: '',
  mode_of_disbursement: '',
  attachment: null,
})

const availableGroups = computed(() => props.groupsByFund[form.fund_type] ?? [])
const availableClasses = computed(() => props.expenseClasses[form.expense_group] ?? [])

const onFundChange = () => {
  form.expense_group = ''
  form.expense_class = ''
}
const onGroupChange = () => {
  form.expense_class = ''
}

const onFile = (e) => {
  form.attachment = e.target.files?.[0] ?? null
}

const submit = () => {
  processing.value = true
  router.post('/modules/disbursement/records', { ...form }, {
    forceFormData: true,
    onFinish: () => {
      processing.value = false
    },
  })
}
</script>

<style scoped>
.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05); }
.label { display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; }
.req { color: #dc2626; }
.input { width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #d1d5db; border-radius: 0.6rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-family: inherit; }
.input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15); }
.input:disabled { background: #f1f5f9; color: #94a3b8; }
.input-error { border-color: #dc2626; }
.err { color: #dc2626; font-size: 0.78rem; margin-top: 0.3rem; }

.amount-wrap { position: relative; display: flex; align-items: center; }
.peso { position: absolute; left: 0.85rem; color: #64748b; font-weight: 600; }
.input-amount { padding-left: 1.9rem; }

.file-drop { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; border: 1.5px dashed #cbd5e1; border-radius: 0.7rem; padding: 1rem 1.1rem; transition: border-color 0.15s, background 0.15s; }
.file-drop:hover, .file-drop.has-file { border-color: #7c3aed; background: #f5f3ff; }
.file-text { font-size: 0.85rem; color: #64748b; }

.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.6rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; transition: background 0.15s; }
.btn-ghost:hover { background: #f1f5f9; }
.btn-primary { font-size: 0.85rem; font-weight: 700; color: #fff; padding: 0.6rem 1.5rem; border-radius: 0.6rem; background: linear-gradient(135deg, #7c3aed, #a855f7); box-shadow: 0 6px 16px rgba(124, 58, 237, 0.35); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
