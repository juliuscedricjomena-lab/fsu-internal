<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
          <Link href="/dashboard" class="text-fsu-primary hover:text-fsu-secondary text-sm font-medium mb-2 inline-block">← Back to Dashboard</Link>
          <h1 class="text-3xl font-bold text-gray-900">💸 Disbursement Module</h1>
          <p class="text-gray-600 mt-2">Manage fund disbursements and payments</p>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Quick Actions -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
          <button class="bg-fsu-primary text-white rounded-lg shadow hover:shadow-lg transition p-6 text-left">
            <h3 class="text-lg font-semibold mb-2">New Disbursement</h3>
            <p class="text-sm opacity-90">Create a new disbursement request</p>
          </button>
          <button class="bg-white text-gray-900 rounded-lg shadow hover:shadow-lg transition p-6 text-left border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">View Disbursements</h3>
            <p class="text-sm text-gray-600">View all disbursement requests</p>
          </button>
          <button class="bg-white text-gray-900 rounded-lg shadow hover:shadow-lg transition p-6 text-left border border-gray-200">
            <h3 class="text-lg font-semibold mb-2">Approvals</h3>
            <p class="text-sm text-gray-600">Pending disbursement approvals</p>
          </button>
        </div>

        <!-- Content Placeholder -->
        <div class="bg-white rounded-lg shadow p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Disbursement Requests</h2>
          <div class="text-center py-12">
            <p class="text-gray-600">Disbursement requests and approvals will appear here.</p>
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
