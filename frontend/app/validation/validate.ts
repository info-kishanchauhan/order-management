import type { ObjectSchema, ValidationError } from 'joi'
import type { FieldErrors, ValidationResult } from './types'

/**
 * Converts Joi details to `{ field: [messages] }` for forms and server-aligned display.
 */
export function joiDetailsToFieldErrors (error: ValidationError | undefined): FieldErrors {
  const out: FieldErrors = {}
  if (!error?.details?.length) {
    return out
  }
  for (const detail of error.details) {
    const path = detail.path
    const key = path.length ? path.map(String).join('.') : '_root'
    if (!out[key]) {
      out[key] = []
    }
    out[key].push(detail.message.replace(/"/g, ''))
  }
  return out
}

/** First message per field — handy when the UI expects `Record<string, string>`. */
export function firstMessagePerField (errors: FieldErrors): Record<string, string> {
  const o: Record<string, string> = {}
  for (const [k, messages] of Object.entries(errors)) {
    const first = messages[0]
    if (first) {
      o[k] = first
    }
  }
  return o
}

const defaultOptions = {
  abortEarly: false,
  stripUnknown: true
} as const

/**
 * Validates a plain object with a Joi schema. Use in components before API calls.
 */
export function validateWith<T> (
  schema: ObjectSchema<T>,
  value: unknown
): ValidationResult<T> {
  const { error, value: parsed } = schema.validate(value, defaultOptions)

  if (error) {
    const errors = joiDetailsToFieldErrors(error)
    const message =
      error.details[0]?.message.replace(/"/g, '') ?? 'Validation failed.'
    return { ok: false, errors, message }
  }

  return { ok: true, value: parsed as T }
}
