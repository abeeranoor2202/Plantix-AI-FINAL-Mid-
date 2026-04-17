# API v1 Specification

Base URL: /api/v1
Authentication: Bearer token (Laravel Sanctum)
Envelope (all responses):
- success: boolean
- message: string
- data: object|array|null
- errors: object|array|null

## Auth
- POST /api/v1/auth/login
  - body: email, password, device_name?
  - 200: token + user
- GET /api/v1/auth/me
  - auth required
- POST /api/v1/auth/logout
  - auth required

## Forum
- GET /api/v1/forum/threads
  - query: search, category, status, sort_by(latest|oldest|popular), date_from, date_to, page, limit
- GET /api/v1/forum/threads/{thread}

## Orders
- GET /api/v1/orders
  - query: search, status, dispute_status, min_total, max_total, date_from, date_to, page, limit
  - scoped by role: user sees own, vendor sees own store, admin sees all
- GET /api/v1/orders/{id}
  - scoped by role

## Appointments
- GET /api/v1/appointments
  - query: search, status, type, date_from, date_to, page, limit
  - scoped by role: user/expert/admin
- GET /api/v1/appointments/{id}
  - scoped by role

## Notifications
- GET /api/v1/notifications
  - query: type, status(all|unread|read), page, limit
- GET /api/v1/notifications/unread-count
- PATCH /api/v1/notifications/{id}/read
- PATCH /api/v1/notifications/read-all

## Dashboards
- GET /api/v1/dashboards/summary
  - returns role-specific aggregate payload

## Activity (Admin only)
- GET /api/v1/activity/logs
  - query: action, entity_type, actor_role, date_from, date_to, page, limit

## Security and policy
- /api/v1/auth/login: throttle api-v1-auth
- all protected endpoints: auth:sanctum + throttle:api-v1
- role-restricted endpoints: api.role middleware
- error status codes: 200, 201, 400, 401, 403, 404, 422, 429, 500
