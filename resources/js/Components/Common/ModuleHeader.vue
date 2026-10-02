<template>
  <div class="module-header" :style="headerStyle">
    <div class="module-header-mesh" aria-hidden="true"></div>
    <div class="max-w-7xl mx-auto px-6 py-7 relative">
      <Link :href="backTo" class="back-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6" /></svg>
        {{ backLabel }}
      </Link>

      <div class="flex items-center gap-4 mt-3">
        <div class="header-icon">
          <component :is="icon" />
        </div>
        <div>
          <h1 class="header-title">{{ title }}</h1>
          <p class="header-sub">{{ subtitle }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { iconFor, gradientFor } from '../../Composables/moduleTheme'

const props = defineProps({
  module: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  // Where the back link points. Defaults to the dashboard so top-level
  // module pages keep their existing behavior; deeper pages override this
  // to point at their parent (e.g. the module landing page).
  backTo: { type: String, default: '/dashboard' },
  backLabel: { type: String, default: 'Back to Dashboard' },
})

const icon = computed(() => iconFor(props.module))

const headerStyle = computed(() => {
  const [a, b] = gradientFor(props.module)
  return { '--c1': a, '--c2': b }
})
</script>

<style scoped>
.module-header {
  position: relative;
  overflow: hidden;
  color: #fff;
  background: linear-gradient(120deg, var(--c1), var(--c2));
}

.module-header-mesh {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(500px 240px at 12% 10%, rgba(255, 255, 255, 0.22), transparent 60%),
    radial-gradient(600px 300px at 90% 120%, rgba(0, 0, 0, 0.28), transparent 60%);
  pointer-events: none;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.85);
  transition: color 0.15s ease;
}
.back-link:hover { color: #fff; }
.back-link svg { height: 1rem; width: 1rem; }

.header-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 3.5rem;
  width: 3.5rem;
  border-radius: 1rem;
  background: rgba(255, 255, 255, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(6px);
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
  flex-shrink: 0;
}
.header-icon svg { height: 1.75rem; width: 1.75rem; }

.header-title { font-size: 1.75rem; font-weight: 800; line-height: 1.1; }
.header-sub { font-size: 0.9rem; color: rgba(255, 255, 255, 0.85); margin-top: 0.15rem; }

@media (max-width: 640px) {
  .header-title { font-size: 1.4rem; }
  .header-icon { height: 3rem; width: 3rem; }
}
</style>
