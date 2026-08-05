export function extractErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
  return error?.response?.data?.message || fallback
}

export function extractFieldErrors(error) {
  const errors = error?.response?.data?.errors
  if (!errors || typeof errors !== 'object') return {}

  const flat = {}
  for (const [field, messages] of Object.entries(errors)) {
    flat[field] = Array.isArray(messages) ? messages[0] : messages
  }
  return flat
}
