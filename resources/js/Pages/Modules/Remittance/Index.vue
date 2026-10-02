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
                <td class="td">
                  <div class="action-group">
                    <button class="icon-btn icon-preview" title="Preview" @click="openPreview(f)">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" /></svg>
                    </button>
                    <a :href="`/modules/remittance/${f.id}/download`" class="icon-btn icon-download" title="Download">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" /></svg>
                    </a>
                    <button class="icon-btn icon-discard" title="Discard" @click="remove(f)">
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
                <p class="preview-name">{{ preview.file?.original_name }}</p>
                <p class="preview-meta">{{ preview.file?.type }} • {{ formatSize(preview.file?.size) }}</p>
              </div>
              <div class="flex items-center gap-2">
                <a :href="`/modules/remittance/${preview.file?.id}/download`" class="preview-dl" title="Download">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" /></svg>
                </a>
                <button class="preview-close" @click="closePreview" title="Close">✕</button>
              </div>
            </div>
            <div class="preview-body">
              <img v-if="isImage" :src="previewUrl" :alt="preview.file?.original_name" class="preview-img" />
              <iframe v-else-if="isPdf" :src="previewUrl" class="preview-frame" title="File preview"></iframe>
              <div v-else class="preview-fallback">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-12 w-12 text-slate-300"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /></svg>
                <p>No inline preview available for this file type.</p>
                <a :href="`/modules/remittance/${preview.file?.id}/download`" class="btn-primary">Download to view</a>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
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

// Preview
const preview = reactive({ open: false, file: null })

const openPreview = (file) => {
  preview.file = file
  preview.open = true
}
const closePreview = () => {
  preview.open = false
  preview.file = null
}

const previewUrl = computed(() => (preview.file ? `/modules/remittance/${preview.file.id}/preview` : ''))

const extOf = (name) => (name?.split('.').pop() || '').toLowerCase()
const isImage = computed(() => ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extOf(preview.file?.original_name)))
const isPdf = computed(() => extOf(preview.file?.original_name) === 'pdf')

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
/* Icon action buttons */
.action-group { display: inline-flex; align-items: center; gap: 0.4rem; }
.icon-btn {
  display: inline-flex; align-items: center; justify-content: center;
  height: 2rem; width: 2rem; border-radius: 0.55rem; border: 1px solid #e2e8f0;
  background: #fff; color: #64748b; transition: all 0.15s ease; cursor: pointer;
}
.icon-btn svg { height: 1.05rem; width: 1.05rem; }
.icon-preview:hover { color: #0369a1; border-color: #7dd3fc; background: #f0f9ff; }
.icon-download:hover { color: #15803d; border-color: #86efac; background: #f0fdf4; }
.icon-discard:hover { color: #dc2626; border-color: #fca5a5; background: #fef2f2; }

/* Preview modal */
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
.preview-body { flex: 1; min-height: 0; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: auto; }
.preview-img { max-width: 100%; max-height: 100%; object-fit: contain; }
.preview-frame { width: 100%; height: 100%; border: 0; }
.preview-fallback { display: flex; flex-direction: column; align-items: center; gap: 0.9rem; color: #64748b; font-size: 0.9rem; padding: 2rem; text-align: center; }
.btn-primary { font-size: 0.85rem; font-weight: 700; color: #fff; padding: 0.55rem 1.2rem; border-radius: 0.6rem; background: linear-gradient(135deg, #0369a1, #0ea5e9); box-shadow: 0 6px 16px rgba(3, 105, 161, 0.3); }
</style>
