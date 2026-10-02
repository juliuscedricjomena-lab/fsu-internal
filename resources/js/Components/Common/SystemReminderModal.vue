<template>
  <Teleport to="body">
    <div v-if="show" class="reminder-backdrop">
      <div class="reminder-dialog" role="dialog" aria-modal="true" aria-labelledby="reminder-title">
        <!-- Letterhead -->
        <div class="reminder-letterhead">
          <img :src="logoLeft" alt="PNPA Seal" class="reminder-seal" @error="hideImg" />
          <div class="text-center leading-tight px-2">
            <p class="letterhead-sub">Philippine National Police Academy</p>
            <p class="letterhead-title">FINANCE SERVICE UNIT 18</p>
          </div>
          <img :src="logoRight" alt="FSU Seal" class="reminder-seal" @error="hideImg" />
        </div>

        <!-- Title bar -->
        <div class="reminder-titlebar">
          <span class="reminder-lock" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
              <rect x="5" y="11" width="14" height="10" rx="2" />
              <path d="M8 11V7a4 4 0 0 1 8 0v4" />
            </svg>
          </span>
          <h2 id="reminder-title">OFFICIAL SYSTEM REMINDER</h2>
        </div>

        <!-- Body -->
        <div class="reminder-body">
          <div class="reminder-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0">
              <path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" />
            </svg>
            <span>IMPORTANT NOTICE BEFORE SUBMISSION</span>
          </div>

          <p class="reminder-text">
            All data and information entered into this system shall be treated as official government
            records and shall be subject to audit, review, and permanent filing in accordance with
            applicable rules and regulations.
          </p>

          <p class="reminder-text">
            <span class="font-semibold">BEFORE</span> clicking the SUBMIT button, please ensure that you have:
          </p>

          <ul class="reminder-list">
            <li>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="reminder-check"><path d="M20 6 9 17l-5-5" /></svg>
              Verified the accuracy and completeness of all entered information
            </li>
            <li>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="reminder-check"><path d="M20 6 9 17l-5-5" /></svg>
              Confirmed that all figures, dates, reference numbers, and supporting documents are correct and true
            </li>
            <li>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="reminder-check"><path d="M20 6 9 17l-5-5" /></svg>
              Ensured that no erroneous, misleading, or unverified details have been included
            </li>
          </ul>

          <p class="reminder-text">
            Please be reminded that submission of false, incorrect, or misleading information is a serious
            offense and may subject the responsible person to administrative, criminal, or both liabilities
            under existing laws.
          </p>

          <div class="reminder-warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 mt-0.5">
              <path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z" />
            </svg>
            <span>
              ONCE SUBMITTED, THIS RECORD SHALL BE PERMANENTLY RECORDED AND CANNOT BE ALTERED WITHOUT
              PROPER AUTHORITY AND AUDIT TRAIL.
            </span>
          </div>

          <p class="reminder-text font-medium">
            👉 Kindly <span class="font-bold">DOUBLE-CHECK</span> all entries prior to final submission.
          </p>
        </div>

        <!-- Action -->
        <div class="reminder-footer">
          <button type="button" class="understand-btn" @click="$emit('acknowledge')">
            I UNDERSTAND
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import logoLeft from '../../../images/logo.png'
import logoRight from '../../../images/fsu.webp'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['acknowledge'])

const hideImg = (e) => {
  e.target.style.visibility = 'hidden'
}
</script>

<style scoped>
.reminder-backdrop {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(10, 15, 30, 0.65);
  backdrop-filter: blur(2px);
}

.reminder-dialog {
  position: relative;
  width: 100%;
  max-width: 820px;
  max-height: 94vh;
  overflow-y: auto;
  background: #fff;
  border: 3px solid #0b1f4d;
  border-radius: 1rem;
  box-shadow: 0 25px 70px rgba(0, 0, 0, 0.55);
  overflow-x: hidden;
}

/* Decorative gold corner accents (top-left, bottom-right) */
.reminder-dialog::before,
.reminder-dialog::after {
  content: '';
  position: absolute;
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, var(--color-fsu-gold, #c9a227) 0%, transparent 70%);
  opacity: 0.35;
  pointer-events: none;
}

.reminder-dialog::before {
  top: 0;
  left: 0;
  border-top-left-radius: 0.85rem;
}

.reminder-dialog::after {
  bottom: 0;
  right: 0;
  background: linear-gradient(315deg, var(--color-fsu-gold, #c9a227) 0%, transparent 70%);
  border-bottom-right-radius: 0.85rem;
}

.reminder-letterhead {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 4px solid var(--color-fsu-gold, #c9a227);
}

.reminder-seal {
  height: 4.5rem;
  width: 4.5rem;
  object-fit: contain;
  flex-shrink: 0;
}

.letterhead-sub {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1f2937;
}

.letterhead-title {
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: 0.02em;
  color: #0b1f4d;
}

.reminder-titlebar {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.5rem;
  background: linear-gradient(90deg, #0b1f4d 0%, #142c66 100%);
  color: #fff;
}

.reminder-lock {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 2rem;
  width: 2rem;
  border-radius: 9999px;
  border: 2px solid #fff;
  background: rgba(255, 255, 255, 0.08);
}

.reminder-titlebar h2 {
  font-size: 1.3rem;
  font-weight: 800;
  letter-spacing: 0.04em;
}

.reminder-body {
  position: relative;
  z-index: 1;
  padding: 1.5rem 1.75rem;
  color: #374151;
}

.reminder-section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.8rem;
}

.reminder-text {
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 0.9rem;
}

.reminder-list {
  margin: 0 0 0.75rem;
  padding: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.reminder-list li {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  font-size: 0.9rem;
  line-height: 1.5;
}

.reminder-check {
  height: 1rem;
  width: 1rem;
  color: #15803d;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.reminder-warning {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  font-size: 0.85rem;
  font-weight: 700;
  color: #7c2d12;
  line-height: 1.5;
  margin-bottom: 0.9rem;
  padding: 0.75rem 0.9rem;
  background: #fff7ed;
  border-left: 4px solid var(--color-fsu-gold, #c9a227);
  border-radius: 0.4rem;
}

.reminder-footer {
  display: flex;
  justify-content: center;
  padding: 1rem 1.5rem 1.5rem;
}

.understand-btn {
  min-width: 220px;
  padding: 0.75rem 1.5rem;
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: 0.03em;
  color: #fff;
  border-radius: 0.6rem;
  background: linear-gradient(180deg, #ff3b3b, #d61f1f);
  border: 1px solid #b91c1c;
  box-shadow: 0 8px 18px rgba(214, 31, 31, 0.45);
  transition: transform 0.05s, opacity 0.15s;
}

.understand-btn:hover {
  opacity: 0.93;
}

.understand-btn:active {
  transform: translateY(1px);
}

/* Mobile responsive */
@media (max-width: 640px) {
  .reminder-backdrop {
    padding: 0.5rem;
  }

  .reminder-dialog {
    border-width: 2px;
    border-radius: 0.75rem;
  }

  .reminder-dialog::before,
  .reminder-dialog::after {
    width: 70px;
    height: 70px;
  }

  .reminder-letterhead {
    gap: 0.5rem;
    padding: 0.85rem 0.9rem;
  }

  .reminder-seal {
    height: 3rem;
    width: 3rem;
  }

  .letterhead-sub {
    font-size: 0.7rem;
  }

  .letterhead-title {
    font-size: 1.05rem;
  }

  .reminder-titlebar {
    padding: 0.7rem 0.9rem;
    gap: 0.55rem;
  }

  .reminder-titlebar h2 {
    font-size: 1rem;
  }

  .reminder-lock {
    height: 1.65rem;
    width: 1.65rem;
  }

  .reminder-body {
    padding: 1.1rem 1rem;
  }

  .reminder-text,
  .reminder-list li {
    font-size: 0.82rem;
  }

  .understand-btn {
    width: 100%;
    min-width: 0;
    font-size: 1rem;
  }
}
</style>
