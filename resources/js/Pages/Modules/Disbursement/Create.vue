<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Disbursement"
        title="New Disbursement"
        subtitle="Record a disbursement across any fund category"
        back-to="/modules/disbursement/records"
        back-label="Back to Records"
      />

      <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="panel">
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Category -->
            <div>
              <label class="label">Category <span class="req">*</span></label>
              <select v-model="form.category" class="input" :class="{ 'input-error': errors.category }">
                <option value="" disabled>Select disbursement category</option>
                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
              </select>
              <p v-if="errors.category" class="err">{{ errors.category }}</p>
            </div>

            <!-- Payee -->
            <div>
              <label class="label">Payee <span class="req">*</span></label>
              <input v-model="form.payee" type="text" class="input" :class="{ 'input-error': errors.payee }" placeholder="Name of payee" />
              <p v-if="errors.payee" class="err">{{ errors.payee }}</p>
            </div>

            <!-- Purpose -->
            <div>
              <label class="label">Purpose <span class="req">*</span></label>
              <textarea v-model="form.purpose" rows="2" class="input" :class="{ 'input-error': errors.purpose }" placeholder="Purpose of disbursement"></textarea>
              <p v-if="errors.purpose" class="err">{{ errors.purpose }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Reference No -->
              <div>
                <label class="label">Reference Number <span class="req">*</span></label>
                <input v-model="form.reference_no" type="text" class="input" :class="{ 'input-error': errors.reference_no }" placeholder="DV / Check No." />
                <p v-if="errors.reference_no" class="err">{{ errors.reference_no }}</p>
              </div>

              <!-- Date -->
              <div>
                <label class="label">Date <span class="req">*</span></label>
                <input v-model="form.disbursement_date" type="date" class="input" :class="{ 'input-error': errors.disbursement_date }" />
                <p v-if="errors.disbursement_date" class="err">{{ errors.disbursement_date }}</p>
              </div>
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

defineProps({
  categories: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)

const form = reactive({
  category: '',
  payee: '',
  purpose: '',
  reference_no: '',
  disbursement_date: new Date().toISOString().slice(0, 10),
  amount: '',
  attachment: null,
})

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
