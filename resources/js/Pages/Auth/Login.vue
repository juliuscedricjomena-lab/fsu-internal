<template>
  <div class="min-h-screen bg-gradient-to-br from-fsu-primary to-fsu-secondary flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-lg shadow-xl p-8">
        <h1 class="text-3xl font-bold text-fsu-primary mb-2">FSU Internal</h1>
        <p class="text-gray-600 mb-8">Finance System Login</p>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Field -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="your@email.com"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-fsu-primary focus:border-transparent outline-none transition"
              required
            />
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          </div>

          <!-- Password Field -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-fsu-primary focus:border-transparent outline-none transition"
              required
            />
            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
          </div>

          <!-- Error Message -->
          <div v-if="errors.email" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 text-sm">{{ errors.email }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-fsu-accent text-white font-semibold py-2 rounded-lg hover:opacity-90 transition disabled:opacity-50"
          >
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <!-- Info Box -->
        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-blue-900 text-sm font-medium">Demo Credentials:</p>
          <p class="text-blue-800 text-sm">Email: admin@fsu.local</p>
          <p class="text-blue-800 text-sm">Password: password123</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center mt-8 text-white">
        <p class="text-sm">© 2026 FSU Internal Finance System</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = reactive({
  email: '',
  password: '',
})

const errors = ref({})
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  errors.value = {}

  router.post('/login', form, {
    onError: (errors) => {
      Object.assign(errors.value, errors)
      loading.value = false
    },
  })
}
</script>

<style scoped>
</style>
