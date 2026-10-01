<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
          <Link href="/dashboard" class="text-fsu-primary hover:text-fsu-secondary text-sm font-medium mb-2 inline-block">← Back to Dashboard</Link>
          <h1 class="text-3xl font-bold text-gray-900">📈 Reports Module</h1>
          <p class="text-gray-600 mt-2">View and generate financial reports</p>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Quick Actions -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
          <button class="bg-fsu-primary text-white rounded-lg shadow hover:shadow-lg transition p-6 text-left">
            <h3 class="text-lg font-semibold mb-2">Generate Report</h3>
            <p class="text-sm opacity-90">Create a new financial report</p>
          </button>
          <button class="bg-white text-gray-900 rounded-lg shadow hover:shadow-lg transition p-6 text-left border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">View Reports</h3>
            <p class="text-sm text-gray-600">View all generated reports</p>
          </button>
          <button class="bg-white text-gray-900 rounded-lg shadow hover:shadow-lg transition p-6 text-left border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">Export</h3>
            <p class="text-sm text-gray-600">Export reports to various formats</p>
          </button>
        </div>

        <!-- Report Templates -->
        <div class="grid md:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Daily Report</h3>
            <p class="text-gray-600 text-sm mb-4">Summary of daily financial transactions</p>
            <button class="text-fsu-primary font-medium hover:text-fsu-secondary transition">Generate →</button>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Monthly Report</h3>
            <p class="text-gray-600 text-sm mb-4">Comprehensive monthly financial summary</p>
            <button class="text-fsu-primary font-medium hover:text-fsu-secondary transition">Generate →</button>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Collection Report</h3>
            <p class="text-gray-600 text-sm mb-4">Collection activities and trends</p>
            <button class="text-fsu-primary font-medium hover:text-fsu-secondary transition">Generate →</button>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Disbursement Report</h3>
            <p class="text-gray-600 text-sm mb-4">Disbursement activities and status</p>
            <button class="text-fsu-primary font-medium hover:text-fsu-secondary transition">Generate →</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '../../Components/Layout/AppLayout.vue'
</script>

<style scoped>
</style>
