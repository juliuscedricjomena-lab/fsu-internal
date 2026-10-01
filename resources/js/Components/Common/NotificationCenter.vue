<template>
  <div class="fixed top-4 right-4 z-50 space-y-3 max-w-md">
    <transition-group
      name="notification"
      tag="div"
      class="space-y-3"
    >
      <div
        v-for="notification in ui.notifications"
        :key="notification.id"
        :class="[
          'rounded-lg shadow-lg p-4 text-white animate-slide-in',
          getNotificationClass(notification.type)
        ]"
      >
        <div class="flex items-start gap-3">
          <div class="text-xl flex-shrink-0">
            {{ getNotificationIcon(notification.type) }}
          </div>
          <div class="flex-1">
            <p class="font-medium">{{ notification.title || getNotificationTitle(notification.type) }}</p>
            <p class="text-sm opacity-90">{{ notification.message }}</p>
          </div>
          <button
            @click="ui.removeNotification(notification.id)"
            class="flex-shrink-0 hover:opacity-70 transition"
          >
            ✕
          </button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useUIStore } from '@/Stores/ui'

const ui = useUIStore()

const getNotificationClass = (type) => {
  const classes = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500',
  }
  return classes[type] || classes.info
}

const getNotificationIcon = (type) => {
  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ',
  }
  return icons[type] || icons.info
}

const getNotificationTitle = (type) => {
  const titles = {
    success: 'Success',
    error: 'Error',
    warning: 'Warning',
    info: 'Info',
  }
  return titles[type] || titles.info
}
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

@keyframes slide-in {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease;
}
</style>
