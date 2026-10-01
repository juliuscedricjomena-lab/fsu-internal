import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUIStore = defineStore('ui', () => {
  // State
  const notifications = ref([])
  const modals = ref({})
  const isLoading = ref(false)
  const sidebarOpen = ref(false)

  // Notification management
  const addNotification = (notification) => {
    const id = Date.now()
    const fullNotification = {
      id,
      type: 'info', // 'success', 'error', 'warning', 'info'
      duration: 5000,
      ...notification,
    }

    notifications.value.push(fullNotification)

    // Auto-remove after duration
    if (fullNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, fullNotification.duration)
    }

    return id
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearNotifications = () => {
    notifications.value = []
  }

  const showSuccess = (message, duration = 5000) => {
    return addNotification({
      type: 'success',
      message,
      duration,
    })
  }

  const showError = (message, duration = 5000) => {
    return addNotification({
      type: 'error',
      message,
      duration,
    })
  }

  const showWarning = (message, duration = 5000) => {
    return addNotification({
      type: 'warning',
      message,
      duration,
    })
  }

  const showInfo = (message, duration = 5000) => {
    return addNotification({
      type: 'info',
      message,
      duration,
    })
  }

  // Modal management
  const openModal = (modalName, data = null) => {
    modals.value[modalName] = {
      isOpen: true,
      data,
    }
  }

  const closeModal = (modalName) => {
    if (modals.value[modalName]) {
      modals.value[modalName].isOpen = false
    }
  }

  const toggleModal = (modalName, data = null) => {
    if (modals.value[modalName]?.isOpen) {
      closeModal(modalName)
    } else {
      openModal(modalName, data)
    }
  }

  const getModalData = (modalName) => {
    return modals.value[modalName]?.data || null
  }

  const isModalOpen = (modalName) => {
    return modals.value[modalName]?.isOpen || false
  }

  // Loading state
  const setLoading = (state) => {
    isLoading.value = state
  }

  // Sidebar state
  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
  }

  const setSidebarOpen = (state) => {
    sidebarOpen.value = state
  }

  return {
    // State
    notifications,
    modals,
    isLoading,
    sidebarOpen,

    // Notification methods
    addNotification,
    removeNotification,
    clearNotifications,
    showSuccess,
    showError,
    showWarning,
    showInfo,

    // Modal methods
    openModal,
    closeModal,
    toggleModal,
    getModalData,
    isModalOpen,

    // Loading methods
    setLoading,

    // Sidebar methods
    toggleSidebar,
    setSidebarOpen,
  }
})
