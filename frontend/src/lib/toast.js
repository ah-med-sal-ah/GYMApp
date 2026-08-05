import { reactive } from 'vue'

const toasts = reactive([])
let nextId = 1

function push(type, message, timeout = 4000) {
  const id = nextId++
  toasts.push({ id, type, message })

  if (timeout > 0) {
    setTimeout(() => dismiss(id), timeout)
  }

  return id
}

function dismiss(id) {
  const index = toasts.findIndex((toast) => toast.id === id)
  if (index !== -1) toasts.splice(index, 1)
}

export function useToast() {
  return {
    toasts,
    dismiss,
    success: (message) => push('success', message),
    error: (message) => push('error', message),
  }
}
