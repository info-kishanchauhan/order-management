import Joi from 'joi'

/** POST /api/login */
export const loginSchema = Joi.object({
  email: Joi.string()
    .trim()
    .email({ tlds: { allow: false } })
    .required()
    .messages({
      'string.empty': 'Email is required.',
      'string.email': 'Enter a valid email address.'
    }),
  password: Joi.string().required().messages({
    'string.empty': 'Password is required.'
  })
})

export type LoginPayload = {
  email: string
  password: string
}

/** POST /api/register — mirrors Laravel rules loosely (confirmed password). */
export const registerSchema = Joi.object({
  name: Joi.string().trim().min(1).max(255).required().messages({
    'string.empty': 'Name is required.',
    'string.max': 'Name may not exceed 255 characters.'
  }),
  email: Joi.string()
    .trim()
    .email({ tlds: { allow: false } })
    .required()
    .messages({
      'string.empty': 'Email is required.',
      'string.email': 'Enter a valid email address.'
    }),
  password: Joi.string().min(8).required().messages({
    'string.empty': 'Password is required.',
    'string.min': 'Password must be at least 8 characters.'
  }),
  password_confirmation: Joi.string()
    .valid(Joi.ref('password'))
    .required()
    .messages({
      'any.only': 'Password confirmation does not match.',
      'string.empty': 'Confirm your password.'
    })
})

export type RegisterPayload = {
  name: string
  email: string
  password: string
  password_confirmation: string
}
