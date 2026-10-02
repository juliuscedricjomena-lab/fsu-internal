<template>
  <AppLayout>
    <div class="min-h-screen bg-slate-50">
      <ModuleHeader
        module="Remittance"
        title="Remittance"
        subtitle="Upload and manage PAG-IBIG and PHILHEALTH remittance files"
      />

      <div class="max-w-5xl mx-auto px-6 py-10">
        <!-- Upload panel -->
        <div class="panel mb-8">
          <h2 class="panel-title">Upload Remittance File</h2>
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Type -->
            <div>
              <label class="label">Remittance Type <span class="req">*</span></label>
              <div class="type-grid">
                <label
                  v-for="t in types"
                  :key="t"
                  class="type-option"
                  :class="{ 'type-selected': form.type === t }"
                >
                  <input type="radio" class="hidden" :value="t" v-model="form.type" />
                  <span class="type-dot"></span>
                  {{ t }}
                </label>
              </div>
              <p v-if="errors.type" class="err">{{ errors.type }}</p>
            </div>

            <!-- File -->
            <div>
              <label class="label">File <span class="req">*</span></label>
              <label class="file-drop" :class="{ 'has-file': form.file }">
                <input type="file" class="hidden" accept=".pdf,.xls,.xlsx,.csv,.jpg,.jpeg,.png" @change="onFile" />
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-sky-600"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M17 8l-5-5-5 5" /><path d="M12 3v12" /></svg>
                <span v-if="!form.file" class="file-text">Choose File — PDF, Excel, CSV, or image (max 10MB)</span>
                <span v-else class="file-text font-medium text-slate-800">{{ form.file.name }}</span>
              </label>
              <p v-if="errors.file" class="err">{{ errors.file }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
              <button type="submit" :disabled="processing || !form.file || !form.type" class="btn-primary">
                {{ processing ? 'Uploading...' : 'Upload' }}
              </button>
              <button type="button" class="btn-ghost" @click="discard" :disabled="!form.file && !form.type">
                Discard
              </button>
            </div>
          </form>
        </div>

        <!-- Uploaded files -->
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
          <h2 class="text-lg font-bold text-slate-900">Uploaded Files</h2>
          <div class="flex items-end gap-3">
            <div>
              <label class="label">Type</label>
              <select v-model="localFilters.type" class="input" @change="applyFilters">
                <option value="">All</option>
                <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
            <div>
              <label class="label">Date</label>
              <input v-model="localFilters.date" type="date" class="input" @change="applyFilters" />
            </div>
            <button v-if="localFilters.type || localFilters.date" class="btn-ghost" @click="clearFilters">Clear</button>
          </div>
        </div>

        <div class="panel">
          <div v-if="files.length === 0" class="empty">
            No remittance files uploaded yet.
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500 border-b border-slate-200">
                <th class="th">#</th>
                <th class="th">Type</th>
                <th class="th">File Name</th>
                <th class="th">Size</th>
                <th class="th">Uploaded By</th>
                <th class="th">Uploaded At</th>
                <th class="th text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(f, i) in files" :key="f.id" class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="td">{{ i + 1 }}</td>
                <td class="td"><span class="chip" :class="f.type === 'PAG-IBIG' ? 'chip-pagibig' : 'chip-philhealth'">{{ f.type }}</span></td>
                <td class="td font-medium text-slate-800">{{ f.original_name }}</td>
                <td class="td">{{ formatSize(f.size) }}</td>
                <td class="td">{{ f.user?.name ?? '—' }}</td>
                <td class="td">{{ formatDateTime(f.created_at) }}</td>
                <td class="td text-center whitespace-nowrap">
                  <a :href="`/modules/remittance/${f.id}/download`" class="link">Download</a>
                  <button class="link-danger ml-3" @click="remove(f)">Discard</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '../../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../../Components/Common/ModuleHeader.vue'

const props = defineProps({
  files: { type: Array, default: () => [] },
  types: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)

const form = reactive({
  type: '',
  file: null,
})

const localFilters = reactive({
  type: props.filters.type ?? '',
  date: props.filters.date ?? '',
})

const onFile = (e) => {
  form.file = e.target.files?.[0] ?? null
}

const submit = () => {
  processing.value = true
  router.post('/modules/remittance', { ...form }, {
    forceFormData: true,
    onSuccess: () => {
      form.type = ''
      form.file = null
    },
    onFinish: () => {
      processing.value = false
    },
  })
}

const discard = () => {
  form.type = ''
  form.file = null
}

const applyFilters = () => {
  const query = Object.fromEntries(Object.entries(localFilters).filter(([, v]) => v))
  router.get('/modules/remittance', query, { preserveState: true, preserveScroll: true, replace: true })
}

const clearFilters = () => {
  localFilters.type = ''
  localFilters.date = ''
  router.get('/modules/remittance', {}, { preserveState: true, replace: true })
}

const remove = (file) => {
  if (!confirm(`Discard "${file.original_name}"? This cannot be undone.`)) return
  router.delete(`/modules/remittance/${file.id}`, { preserveScroll: true })
}

const formatSize = (bytes) => {
  if (!bytes) return '—'
  const kb = bytes / 1024
  return kb < 1024 ? `${kb.toFixed(1)} KB` : `${(kb / 1024).toFixed(2)} MB`
}

const formatDateTime = (d) => (d ? new Date(d).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '')
</script>

<style scoped>
.panel { background: #fff; border: 1px solid #eef0f3; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); overflow-x: auto; }
.panel-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 1.1rem; }
.label { display: block; font-size: 0.72rem; font-weight: 600; color: #64748b; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.03em; }
.req { color: #dc2626; }

.type-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; max-width: 24rem; }
.type-option {
  display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-weight: 600; color: #475569;
  border: 1.5px solid #e2e8f0; border-radius: 0.7rem; padding: 0.7rem 1rem; transition: all 0.15s ease;
}
.type-option:hover { border-color: #7dd3fc; }
.type-selected { border-color: #0ea5e9; background: #f0f9ff; color: #0369a1; }
.type-dot { height: 0.8rem; width: 0.8rem; border-radius: 9999px; border: 2px solid #cbd5e1; transition: all 0.15s ease; }
.type-selected .type-dot { border-color: #0ea5e9; background: #0ea5e9; box-shadow: inset 0 0 0 2px #fff; }

.input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-size: 0.88rem; }
.input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15); }

.file-drop { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; border: 1.5px dashed #cbd5e1; border-radius: 0.7rem; padding: 1rem 1.1rem; transition: border-color 0.15s, background 0.15s; }
.file-drop:hover, .file-drop.has-file { border-color: #0ea5e9; background: #f0f9ff; }
.file-text { font-size: 0.85rem; color: #64748b; }

.btn-primary { font-size: 0.85rem; font-weight: 700; color: #fff; padding: 0.6rem 1.5rem; border-radius: 0.6rem; background: linear-gradient(135deg, #0369a1, #0ea5e9); box-shadow: 0 6px 16px rgba(3, 105, 161, 0.35); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; transition: background 0.15s; }
.btn-ghost:hover:not(:disabled) { background: #f1f5f9; }
.btn-ghost:disabled { opacity: 0.5; cursor: not-allowed; }

.empty { padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 0.9rem; }
.th { padding: 0.75rem 0.5rem; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em; }
.td { padding: 0.75rem 0.5rem; color: #475569; }
.chip { font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 9999px; }
.chip-pagibig { color: #9a3412; background: #ffedd5; }
.chip-philhealth { color: #0369a1; background: #e0f2fe; }
.link { color: #0ea5e9; font-weight: 600; }
.link:hover { text-decoration: underline; }
.link-danger { color: #dc2626; font-weight: 600; }
.link-danger:hover { text-decoration: underline; }
</style>
