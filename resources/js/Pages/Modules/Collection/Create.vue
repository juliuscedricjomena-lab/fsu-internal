<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Collection"
        title="Transaction Entry"
        subtitle="Collection Section — record a new collection transaction"
        back-to="/modules/collection/transactions"
        back-label="Back to Transactions"
      />

      <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="panel">
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Account Type -->
            <div>
              <label class="label">Account Type <span class="req">*</span></label>
              <select v-model="form.account_type" class="input" :class="{ 'input-error': errors.account_type }">
                <option value="" disabled>Select account type</option>
                <option v-for="type in accountTypes" :key="type" :value="type">{{ type }}</option>
              </select>
              <p v-if="errors.account_type" class="err">{{ errors.account_type }}</p>
            </div>

            <!-- Payor Name -->
            <div>
              <label class="label">Payor Name <span class="req">*</span></label>
              <input v-model="form.payor_name" type="text" class="input" :class="{ 'input-error': errors.payor_name }" placeholder="Full name of payor" />
              <p v-if="errors.payor_name" class="err">{{ errors.payor_name }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <!-- Date of Transaction -->
              <div>
                <label class="label">Date of Transaction <span class="req">*</span></label>
                <input v-model="form.transaction_date" type="date" class="input" :class="{ 'input-error': errors.transaction_date }" />
                <p v-if="errors.transaction_date" class="err">{{ errors.transaction_date }}</p>
              </div>

              <!-- OR Number -->
              <div>
                <label class="label">Official Receipt Number <span class="req">*</span></label>
                <input v-model="form.or_number" type="text" class="input" :class="{ 'input-error': errors.or_number }" placeholder="OR-000000" />
                <p v-if="errors.or_number" class="err">{{ errors.or_number }}</p>
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

            <!-- OR Attachment -->
            <div>
              <label class="label">Official Receipt Attachment</label>
              <label class="file-drop" :class="{ 'has-file': form.or_attachment }">
                <input type="file" class="hidden" accept=".pdf,.jpg,.jpeg,.png" @change="onFile" />
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-teal-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M17 8l-5-5-5 5" /><path d="M12 3v12" /></svg>
                <span v-if="!form.or_attachment" class="file-text">Choose File — PDF, JPG, or PNG (max 5MB)</span>
                <span v-else class="file-text font-medium text-slate-800">{{ form.or_attachment.name }}</span>
              </label>
              <p v-if="errors.or_attachment" class="err">{{ errors.or_attachment }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <Link href="/modules/collection/transactions" class="btn-ghost">Cancel</Link>
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
  accountTypes: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)

const form = reactive({
  account_type: '',
  payor_name: '',
  transaction_date: new Date().toISOString().slice(0, 10),
  or_number: '',
  amount: '',
  or_attachment: null,
})

const onFile = (e) => {
  form.or_attachment = e.target.files?.[0] ?? null
}

const submit = () => {
  processing.value = true
  router.post('/modules/collection/transactions', { ...form }, {
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
.input {
  width: 100%; padding: 0.6rem 0.85rem; border: 1px solid #d1d5db; border-radius: 0.6rem; background: #fff;
  outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a;
}
.input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15); }
.input-error { border-color: #dc2626; }
.err { color: #dc2626; font-size: 0.78rem; margin-top: 0.3rem; }

.amount-wrap { position: relative; display: flex; align-items: center; }
.peso { position: absolute; left: 0.85rem; color: #64748b; font-weight: 600; }
.input-amount { padding-left: 1.9rem; }

.file-drop {
  display: flex; align-items: center; gap: 0.75rem; cursor: pointer;
  border: 1.5px dashed #cbd5e1; border-radius: 0.7rem; padding: 1rem 1.1rem; transition: border-color 0.15s, background 0.15s;
}
.file-drop:hover { border-color: #0d9488; background: #f0fdfa; }
.file-drop.has-file { border-color: #0d9488; background: #f0fdfa; }
.file-text { font-size: 0.85rem; color: #64748b; }

.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.6rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; transition: background 0.15s; }
.btn-ghost:hover { background: #f1f5f9; }
.btn-primary {
  font-size: 0.85rem; font-weight: 700; color: #fff; padding: 0.6rem 1.5rem; border-radius: 0.6rem;
  background: linear-gradient(135deg, #0d9488, #14b8a6); box-shadow: 0 6px 16px rgba(13, 148, 136, 0.35); transition: opacity 0.15s;
}
.btn-primary:hover { opacity: 0.92; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
