# Order Management System

Production-minded slice: **Laravel 12** JSON API (orders + auth) and a **Nuxt** (Vue 3) SPA that consumes it — this repo uses **Nuxt 4.x** with the same Composition API / patterns as Nuxt 3. Orders are scoped per authenticated user (Sanctum bearer tokens).

## Prerequisites

- PHP 8.2+, Composer, **pdo_sqlite** (or MySQL/PostgreSQL) for the backend
- Node.js 20+ and npm for the frontend

## Backend (Laravel)

```bash
cd backend
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using sqlite (default in .env.example)
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

Default API base: `http://localhost:8000`. All HTTP routes are under **`/api`**.

### CORS (local dev)

Set `FRONTEND_URL` and/or `CORS_ALLOWED_ORIGINS` in `backend/.env` to match the Nuxt dev server (e.g. `http://localhost:3000`). `config/cors.php` reads these values.

### Tests

```bash
cd backend
composer test
# or
php artisan test
```

Requires SQLite in-memory (`phpunit.xml`) — ensure the PHP `pdo_sqlite` extension is enabled (`php -m | grep sqlite`).

**Common issue:** `could not find driver` — install/enabled `pdo_sqlite` (or switch `DB_*` in `phpunit.xml` to MySQL for CI).

## Frontend (Nuxt)

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

Open `http://localhost:3000`. Set `NUXT_PUBLIC_API_BASE` in `frontend/.env` to your Laravel URL (default `http://localhost:8000`).

The UI is **client-only** (`ssr: false`) so bearer tokens in `localStorage` stay consistent with middleware.

### Frontend tests (optional)

```bash
cd frontend
npm run test
```

## Key decisions & tradeoffs

- **409 Conflict** for invalid status transitions (documented in API and `InvalidOrderStatusTransitionException`), with a clear JSON body: `message` + `errors.status`.
- **API Resources** (`OrderResource`) for stable JSON; **repository** (`OrderRepository` extends `BaseRepository`) for queries; **service** (`OrderStatusTransitionService`) for transitions + **DB transactions** + **structured logging** on status changes.
- **Database**: indexes on `user_id` + `created_at` / `status` / `price_cents`; **CHECK** on `status` for MySQL/PostgreSQL (SQLite in dev relies on validation + app rules).
- **Frontend**: `useApi` composable + **runtime config** for base URL; **debounced** search (`watchDebounced`); **AbortController** on list requests; **lazy-loaded** create-order chunk (`defineAsyncComponent`); table **loading / error / empty** states; **Pinia** for auth and orders list state.

## Next steps if I had more time

- Policy classes + `authorize()` on orders; optional admin role to see all orders.
- Refresh tokens or shorter-lived Sanctum tokens + rotation.
- E2E tests (Playwright) for full flows.
- SSR with httpOnly cookies or same-site cookie strategy instead of `localStorage` tokens.
- OpenAPI/Swagger for the API contract.

## Review checklist mapping

| Requirement | Where |
|-------------|--------|
| Orders CRUD + filters/sort/pagination | `OrderController`, `OrderRepository`, `IndexOrderRequest` |
| Status transitions | `OrderStatusTransitionService`, 409 on invalid |
| Form requests + JSON errors | `app/Http/Requests/*`, `ApiExceptionHandler` |
| Base controller / repository | `app/Http/Controllers/Controller.php`, `app/Repositories/BaseRepository.php` |
| Auth | `AuthController`, Sanctum, `routes/api.php` |
| Nuxt table + create + status | `app/pages/orders/index.vue`, `CreateOrderForm.vue` |
| Lazy create form | async component in `orders/index.vue` |
| Debounce + abort | `watchDebounced`, `orders` store + `useApi` |
