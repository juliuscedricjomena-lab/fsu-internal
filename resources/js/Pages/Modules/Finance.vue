<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <ModuleHeader
        module="Finance"
        title="Finance Module"
        subtitle="Financial reporting and analysis"
      />

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Quick Actions -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
          <button class="bg-fsu-primary text-white rounded-lg shadow hover:shadow-lg transition p-6 text-left">
            <h3 class="text-lg font-semibold mb-2">New Record</h3>
            <p class="text-sm opacity-90">Create a new financial record</p>
          </button>
          <button class="bg-white text-gray-900 rounded-lg shadow hover:shadow-lg transition p-6 text-left border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">View Records</h3>
            <p class="text-sm text-gray-600">View all financial records</p>
          </button>
          <button class="bg-white text-gray-900 rounded-lg shadow hover:shadow-lg transition p-6 text-left border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">Analysis</h3>
            <p class="text-sm text-gray-600">View financial analysis and trends</p>
          </button>
        </div>

        <!-- Content Placeholder -->
        <div class="bg-white rounded-lg shadow p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Financial Overview</h2>
          <div class="text-center py-12">
            <p class="text-gray-600">Financial records and analytics will appear here.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '../../Components/Layout/AppLayout.vue'
import ModuleHeader from '../../Components/Common/ModuleHeader.vue'
</script>

<style scoped>
</style>
