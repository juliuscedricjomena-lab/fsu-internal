import { h } from 'vue'

/**
 * Shared module theming: per-module gradient colors and SVG icons.
 * Used by the dashboard grid and the individual module pages so the
 * iconography stays consistent across the app.
 */

const svg = (paths) => () =>
  h(
    'svg',
    {
      viewBox: '0 0 24 24',
      fill: 'none',
      stroke: 'currentColor',
      'stroke-width': '2',
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round',
    },
    paths.map((d) => h('path', { d }))
  )

export const moduleIcons = {
  Collection: svg(['M12 1v22', 'M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6']),
  Finance: svg(['M3 3v18h18', 'M7 14l4-4 3 3 5-6']),
  Disbursement: svg(['M12 1v22', 'M5 8l7-7 7 7', 'M5 16l7 7 7-7']),
  Reports: svg([
    'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z',
    'M14 2v6h6',
    'M8 13h8',
    'M8 17h8',
    'M8 9h2',
  ]),
  Admin: svg([
    'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z',
    'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
  ]),
  Remittance: svg([
    'M22 2 11 13',
    'M22 2 15 22l-4-9-9-4 20-7z',
  ]),
  'Pay & Allowances': svg([
    'M12 2v20',
    'M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6',
  ]),
}

export const moduleGradients = {
  Collection: ['#0d9488', '#14b8a6'],
  Finance: ['#2563eb', '#3b82f6'],
  Disbursement: ['#7c3aed', '#a855f7'],
  Reports: ['#d97706', '#f59e0b'],
  Admin: ['#9f1239', '#6d1a1a'],
  Remittance: ['#0369a1', '#0ea5e9'],
  'Pay & Allowances': ['#047857', '#10b981'],
}

export const iconFor = (name) => moduleIcons[name] || moduleIcons.Admin
export const gradientFor = (name) => moduleGradients[name] || moduleGradients.Admin
