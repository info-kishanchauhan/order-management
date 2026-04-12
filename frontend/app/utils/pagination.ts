/**
 * Page numbers for a pill-style control (with ellipsis gaps when needed).
 */
export function visiblePages (current: number, last: number): Array<number | 'ellipsis'> {
  if (last < 1) { return [] }
  if (last === 1) { return [1] }
  if (last <= 9) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  const pages = new Set<number>()
  pages.add(1)
  pages.add(last)
  for (let i = current - 1; i <= current + 1; i++) {
    if (i >= 1 && i <= last) { pages.add(i) }
  }

  const sorted = [...pages].sort((a, b) => a - b)
  const out: Array<number | 'ellipsis'> = []
  for (let i = 0; i < sorted.length; i++) {
    const n = sorted[i]
    if (i > 0 && n - sorted[i - 1] > 1) {
      out.push('ellipsis')
    }
    out.push(n)
  }
  return out
}
