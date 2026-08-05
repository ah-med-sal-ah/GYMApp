/**
 * @param {object} payload
 * @param {string[]} [forceKeys] - keys that must always be appended, even when empty/null
 *   (sent as an empty string). Use this when the backend needs to distinguish
 *   "explicitly cleared" from "left untouched".
 */
export function buildFormData(payload, forceKeys = []) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    const isForced = forceKeys.includes(key)
    if (!isForced && (value === null || value === undefined || value === '')) return

    if (Array.isArray(value)) {
      value.forEach((item) => formData.append(`${key}[]`, item))
      return
    }

    formData.append(key, value ?? '')
  })

  return formData
}
