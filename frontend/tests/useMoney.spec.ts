import { describe, expect, it } from 'vitest'
import { formatCents } from '~/composables/useMoney'

describe('formatCents', () => {
  it('formats USD from integer cents', () => {
    expect(formatCents(1999)).toBe('$19.99')
  })

  it('returns em dash for invalid input', () => {
    expect(formatCents(Number.NaN)).toBe('—')
  })
})
