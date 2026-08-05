export function formatCurrency(value) {
  const number = Number(value ?? 0)
  return `${number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} DT`
}

// Like formatCurrency, but renders a dash for tiers a sport doesn't offer (null price).
export function formatOptionalCurrency(value) {
  return value === null || value === undefined ? '—' : formatCurrency(value)
}

export function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
