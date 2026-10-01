import { useAuthStore } from '@/Stores/auth'
import { useUIStore } from '@/Stores/ui'

/**
 * Composable for accessing all stores together
 * Usage: const { auth, ui } = useAppStore()
 */
export function useAppStore() {
  const auth = useAuthStore()
  const ui = useUIStore()

  return {
    auth,
    ui,
  }
}

/**
 * Composable for accessing auth store
 * Usage: const { user, hasRole, hasPermission } = useAuth()
 */
export function useAuth() {
  return useAuthStore()
}

/**
 * Composable for accessing UI store
 * Usage: const { showSuccess, showError, openModal } = useUI()
 */
export function useUI() {
  return useUIStore()
}
