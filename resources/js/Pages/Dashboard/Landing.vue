<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-8">
          <div class="flex justify-between items-start">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user.name }}!</h1>
              <p class="text-gray-600 mt-2">{{ user.designation || 'FSU Employee' }} • Role: <span class="font-semibold">{{ user.role?.name }}</span></p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500">Last login</p>
              <p class="text-lg font-semibold text-gray-900">Today</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- I UNDERSTAND Section -->
        <div v-if="!understood" class="mb-12 p-6 bg-blue-50 border-2 border-blue-200 rounded-lg shadow-sm">
          <div class="flex items-start gap-4">
            <div class="text-3xl">ℹ️</div>
            <div class="flex-1">
              <h2 class="text-xl font-semibold text-blue-900 mb-3">I UNDERSTAND</h2>
              <p class="text-blue-800 mb-4 leading-relaxed">
                I understand that this FSU Internal Finance System contains confidential financial information. 
                I agree to use this system only for authorized purposes and will maintain the confidentiality of all data 
                accessed through this system. I will comply with all applicable policies and procedures.
              </p>
              <button
                @click="toggleUnderstand"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
              >
                I Understand & Accept
              </button>
            </div>
          </div>
        </div>

        <!-- Available Modules Grid -->
        <div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Available Modules</h2>
          <p class="text-gray-600 mb-6">Access the modules available to your role</p>
          
          <div v-if="modules.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <Link
              v-for="module in modules"
              :key="module.name"
              :href="`/modules/${module.path}`"
              class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 border-l-4 border-fsu-primary group cursor-pointer"
            >
              <div class="text-4xl mb-3 group-hover:scale-110 transition">{{ module.icon }}</div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-fsu-primary transition">{{ module.name }}</h3>
              <p class="text-gray-600 text-sm mb-4">{{ module.description }}</p>
              <span class="text-fsu-primary font-medium text-sm">Access Module →</span>
            </Link>
          </div>

          <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-12">
            <p class="text-yellow-800">You don't have access to any modules yet. Please contact your administrator.</p>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid md:grid-cols-4 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-600 text-sm font-medium">Role</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ user.role?.name || 'N/A' }}</p>
              </div>
              <div class="text-3xl">👤</div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-600 text-sm font-medium">Status</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ capitalizeStatus(user.status) }}</p>
              </div>
              <div class="text-3xl">✅</div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-600 text-sm font-medium">Modules</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ modules.length }}</p>
              </div>
              <div class="text-3xl">📦</div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-600 text-sm font-medium">Member Since</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ formatDate(user.created_at) }}</p>
              </div>
              <div class="text-3xl">📅</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '../../Components/Layout/AppLayout.vue'

defineProps({
  modules: Array,
  user: Object,
})

const understood = ref(false)

const toggleUnderstand = () => {
  understood.value = !understood.value
}

const capitalizeStatus = (status) => {
  return status?.charAt(0).toUpperCase() + status?.slice(1) || 'Unknown'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<style scoped>
</style>
