# Payments Endpoint

Base URL `/api/v1`

Most payment routes require `AuthMiddleware` (requires `auth_token` cookie). The **webhook** route is public — it is authenticated by Stripe's signature instead.

Requires `STRIPE_SECRET_KEY` (and `STRIPE_WEBHOOK_SECRET` for the webhook) to be set in the environment (`.env`), plus `STRIPE_MODE` = `sandbox` (test) or `live`.

> **Sandbox note (Sri Lanka):** Stripe isn't available in Sri Lanka, so Connect can't be activated on a local business. For development/testing, use a **US-based Stripe account's sandbox (test) keys** (`sk_test_...`) and set `STRIPE_MODE=sandbox`. `STRIPE_PUBLISHABLE_KEY` should match (starts with `pk_test_`). Set up a test webhook with `stripe listen --forward-to <url>/api/v1/payment/webhook` and copy the `whsec_...` signing secret into `STRIPE_WEBHOOK_SECRET`.

---

## Connect Account (Onboarding)

`POST /payment/connect`

Creates a Stripe Connect account for the authenticated user and returns an Account Link URL so they can complete Stripe onboarding (verification and payout setup). The link's return/refresh URLs are built from the `FRONTEND_HOST` env var (falls back to `DOMAIN`). Use `FRONTEND_HOST` including scheme, e.g. `http://localhost:3000`.

### Request Body
```json
{
    "email": "organizer@example.com"
}
```

| Field   | Type   | Required | Description                          |
|---------|--------|----------|--------------------------------------|
| `email` | string | yes      | Email address of the account holder  |

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Connect account created successfully",
    "data": {
        "accountId": "acct_1Ab2C3d4E5f6",
        "onboardingUrl": "https://connect.stripe.com/setup/s/acct_1Ab2C3d4E5f6",
        "expiresAt": 1756281600
    }
}
```

Client should redirect the user to `data.onboardingUrl`. The link expires (`expiresAt`) after a short time, so generate it fresh when needed.

### Response Body `400 ERROR` (missing email)
```json
{
    "success": false,
    "message": "email is required",
    "data": null
}
```

### Response Body `401 ERROR` (missing auth)
```json
{
    "success": false,
    "message": "User not authenticated",
    "data": null
}
```

### Response Body `500 ERROR` (Stripe failure)
```json
{
    "success": false,
    "message": "Error creating connect account: <reason>",
    "data": null
}
```

---

## Create Checkout Session

`POST /payment/checkout-session`

Creates a Stripe Checkout Session for an event ticket, returning the hosted checkout URL. The amount is derived from the event's `ticketPrice`. Requires `AuthMiddleware`.

### Request Body
```json
{
    "eventId": 1,
    "quantity": 2,
    "currency": "lkr",
    "registerId": 12,
    "email": "user@example.com"
}
```

| Field        | Type   | Required | Description                                           |
|--------------|--------|----------|-------------------------------------------------------|
| `eventId`    | number | yes      | ID of the event to purchase a ticket for              |
| `quantity`   | number | no       | Number of tickets (default `1`)                       |
| `currency`   | string | no       | ISO currency code (default `lkr`)                     |
| `registerId` | number | no       | Registration id attached to the payment on completion |
| `email`      | string | no       | Customer email pre-filled in the Stripe session       |

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Checkout session created successfully",
    "data": {
        "sessionId": "cs_test_a1b2c3d4",
        "url": "https://checkout.stripe.com/c/pay/cs_test_a1b2c3d4",
        "amount": 2500.00,
        "currency": "lkr"
    }
}
```

Client should redirect the user to `data.url`.

### Response Body `400 ERROR` (free event)
```json
{
    "success": false,
    "message": "This event is free. No payment required.",
    "data": null
}
```

### Response Body `404 ERROR` (event not found)
```json
{
    "success": false,
    "message": "Event not found",
    "data": null
}
```

### Response Body `500 ERROR` (Stripe failure)
```json
{
    "success": false,
    "message": "Error creating checkout session: <reason>",
    "data": null
}
```

---

## Payment Records

`GET /payment/records`

Returns the authenticated user's payment records grouped per event. Requires `AuthMiddleware`.

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Payment records retrieved",
    "data": {
        "sales": [
            {
                "event": {
                    "id": 1,
                    "title": "Tech Conference 2026",
                    "startDate": "2026-05-10 09:00:00",
                    "coverImage": "cover.jpg"
                },
                "paymentCount": 2,
                "revenue": 5000.00,
                "commission": 250.00,
                "payout": 4750.00,
                "payments": [
                    {
                        "id": 7,
                        "amount": 2500.00,
                        "paymentAt": "2026-05-01 12:34:56",
                        "buyer": {
                            "firstName": "Amaya",
                            "lastName": "Perera",
                            "email": "amaya@example.com"
                        }
                    }
                ]
            }
        ],
        "purchases": []
    }
}
```

| Field         | Description                                                        |
|---------------|--------------------------------------------------------------------|
| `sales`       | Payments collected by the user's events, grouped per event. Each group contains the event summary, `paymentCount`, `revenue`, platform `commission` (5%), `payout`, and the per-payment records with buyer details. |
| `purchases`   | Payments the user made for other organizers' events, grouped per event. |

### Response Body `401 ERROR` (missing auth)
```json
{
    "success": false,
    "message": "User not authenticated",
    "data": null
}
```

---

## Confirm Payment

`POST /payment/confirm`

Records a paid Checkout Session by id (idempotent). Use this as a fallback from the client-facing success page so a payment is saved even if the webhook hasn't been received yet. Requires `AuthMiddleware`; the session must belong to the authenticated user.

### Request Body
```json
{
    "session_id": "cs_test_a1b2c3d4"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Payment recorded",
    "data": null
}
```

If the session is not yet `paid`, or it belongs to a different user, a `200` response with `success: false` and an explanatory `message` is returned instead.

---

## Webhook (Payment Events)

`POST /payment/webhook`

Public Stripe webhook endpoint (no `AuthMiddleware`). Listens for Stripe payment events and verifies the request via the `STRIPE_WEBHOOK_SECRET` signature.

### Supported Events

| Event                        | Action                                                              |
|------------------------------|---------------------------------------------------------------------|
| `checkout.session.completed` | If `payment_status == "paid"`, records a `Payment` row using `userId` and `registerId` from the session metadata (idempotent) |

### Headers
- `Stripe-Signature`: signature sent by Stripe (verified against `STRIPE_WEBHOOK_SECRET`)

### Response Body `200 OK`
```json
{
    "success": true
}
```

### Response Body `400 ERROR` (invalid payload or signature)
```json
{
    "error": "Invalid payload"
}
```

```json
{
    "error": "Invalid signature"
}
```

### Response Body `500 ERROR` (missing secret)
```json
{
    "error": "Stripe webhook secret is not configured"
}
```
