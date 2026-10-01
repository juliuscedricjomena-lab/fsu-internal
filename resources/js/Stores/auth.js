import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const isAuthenticated = ref(false)
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const userRole = computed(() => user.value?.role?.name || null)
  const userPermissions = computed(() => {
    if (!user.value?.role) return []
    return user.value.role.permissions?.map(p => p.name) || []
  })

  // Methods
  const setUser = (userData) => {
    user.value = userData
    isAuthenticated.value = !!userData
  }

  const clearUser = () => {
    user.value = null
    isAuthenticated.value = false
    error.value = null
  }

  const hasRole = (role) => {
    if (typeof role === 'string') {
      return userRole.value === role
    }
    if (Array.isArray(role)) {
      return role.includes(userRole.value)
    }
    return false
  }

  const hasPermission = (permission) => {
    if (typeof permission === 'string') {
      return userPermissions.value.includes(permission)
    }
    if (Array.isArray(permission)) {
      return permission.some(p => userPermissions.value.includes(p))
    }
    return false
  }

  const setLoading = (state) => {
    loading.value = state
  }

  const setError = (message) => {
    error.value = message
  }

  const clearError = () => {
    error.value = null
  }

  const fetchCurrentUser = async () => {
    setLoading(true)
    clearError()
    try {
      const response = await fetch('/me')
      if (!response.ok) {
        throw new Error('Failed to fetch user')
      }
      const data = await response.json()
      setUser(data)
      return data
    } catch (err) {
      setError(err.message)
      clearUser()
      return null
    } finally {
      setLoading(false)
    }
  }

  return {
    // State
    user,
    isAuthenticated,
    loading,
    error,

    // Computed
    userRole,
    userPermissions,

    // Methods
    setUser,
    clearUser,
    hasRole,
    hasPermission,
    setLoading,
    setError,
    clearError,
    fetchCurrentUser,
  }
})
