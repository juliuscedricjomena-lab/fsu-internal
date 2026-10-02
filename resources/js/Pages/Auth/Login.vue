<template>
  <div class="login-page">
    <!-- Background photo with dark overlay -->
    <div class="login-bg" :style="bgStyle"></div>
    <div class="login-overlay"></div>

    <!-- Login card -->
    <div class="login-wrapper">
      <div class="login-card">
        <!-- Header: dual logos + unit name -->
        <div class="flex items-center gap-3 mb-5">
          <img
            :src="pnpaLogo"
            alt="PNPA Seal"
            class="h-11 w-11 rounded-full object-cover shrink-0 bg-white/60"
            @error="onLogoError"
          />
          <div class="leading-tight min-w-0">
            <p class="text-[10px] font-semibold tracking-wide text-gray-700 uppercase">
              Philippine National Police Academy
            </p>
            <p class="text-sm font-extrabold text-fsu-primary uppercase">
              Finance Service Unit 18
            </p>
          </div>
          <img
            :src="pnpLogo"
            alt="PNP Seal"
            class="h-11 w-11 rounded-full object-cover shrink-0 bg-white/60 ml-auto"
            @error="onLogoError"
          />
        </div>

        <!-- Welcome text -->
        <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
        <p class="text-sm italic text-gray-600 mb-5">
          Sign in to manage cadet allowances and expenses.
        </p>

        <!-- Lockout / error banner -->
        <div v-if="errors.login" class="lock-banner">
          <svg class="h-4 w-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="5" y="11" width="14" height="10" rx="2" />
            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
          </svg>
          <span>{{ errors.login }}</span>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <!-- Username or Email -->
          <div>
            <label class="field-label">Username or Email</label>
            <div class="field">
              <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
              </svg>
              <input
                v-model="form.login"
                type="text"
                autocomplete="username"
                placeholder="admin"
                class="field-input"
                required
              />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="field-label">Password</label>
            <div class="field">
              <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="5" y="11" width="14" height="10" rx="2" />
                <path d="M8 11V7a4 4 0 0 1 8 0v4" />
              </svg>
              <input
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                placeholder="••••••••"
                class="field-input"
                required
              />
            </div>
          </div>

          <!-- Remember me + forgot password -->
          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
              <input
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-400 text-fsu-primary focus:ring-fsu-primary"
              />
              Remember me
            </label>
            <a href="/forgot-password" class="text-sm font-semibold text-fsu-primary hover:underline">
              Forgot password?
            </a>
          </div>

          <!-- Sign in -->
          <button type="submit" :disabled="loading" class="sign-in-btn">
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// Logos bundled via Vite from resources/images/.
import logoLeft from '../../../images/logo.png'
import logoRight from '../../../images/fsu.webp'

// Background photo — drop the real file into public/images/.
// Falls back to the maroon gradient when missing.
const bgImage = '/images/login-bg.jpg'
const pnpaLogo = ref(logoLeft)
const pnpLogo = ref(logoRight)

const bgStyle = computed(() => ({
  backgroundImage: `url('${bgImage}')`,
}))

const form = reactive({
  login: '',
  password: '',
  remember: false,
})

const loading = ref(false)
const page = usePage()

// Server-side validation errors shared by Inertia.
const errors = computed(() => page.props.errors ?? {})

const handleLogin = () => {
  loading.value = true
  router.post('/login', form, {
    onFinish: () => {
      loading.value = false
    },
  })
}

// Hide broken logo images gracefully if assets are missing.
const onLogoError = (e) => {
  e.target.style.visibility = 'hidden'
}
</script>

<style scoped>
.login-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  /* Maroon fallback gradient shown when the photo is missing */
  background-color: #4a0f0f;
}

.login-overlay {
  position: absolute;
  inset: 0;
  background: rgba(20, 6, 6, 0.4);
}

.login-wrapper {
  position: relative;
  z-index: 10;
  width: 100%;
  padding: 1.5rem;
  display: flex;
  justify-content: center;
}

.login-card {
  width: 100%;
  max-width: 380px;
  padding: 1.75rem;
  border-radius: 1rem;
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.field-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.35rem;
}

.field {
  position: relative;
  display: flex;
  align-items: center;
}

.field-icon {
  position: absolute;
  left: 0.75rem;
  height: 1.1rem;
  width: 1.1rem;
  color: #9ca3af;
  pointer-events: none;
}

.field-input {
  width: 100%;
  padding: 0.6rem 0.9rem 0.6rem 2.4rem;
  border: 1px solid #d1d5db;
  border-radius: 0.6rem;
  background: rgba(255, 255, 255, 0.9);
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.field-input:focus {
  border-color: var(--color-fsu-primary);
  box-shadow: 0 0 0 2px rgba(109, 26, 26, 0.25);
}

.lock-banner {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 0.65rem 0.75rem;
  margin-bottom: 1rem;
  font-size: 0.8rem;
  color: #991b1b;
  background: #fef2f2;
  border: 1px solid #f3c4c4;
  border-radius: 0.6rem;
}

.sign-in-btn {
  width: 100%;
  padding: 0.7rem;
  margin-top: 0.25rem;
  font-weight: 700;
  color: #fff;
  border-radius: 0.6rem;
  background: linear-gradient(180deg, var(--color-fsu-primary), var(--color-fsu-secondary));
  box-shadow: 0 6px 16px rgba(74, 15, 15, 0.4);
  transition: opacity 0.15s, transform 0.05s;
}

.sign-in-btn:hover {
  opacity: 0.93;
}

.sign-in-btn:active {
  transform: translateY(1px);
}

.sign-in-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
