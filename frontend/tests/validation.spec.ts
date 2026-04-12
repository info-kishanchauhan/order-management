import { describe, expect, it } from 'vitest'
import {
  createOrderSchema,
  firstMessagePerField,
  loginSchema,
  validateWith
} from '~/validation'

describe('validateWith', () => {
  it('returns ok with stripped unknown keys', () => {
    const r = validateWith(loginSchema, {
      email: '  a@b.co  ',
      password: 'secret',
      extra: 1
    })
    expect(r.ok).toBe(true)
    if (r.ok) {
      expect(r.value.email).toBe('a@b.co')
      expect('extra' in r.value).toBe(false)
    }
  })

  it('returns field errors for login', () => {
    const r = validateWith(loginSchema, { email: 'nope', password: '' })
    expect(r.ok).toBe(false)
    if (!r.ok) {
      const flat = firstMessagePerField(r.errors)
      expect(flat.email || flat.password).toBeTruthy()
    }
  })

  it('validates create order payload', () => {
    const bad = validateWith(createOrderSchema, {
      customer_name: 'x',
      item_name: 'Item',
      price_cents: -1
    })
    expect(bad.ok).toBe(false)

    const good = validateWith(createOrderSchema, {
      customer_name: 'Jane',
      item_name: 'Widget',
      price_cents: 0
    })
    expect(good.ok).toBe(true)
  })
})
