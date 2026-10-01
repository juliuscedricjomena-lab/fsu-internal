<template>
  <div class="min-h-screen bg-gradient-to-br from-fsu-primary to-fsu-secondary text-white">
    <div class="container mx-auto px-4 py-20">
      <div class="text-center">
        <h1 class="text-5xl font-bold mb-4">FSU Internal Finance System</h1>
        <p class="text-xl mb-8 opacity-90">Comprehensive financial management platform</p>
        
        <div class="flex gap-4 justify-center">
          <Link href="/login" class="px-8 py-3 bg-fsu-accent text-white rounded-lg hover:opacity-90 transition">
            Login
          </Link>
          <Link href="/register" class="px-8 py-3 bg-white text-fsu-primary rounded-lg hover:opacity-90 transition">
            Register
          </Link>
        </div>
      </div>
      
      <div class="grid md:grid-cols-3 gap-8 mt-20">
        <div class="bg-white bg-opacity-10 p-6 rounded-lg backdrop-blur">
          <h3 class="text-2xl font-semibold mb-2">Secure</h3>
          <p>Enterprise-grade security for your financial data</p>
        </div>
        <div class="bg-white bg-opacity-10 p-6 rounded-lg backdrop-blur">
          <h3 class="text-2xl font-semibold mb-2">Fast</h3>
          <p>Real-time processing and instant reporting</p>
        </div>
        <div class="bg-white bg-opacity-10 p-6 rounded-lg backdrop-blur">
          <h3 class="text-2xl font-semibold mb-2">Reliable</h3>
          <p>99.9% uptime guarantee with 24/7 support</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
</style>
