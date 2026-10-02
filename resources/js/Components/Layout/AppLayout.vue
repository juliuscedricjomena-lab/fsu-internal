<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Notification Center -->
    <NotificationCenter />

    <!-- Navigation -->
    <nav class="bg-fsu-primary text-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <Link href="/" class="text-2xl font-bold">FSU Internal</Link>
        
        <div class="flex items-center gap-6">
          <span class="text-white">{{ user?.name }}</span>
          <button
            @click="logout"
            class="px-4 py-2 bg-fsu-accent text-white rounded-lg hover:opacity-90 transition"
          >
            Logout
          </button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <slot />
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
import NotificationCenter from '../Common/NotificationCenter.vue'
import { useUIStore } from '@/Stores/ui'

const page = usePage()
const ui = useUIStore()

const user = computed(() => page.props.auth?.user)

// Surface server-side flash messages through the notification center.
// Watches the shared `flash` prop so a toast appears after each navigation
// (e.g. "transaction recorded" or "no access to that module").
watch(
  () => page.props.flash,
  (flash) => {
    if (!flash) return
    if (flash.success) ui.showSuccess(flash.success)
    if (flash.error) ui.showError(flash.error)
    if (flash.warning) ui.showWarning(flash.warning)
    if (flash.info) ui.showInfo(flash.info)
  },
  { immediate: true, deep: true }
)

const logout = () => {
  // Clear the system-reminder acknowledgment so it shows again on next login.
  if (typeof window !== 'undefined') {
    const key = `fsu_reminder_ack_${user.value?.id ?? 'guest'}`
    window.sessionStorage.removeItem(key)
  }
  router.post('/logout')
}
</script>

<style scoped>
</style>
