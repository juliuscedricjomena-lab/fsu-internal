<template>
  <Teleport to="body">
    <div v-if="show" class="backdrop" @click.self="$emit('close')">
      <div class="dialog" role="dialog" aria-modal="true">
        <div class="dialog-head">
          <h2 class="dialog-title">{{ isEdit ? 'Edit User' : 'Add User' }}</h2>
          <button class="dialog-close" @click="$emit('close')">✕</button>
        </div>

        <form @submit.prevent="submit" class="dialog-body">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Name <span class="req">*</span></label>
              <input v-model="form.name" type="text" class="input" :class="{ 'input-error': errors.name }" />
              <p v-if="errors.name" class="err">{{ errors.name }}</p>
            </div>
            <div>
              <label class="label">Email <span class="req">*</span></label>
              <input v-model="form.email" type="email" class="input" :class="{ 'input-error': errors.email }" />
              <p v-if="errors.email" class="err">{{ errors.email }}</p>
            </div>
            <div>
              <label class="label">Designation</label>
              <input v-model="form.designation" type="text" class="input" />
              <p v-if="errors.designation" class="err">{{ errors.designation }}</p>
            </div>
            <div>
              <label class="label">Rank</label>
              <input v-model="form.rank" type="text" class="input" />
              <p v-if="errors.rank" class="err">{{ errors.rank }}</p>
            </div>
            <div>
              <label class="label">Role <span class="req">*</span></label>
              <select v-model="form.role_id" class="input" :class="{ 'input-error': errors.role_id }">
                <option value="" disabled>Select role</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
              <p v-if="errors.role_id" class="err">{{ errors.role_id }}</p>
            </div>
            <div>
              <label class="label">Status <span class="req">*</span></label>
              <select v-model="form.status" class="input" :class="{ 'input-error': errors.status }">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
              <p v-if="errors.status" class="err">{{ errors.status }}</p>
            </div>
          </div>

          <!-- Password only required when creating -->
          <template v-if="!isEdit">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-1">
              <div>
                <label class="label">Password <span class="req">*</span></label>
                <input v-model="form.password" type="password" class="input" :class="{ 'input-error': errors.password }" />
                <p v-if="errors.password" class="err">{{ errors.password }}</p>
              </div>
              <div>
                <label class="label">Confirm Password <span class="req">*</span></label>
                <input v-model="form.password_confirmation" type="password" class="input" />
              </div>
            </div>
          </template>

          <div class="dialog-foot">
            <button type="button" class="btn-ghost" @click="$emit('close')">Cancel</button>
            <button type="submit" :disabled="processing" class="btn-primary">
              {{ processing ? 'Saving...' : (isEdit ? 'Save Changes' : 'Create User') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  show: { type: Boolean, default: false },
  mode: { type: String, default: 'create' },
  user: { type: Object, default: null },
  roles: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)
const isEdit = computed(() => props.mode === 'edit')

const blank = () => ({
  name: '',
  email: '',
  designation: '',
  rank: '',
  role_id: '',
  status: 'active',
  password: '',
  password_confirmation: '',
})

const form = reactive(blank())

// Populate the form whenever the modal opens (edit vs create).
watch(
  () => [props.show, props.user],
  () => {
    if (!props.show) return
    const base = blank()
    if (isEdit.value && props.user) {
      base.name = props.user.name ?? ''
      base.email = props.user.email ?? ''
      base.designation = props.user.designation ?? ''
      base.rank = props.user.rank ?? ''
      base.role_id = props.user.role_id ?? props.user.role?.id ?? ''
      base.status = props.user.status ?? 'active'
    }
    Object.assign(form, base)
  },
  { immediate: true }
)

const submit = () => {
  processing.value = true
  const done = { onFinish: () => { processing.value = false }, onSuccess: () => emit('close'), preserveScroll: true }

  if (isEdit.value) {
    router.put(`/modules/admin/users/${props.user.id}`, {
      name: form.name,
      email: form.email,
      designation: form.designation,
      rank: form.rank,
      role_id: form.role_id,
      status: form.status,
    }, done)
  } else {
    router.post('/modules/admin/users', { ...form }, done)
  }
}
</script>

<style scoped>
.backdrop { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); }
.dialog { width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; background: #fff; border-radius: 1rem; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45); }
.dialog-head { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; border-bottom: 1px solid #eef0f3; }
.dialog-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
.dialog-close { color: #94a3b8; font-size: 1.1rem; }
.dialog-close:hover { color: #475569; }
.dialog-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 0.9rem; }
.dialog-foot { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; }

.label { display: block; font-size: 0.78rem; font-weight: 600; color: #334155; margin-bottom: 0.35rem; }
.req { color: #dc2626; }
.input { width: 100%; padding: 0.55rem 0.8rem; border: 1px solid #d1d5db; border-radius: 0.55rem; background: #fff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; color: #0f172a; font-size: 0.9rem; }
.input:focus { border-color: #9f1239; box-shadow: 0 0 0 3px rgba(159, 18, 57, 0.15); }
.input-error { border-color: #dc2626; }
.err { color: #dc2626; font-size: 0.76rem; margin-top: 0.25rem; }

.btn-ghost { font-size: 0.85rem; font-weight: 600; color: #475569; padding: 0.55rem 1.1rem; border-radius: 0.6rem; border: 1px solid #e2e8f0; }
.btn-ghost:hover { background: #f1f5f9; }
.btn-primary { font-size: 0.85rem; font-weight: 700; color: #fff; padding: 0.55rem 1.3rem; border-radius: 0.6rem; background: linear-gradient(135deg, #9f1239, #6d1a1a); box-shadow: 0 6px 16px rgba(159, 18, 57, 0.3); transition: opacity 0.15s; }
.btn-primary:hover { opacity: 0.92; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
