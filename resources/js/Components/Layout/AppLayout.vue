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
          <Link
            href="/logout"
            method="post"
            as="button"
            class="px-4 py-2 bg-fsu-accent text-white rounded-lg hover:opacity-90 transition"
          >
            Logout
          </Link>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <slot />
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import NotificationCenter from '../Common/NotificationCenter.vue'

const { props } = usePage()
const user = props.auth?.user
</script>

<style scoped>
</style>
