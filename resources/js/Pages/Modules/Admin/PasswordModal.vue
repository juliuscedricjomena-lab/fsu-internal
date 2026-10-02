<template>
  <Teleport to="body">
    <div v-if="show" class="backdrop" @click.self="$emit('close')">
      <div class="dialog" role="dialog" aria-modal="true">
        <div class="dialog-head">
          <h2 class="dialog-title">Reset Password</h2>
          <button class="dialog-close" @click="$emit('close')">✕</button>
        </div>

        <form @submit.prevent="submit" class="dialog-body">
          <p class="hint">Set a new password for <span class="font-semibold text-slate-800">{{ user?.name }}</span>.</p>

          <div>
            <label class="label">New Password <span class="req">*</span></label>
            <input v-model="form.password" type="password" class="input" :class="{ 'input-error': errors.password }" />
            <p v-if="errors.password" class="err">{{ errors.password }}</p>
          </div>
          <div>
            <label class="label">Confirm Password <span class="req">*</span></label>
            <input v-model="form.password_confirmation" type="password" class="input" />
          </div>

          <div class="dialog-foot">
            <button type="button" class="btn-ghost" @click="$emit('close')">Cancel</button>
            <button type="submit" :disabled="processing" class="btn-primary">
              {{ processing ? 'Saving...' : 'Reset Password' }}
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
  user: { type: Object, default: null },
})

const emit = defineEmits(['close'])

const page = usePage()
const errors = computed(() => page.props.errors ?? {})
const processing = ref(false)

const form = reactive({ password: '', password_confirmation: '' })

watch(
  () => props.show,
  (open) => {
    if (open) {
      form.password = ''
      form.password_confirmation = ''
    }
  }
)

const submit = () => {
  if (!props.user) return
  processing.value = true
  router.put(`/modules/admin/users/${props.user.id}/password`, { ...form }, {
    preserveScroll: true,
    onSuccess: () => emit('close'),
    onFinish: () => { processing.value = false },
  })
}
</script>

<style scoped>
.backdrop { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); }
.dialog { width: 100%; max-width: 420px; background: #fff; border-radius: 1rem; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45); }
.dialog-head { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; border-bottom: 1px solid #eef0f3; }
.dialog-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
.dialog-close { color: #94a3b8; font-size: 1.1rem; }
.dialog-close:hover { color: #475569; }
.dialog-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 0.9rem; }
.dialog-foot { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; }
.hint { font-size: 0.85rem; color: #64748b; }

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
