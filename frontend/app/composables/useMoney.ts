export function formatCents (cents: number, currency = 'USD'): string {
  if (!Number.isFinite(cents) || cents < 0) {
    return '—'
  }
  return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(cents / 100)
}

export function useMoney () {
  return { formatCents }
}
