<template>
  <AppLayout>
    <div class="page">
      <!-- Mandatory system reminder modal -->
      <SystemReminderModal :show="!understood" @acknowledge="acknowledge" />

      <!-- Hero -->
      <header class="hero">
        <div class="hero-mesh" aria-hidden="true"></div>
        <div class="hero-inner max-w-7xl mx-auto px-6">
          <div class="flex items-center gap-4">
            <div class="hero-logo-ring">
              <img :src="logoLeft" alt="PNPA" class="hero-logo" @error="hideImg" />
            </div>
            <div>
              <p class="hero-eyebrow">Philippine National Police Academy</p>
              <h1 class="hero-title">Welcome back, {{ firstName }}</h1>
              <p class="hero-sub">
                {{ user.designation || 'FSU Employee' }}
                <span class="hero-dot">•</span>
                <span class="hero-role">{{ user.role?.name }}</span>
              </p>
            </div>
          </div>

          <div class="hero-meta">
            <div class="hero-logo-ring hidden sm:flex">
              <img :src="logoRight" alt="FSU" class="hero-logo" @error="hideImg" />
            </div>
            <div class="text-right">
              <p class="hero-meta-label">Signed in</p>
              <p class="hero-meta-value">{{ today }}</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="max-w-7xl mx-auto px-6 -mt-10 pb-20">
        <!-- Stat tiles -->
        <section class="stat-row">
          <div v-for="(s, i) in stats" :key="s.label" class="stat-tile" :style="{ animationDelay: `${i * 60}ms` }">
            <div class="stat-icon" :class="s.cls">
              <component :is="s.icon" />
            </div>
            <div>
              <p class="stat-label">{{ s.label }}</p>
              <p class="stat-value" :class="s.valueCls">{{ s.value }}</p>
            </div>
          </div>
        </section>

        <!-- Modules -->
        <section class="flex items-end justify-between mb-6 mt-12">
          <div>
            <h2 class="section-title">Your Modules</h2>
            <p class="section-sub">Access the modules available to your role</p>
          </div>
          <span class="modules-count">{{ modules.length }} available</span>
        </section>

        <div v-if="modules.length > 0" class="bento-grid">
          <Link
            v-for="(module, i) in modules"
            :key="module.name"
            :href="`/modules/${module.path}`"
            class="module-card"
            :style="{ ...cardStyle(module.name), animationDelay: `${i * 70}ms` }"
            @mousemove="onCardMove"
            @mouseleave="onCardLeave"
          >
            <!-- pointer spotlight -->
            <span class="card-spotlight" aria-hidden="true"></span>
            <!-- watermark glyph -->
            <span class="card-watermark" aria-hidden="true"><component :is="iconFor(module.name)" /></span>

            <div class="module-top">
              <div class="module-icon" :style="chipStyle(module.name)">
                <component :is="iconFor(module.name)" />
              </div>
              <span class="module-pill">Module</span>
            </div>

            <h3 class="module-name">{{ module.name }}</h3>
            <p class="module-desc">{{ module.description }}</p>

            <div class="module-foot">
              <span class="module-cta">Open module</span>
              <span class="module-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
              </span>
            </div>
          </Link>
        </div>

        <div v-else class="empty-state">
          <p>You don't have access to any modules yet. Please contact your administrator.</p>
        </div>
      </main>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, h } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '../../Components/Layout/AppLayout.vue'
import SystemReminderModal from '../../Components/Common/SystemReminderModal.vue'
import { iconFor, gradientFor } from '../../Composables/moduleTheme'
import logoLeft from '../../../images/logo.png'
import logoRight from '../../../images/fsu.webp'

const props = defineProps({
  modules: Array,
  user: Object,
})

// Modal shows once per login session (sessionStorage survives refresh, clears on new session).
const ackKey = `fsu_reminder_ack_${props.user?.id ?? 'guest'}`
const understood = ref(
  typeof window !== 'undefined' && window.sessionStorage.getItem(ackKey) === '1'
)
const acknowledge = () => {
  understood.value = true
  if (typeof window !== 'undefined') window.sessionStorage.setItem(ackKey, '1')
}

const firstName = computed(() => props.user?.name?.split(' ').slice(-2).join(' ') || props.user?.name || 'User')
const today = new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })

/* ---- icons: for building the stat tiles ---- */
const svg = (paths) => () =>
  h(
    'svg',
    { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': '2', 'stroke-linecap': 'round', 'stroke-linejoin': 'round' },
    paths.map((d) => h('path', { d }))
  )

/* ---- per-module gradients + icons come from the shared theme ---- */
const cardStyle = (name) => {
  const [a, b] = gradientFor(name)
  return { '--c1': a, '--c2': b }
}
const chipStyle = (name) => {
  const [a, b] = gradientFor(name)
  return { background: `linear-gradient(135deg, ${a}, ${b})` }
}

/* ---- stats ---- */
const capitalizeStatus = (status) => (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown')
const formatDate = (date) =>
  date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A'

const stats = computed(() => [
  { label: 'Role', value: props.user?.role?.name || 'N/A', cls: 'si-role', icon: svg(['M12 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8z', 'M4 20c0-4 4-6 8-6s8 2 8 6']) },
  { label: 'Status', value: capitalizeStatus(props.user?.status), valueCls: 'text-emerald-600', cls: 'si-status', icon: svg(['M20 6 9 17l-5-5']) },
  { label: 'Modules', value: props.modules?.length ?? 0, cls: 'si-modules', icon: svg(['M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z']) },
  { label: 'Member Since', value: formatDate(props.user?.created_at), cls: 'si-since', icon: svg(['M3 4h18v18H3zM16 2v4M8 2v4M3 10h18']) },
])

/* ---- pointer spotlight ---- */
const onCardMove = (e) => {
  const el = e.currentTarget
  const r = el.getBoundingClientRect()
  el.style.setProperty('--mx', `${e.clientX - r.left}px`)
  el.style.setProperty('--my', `${e.clientY - r.top}px`)
  const rx = ((e.clientY - r.top) / r.height - 0.5) * -5
  const ry = ((e.clientX - r.left) / r.width - 0.5) * 5
  el.style.setProperty('--rx', `${rx}deg`)
  el.style.setProperty('--ry', `${ry}deg`)
}
const onCardLeave = (e) => {
  const el = e.currentTarget
  el.style.setProperty('--rx', '0deg')
  el.style.setProperty('--ry', '0deg')
}

const hideImg = (e) => {
  e.target.style.visibility = 'hidden'
}
</script>

<style scoped>
.page {
  min-height: 100vh;
  background:
    radial-gradient(1200px 500px at 100% -10%, #fbe9e9 0%, transparent 55%),
    radial-gradient(900px 500px at -10% 10%, #eef2ff 0%, transparent 50%),
    #f6f7fb;
}

/* ---------- Hero ---------- */
.hero {
  position: relative;
  overflow: hidden;
  color: #fff;
  padding-bottom: 3.25rem;
  background: linear-gradient(120deg, #3f0d0d 0%, #6d1a1a 55%, #8b1e1e 100%);
}
.hero-mesh {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(600px 300px at 15% 20%, rgba(201, 162, 39, 0.35), transparent 60%),
    radial-gradient(500px 260px at 85% 0%, rgba(255, 255, 255, 0.18), transparent 60%),
    radial-gradient(700px 400px at 60% 120%, rgba(0, 0, 0, 0.35), transparent 60%);
  pointer-events: none;
}
.hero-inner {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding-top: 2rem;
}
.hero-logo-ring {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 4rem;
  width: 4rem;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(6px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  flex-shrink: 0;
}
.hero-logo { height: 3rem; width: 3rem; object-fit: contain; }
.hero-eyebrow {
  font-size: 0.72rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.72);
}
.hero-title { font-size: 1.9rem; font-weight: 800; line-height: 1.1; }
.hero-sub { font-size: 0.92rem; color: rgba(255, 255, 255, 0.85); margin-top: 0.2rem; }
.hero-dot { margin: 0 0.4rem; color: var(--color-fsu-gold, #c9a227); }
.hero-role { font-weight: 700; color: var(--color-fsu-gold, #c9a227); }
.hero-meta { position: relative; display: flex; align-items: center; gap: 1rem; }
.hero-meta-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.07em; color: rgba(255, 255, 255, 0.65); }
.hero-meta-value { font-size: 1.05rem; font-weight: 700; }

/* ---------- Stats ---------- */
.stat-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}
@media (min-width: 1024px) { .stat-row { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

.stat-tile {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.8);
  border-radius: 1.1rem;
  padding: 1.1rem 1.25rem;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
  opacity: 0;
  animation: rise 0.5s ease forwards;
}
.stat-tile:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12); }
.stat-icon {
  display: inline-flex; align-items: center; justify-content: center;
  height: 2.7rem; width: 2.7rem; border-radius: 0.85rem; flex-shrink: 0; color: #fff;
}
.stat-icon svg { height: 1.3rem; width: 1.3rem; }
.si-role { background: linear-gradient(135deg, #0e7490, #06b6d4); }
.si-status { background: linear-gradient(135deg, #059669, #10b981); }
.si-modules { background: linear-gradient(135deg, #2563eb, #60a5fa); }
.si-since { background: linear-gradient(135deg, #b45309, #f59e0b); }
.stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; font-weight: 700; }
.stat-value { font-size: 1.15rem; font-weight: 800; color: #0f172a; line-height: 1.2; }

/* ---------- Section head ---------- */
.section-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; }
.section-sub { font-size: 0.88rem; color: #64748b; }
.modules-count {
  font-size: 0.75rem; font-weight: 800; color: #6d1a1a;
  background: linear-gradient(135deg, #fbe9e9, #f6d5d5);
  padding: 0.35rem 0.8rem; border-radius: 9999px;
  border: 1px solid #f1c7c7;
}

/* ---------- Bento module grid ---------- */
.bento-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 1.25rem;
}
@media (min-width: 640px) { .bento-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1024px) { .bento-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

.module-card {
  position: relative;
  display: flex;
  flex-direction: column;
  isolation: isolate;
  background: #fff;
  border: 1px solid #eef0f3;
  border-radius: 1.3rem;
  padding: 1.5rem;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  transform: perspective(900px) rotateX(var(--rx, 0)) rotateY(var(--ry, 0)) translateZ(0);
  transition: transform 0.2s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  opacity: 0;
  animation: rise 0.55s ease forwards;
  text-decoration: none;
}
.module-card::before {
  content: '';
  position: absolute; inset: 0; border-radius: inherit; padding: 1px;
  background: linear-gradient(135deg, var(--c1), var(--c2));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
  opacity: 0; transition: opacity 0.25s ease;
}
.module-card:hover {
  box-shadow: 0 24px 50px color-mix(in srgb, var(--c1) 26%, transparent);
  border-color: transparent;
}
.module-card:hover::before { opacity: 1; }

.card-spotlight {
  position: absolute; inset: 0; z-index: 0; border-radius: inherit;
  background: radial-gradient(260px circle at var(--mx, 50%) var(--my, 0),
    color-mix(in srgb, var(--c1) 18%, transparent), transparent 65%);
  opacity: 0; transition: opacity 0.25s ease; pointer-events: none;
}
.module-card:hover .card-spotlight { opacity: 1; }

.card-watermark {
  position: absolute; right: -1.25rem; bottom: -1.75rem; z-index: 0;
  color: var(--c1); opacity: 0.06; pointer-events: none;
  transition: transform 0.3s ease, opacity 0.25s ease;
}
.card-watermark svg { height: 9rem; width: 9rem; }
.module-card:hover .card-watermark { transform: scale(1.08) rotate(-6deg); opacity: 0.1; }

.module-top { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.1rem; }
.module-icon {
  display: inline-flex; align-items: center; justify-content: center;
  height: 3.1rem; width: 3.1rem; border-radius: 1rem; color: #fff;
  box-shadow: 0 8px 20px color-mix(in srgb, var(--c1) 40%, transparent);
  transition: transform 0.2s ease;
}
.module-icon svg { height: 1.55rem; width: 1.55rem; }
.module-card:hover .module-icon { transform: scale(1.1) rotate(-4deg); }
.module-pill {
  font-size: 0.65rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase;
  color: #94a3b8; background: #f1f5f9; padding: 0.28rem 0.6rem; border-radius: 9999px;
}
.module-name { position: relative; z-index: 1; font-size: 1.2rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem; }
.module-desc { position: relative; z-index: 1; font-size: 0.86rem; line-height: 1.55; color: #64748b; flex: 1; margin-bottom: 1.1rem; }
.module-foot { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; }
.module-cta {
  font-size: 0.82rem; font-weight: 800;
  background: linear-gradient(135deg, var(--c1), var(--c2));
  -webkit-background-clip: text; background-clip: text; color: transparent;
}
.module-arrow {
  display: inline-flex; align-items: center; justify-content: center;
  height: 2rem; width: 2rem; border-radius: 9999px; color: #fff;
  background: linear-gradient(135deg, var(--c1), var(--c2));
  transform: translateX(0); transition: transform 0.2s ease;
}
.module-arrow svg { height: 1rem; width: 1rem; }
.module-card:hover .module-arrow { transform: translateX(4px); }

.empty-state {
  background: #fffbeb; border: 1px solid #fde68a; border-radius: 1.1rem;
  padding: 1.5rem; color: #92400e; font-size: 0.9rem;
}

@keyframes rise {
  from { opacity: 0; transform: translateY(14px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .stat-tile, .module-card { animation: none; opacity: 1; }
  .module-card { transform: none; }
}

/* Responsive hero */
@media (max-width: 640px) {
  .hero-inner { flex-direction: column; align-items: flex-start; gap: 0.9rem; }
  .hero-title { font-size: 1.45rem; }
  .hero-meta { align-self: flex-end; }
}
</style>
