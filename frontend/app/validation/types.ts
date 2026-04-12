/** Per-field messages (aligned with typical API 422 `errors` shape). */
export type FieldErrors = Record<string, string[]>

export type ValidationOk<T> = { ok: true; value: T }

export type ValidationFail = {
  ok: false
  errors: FieldErrors
  /** First error message (useful for banners). */
  message: string
}

export type ValidationResult<T> = ValidationOk<T> | ValidationFail
