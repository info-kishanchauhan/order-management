import Joi from 'joi'

const orderFields = {
  customer_name: Joi.string().trim().min(2).max(100).required().messages({
    'string.empty': 'Customer name is required.',
    'string.min': 'Customer name must be at least 2 characters.',
    'string.max': 'Customer name may not exceed 100 characters.'
  }),
  item_name: Joi.string().trim().min(2).max(150).required().messages({
    'string.empty': 'Item name is required.',
    'string.min': 'Item name must be at least 2 characters.',
    'string.max': 'Item name may not exceed 150 characters.'
  }),
  price_cents: Joi.number().integer().min(0).required().messages({
    'number.base': 'Price must be a whole number (cents).',
    'number.integer': 'Price must be a whole number (cents).',
    'number.min': 'Price cannot be negative.'
  })
}

/** POST /api/orders — matches backend StoreOrderRequest ranges. */
export const createOrderSchema = Joi.object({
  customer_name: orderFields.customer_name,
  item_name: orderFields.item_name,
  price_cents: orderFields.price_cents
})

export type CreateOrderPayload = {
  customer_name: string
  item_name: string
  price_cents: number
}

/** PUT/PATCH /api/orders/:id — matches UpdateOrderRequest */
export const updateOrderSchema = Joi.object({
  ...orderFields,
  status: Joi.string().valid('pending', 'paid', 'cancelled').required().messages({
    'any.only': 'Pick a valid status.'
  })
})

export type UpdateOrderPayload = {
  customer_name: string
  item_name: string
  price_cents: number
  status: 'pending' | 'paid' | 'cancelled'
}
