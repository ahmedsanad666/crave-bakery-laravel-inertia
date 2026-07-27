# Stripe Go-Live Checklist — Crave Bakery

Guide for taking this project from **Stripe Test Mode** to **real customer payments**, and for planning follow-up product work.

---

## 1. Current status (already implemented)

The app is ready for **test-mode** end-to-end payments. Do not treat this as live until Section 2 is complete.

### What works today

| Area | Status |
|------|--------|
| Laravel Cashier (`laravel/cashier`) | Installed; `User` uses `Billable` |
| Payment methods at checkout | **Cash on Delivery** + **Pay with Stripe** |
| Stripe Payment Element (Vue) | Card form via `@stripe/stripe-js` |
| Pay-then-fulfill | Cart, stock, and promo usage change **only after** Stripe payment succeeds |
| COD flow | Order created immediately; payment stays pending until delivered (then marked paid) |
| Order confirmation email | Sent after COD place, or after successful Stripe fulfill |
| Admin refunds | Stripe refunds via Cashier `$user->refund(payment_intent_id)` when method is `stripe` |
| Webhooks | Cashier route `POST /stripe/webhook` + `StripeEventListener` for `payment_intent.succeeded` / `payment_intent.payment_failed` |
| CSRF | `stripe/*` excluded so Stripe can POST webhooks |

### Environment variables (see `.env.example`)

```env
STRIPE_KEY=pk_test_...          # Publishable (also used as VITE_STRIPE_KEY / cashier.key)
STRIPE_SECRET=sk_test_...       # Secret — server only
STRIPE_WEBHOOK_SECRET=whsec_... # From Stripe CLI (local) or Dashboard (production)
CASHIER_CURRENCY=usd
VITE_STRIPE_KEY="${STRIPE_KEY}"
```

Aliases `STRIPE_PUBLISHABLE_KEY` / `STRIPE_SECRET_KEY` are supported for the publishable key fallback in `config/services.php`.

### Checkout flow (Stripe)

1. Customer submits checkout with `payment_method=stripe`.
2. Validated form is stored in session/cache as **pending checkout** — cart and stock are **not** changed.
3. Customer pays on `/checkout/payment`.
4. On success (confirm and/or webhook), order is created, stock decremented, cart cleared, order marked `paid` / `processing`.

If the customer abandons payment or the card declines, the cart remains intact.

---

## 2. Go-live checklist (per client / production deploy)

Complete these in order before accepting real money.

### 2.1 Stripe account activation

- [ ] Create or use the **client’s** Stripe account (not your personal learning account, unless they own it).
- [ ] Complete Stripe onboarding: business details, identity verification, bank account for payouts.
- [ ] Confirm the account can leave Test Mode and accept Live charges in their country.
- [ ] Agree payout schedule and statement descriptor (what customers see on bank statements).

### 2.2 Live API keys

- [ ] In Stripe Dashboard, toggle **Live** mode (not Test).
- [ ] Developers → API keys → copy:
  - Publishable: `pk_live_...`
  - Secret: `sk_live_...`
- [ ] Put them on the **production** server `.env` only:

```env
APP_URL=https://their-domain.com
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
VITE_STRIPE_KEY=pk_live_...
CASHIER_CURRENCY=usd
```

- [ ] Rebuild frontend assets after changing `VITE_*` (`npm run build`) so the browser never keeps an old `pk_test_` bundle.
- [ ] Confirm Inertia payment page receives live publishable key (`config('cashier.key')`).
- [ ] **Never** commit live secrets to git. If a live secret was pasted into chat or a repo, **rotate it** in the Dashboard immediately.

### 2.3 HTTPS and hosting

- [ ] Site must be served over **HTTPS** with a valid certificate.
- [ ] `APP_URL` must match the public URL Stripe and users use (no `http://localhost` in production).
- [ ] Session/cookie settings work behind the reverse proxy (TrustedProxies / HTTPS) so checkout sessions survive payment.

### 2.4 Production webhooks (required)

Without a live webhook, orders can stay unpaid if the user closes the browser after paying but before `payment.confirm` finishes. Cache + webhook fulfill covers that case **only if** the webhook is configured.

- [ ] Stripe Dashboard (Live) → Developers → Webhooks → Add endpoint:
  - URL: `https://{their-domain}/stripe/webhook`
- [ ] Subscribe at least to:
  - `payment_intent.succeeded`
  - `payment_intent.payment_failed`
  - Plus Cashier defaults if you use more Cashier features later, e.g.:
    - `customer.updated` / `customer.deleted`
    - `customer.subscription.*` (only if you add subscriptions)
    - `invoice.payment_succeeded` / `invoice.payment_failed` / `invoice.payment_action_required`
- [ ] Copy the endpoint **Signing secret** (`whsec_...`) into production:

```env
STRIPE_WEBHOOK_SECRET=whsec_...
```

- [ ] Optional helper from the server (with Live secret already in `.env`):

```bash
php artisan cashier:webhook --url="https://their-domain.com/stripe/webhook"
```

- [ ] In Dashboard → Webhooks → select endpoint → confirm recent deliveries return **2xx**.

### 2.5 Production smoke test

- [ ] Place a **small** real Live charge with a real card.
- [ ] Confirm: order created, `payment_status=paid`, stock reduced, cart cleared, confirmation email sent.
- [ ] Admin → refund that order; confirm money returns in Stripe and order shows `refunded`.
- [ ] Force a declined path in Test Mode before go-live to confirm cart is preserved on failure.
- [ ] Monitor first week of Live webhook logs for signature failures (`STRIPE_WEBHOOK_SECRET` mismatch is the usual cause).

### 2.6 Handoff notes for selling the product

Tell the client clearly:

> Checkout code is done. Going live means activating **their** Stripe account, putting **Live** keys and webhook secret on the server, HTTPS, and one real charge/refund test. Pasting Test keys will never take real money.

---

## 3. Currency and region

### Current behavior

- Default currency is **USD** via `CASHIER_CURRENCY=usd`.
- Amounts sent to Stripe are in the **smallest currency unit** (cents for USD).

### If the client needs TRY, EUR, etc.

1. Set `CASHIER_CURRENCY` to the correct ISO code (e.g. `try`, `eur`) — lowercase as Cashier expects.
2. Confirm Stripe account supports that currency for charges and payouts.
3. Re-test PaymentIntent amounts (zero-decimal currencies like JPY differ — USD/EUR/TRY use fractional units).
4. Update storefront display formatting if you hardcode `$` in Vue (search for `USD`, `$`, `toFixed(2)` in checkout/admin).
5. Tax rules may differ by country; this app currently uses a simple computed tax in `OrderService`, not Stripe Tax.

Until those changes are made and tested, keep Live charges in **USD** only.

---

## 4. Future implementation work (code backlog)

These are **not** required for a basic Live launch, but improve reliability, ops, and product quality.

### Payments and checkout

| Item | Why | Complexity |
|------|-----|------------|
| Job to cancel/expire abandoned PaymentIntents | Reduces clutter and stale Stripe objects after users leave `/checkout/payment` | Medium |
| “Payment received” recovery page | If webhook fulfills the order but the browser never hits confirmation, user needs a clear path to find the order | Medium |
| Idempotency keys on PaymentIntent create | Extra safety under double-clicks / retries | Easy |
| Partial refunds in admin | Today refunds are full PaymentIntent refunds | Medium |
| Manual “Mark COD as paid” (before deliver) | Ops may collect cash earlier than delivery | Easy |
| Stripe Tax / dynamic tax by address | Replace flat tax if client needs compliance | Hard |
| Multi-currency checkout | Price catalogs + Cashier currency per order | Hard |
| Saved cards / Customer Portal | Nice-to-have; not required for one-off bakery orders | Medium |
| 3DS / SCA UX polish | Already supported via Payment Element; improve messaging and return URLs | Easy–Medium |

### Reliability and observability

| Item | Why | Complexity |
|------|-----|------------|
| Structured logging / Sentry for Stripe + webhook errors | Faster diagnosis in production | Medium |
| Admin alert when fulfill fails after succeeded PI | Prevents “charged but no order” edge cases | Medium |
| Queue webhook heavy work | Keep webhook responses fast under load | Easy–Medium |
| Automated tests for pay-then-fulfill and COD | Protect regressions | Medium |

### Admin and compliance

| Item | Why | Complexity |
|------|-----|------------|
| Audit log for refunds | Who refunded what and when | Medium |
| Checkout legal links (Terms, Refund Policy, Privacy) | Required for many Live businesses | Easy (content) |
| Restrict refund permission reviews | Already permission-gated; document for client admins | Easy |
| Invoice PDF: show Stripe receipt ID / PI id | Support and accounting | Easy |

### Explicitly out of scope unless requested

- Full **subscription** billing (Cashier can do it; bakery checkout does not need it today).
- Replacing Cashier again with raw `stripe/stripe-php` only.
- Marketplace / Connect (multi-vendor payouts).

---

## 5. Local vs production testing cheat sheet

### Test Mode (development)

```bash
# Forward webhooks to local app (Valet/Herd example)
stripe listen --forward-to http://crave-bakery.test/stripe/webhook
```

Copy the CLI `whsec_...` into local `.env` as `STRIPE_WEBHOOK_SECRET`.

| Result | Test card |
|--------|-----------|
| Success | `4242 4242 4242 4242` |
| Decline | `4000 0000 0000 0002` |
| Insufficient funds | `4000 0000 0000 9995` |
| Requires 3D Secure | `4000 0025 0000 3155` |

Use any future expiry, any CVC, any postal code.

### Live Mode (production)

- Use **real cards** only.
- Test cards like `4242…` will **not** work with `pk_live_` / `sk_live_`.
- Prefer a small charge + immediate refund for the first Live verification.

### Key prefix reminder

| Prefix | Meaning |
|--------|---------|
| `pk_test_` / `sk_test_` | Fake money |
| `pk_live_` / `sk_live_` | Real money |
| `whsec_` | Webhook signing secret (different for CLI vs Dashboard endpoint) |

---

## 6. Key files in this project

| File | Role |
|------|------|
| `app/Services/StripePaymentService.php` | Pending checkout session/cache, PaymentIntent create, fulfill, refund helpers |
| `app/Services/OrderService.php` | Quotes, `createFromCart`, `createPaidStripeOrderFromCart`, COD paid-on-deliver |
| `app/Http/Controllers/OrderController.php` | Checkout; Stripe branches to pending payment; COD places order |
| `app/Http/Controllers/PaymentController.php` | `payment.intent`, `payment.confirm` |
| `app/Listeners/StripeEventListener.php` | Cashier `WebhookReceived` → fulfill / failed handlers |
| `app/Models/User.php` | `Billable` trait (Cashier customer) |
| `resources/js/Pages/Checkout/Index.vue` | COD vs Stripe method selection |
| `resources/js/Pages/Checkout/Payment.vue` | Payment step UI (no order until paid) |
| `resources/js/Components/Public/StripePayment.vue` | Stripe.js Payment Element |
| `bootstrap/app.php` | CSRF except `stripe/*` |
| `.env.example` | Documented Stripe / Cashier variables |

Cashier registers `POST /stripe/webhook` automatically (route name `cashier.webhook`).

---

## 7. Minimal “ready to sell” definition

You can hand the project to a client as **Live-capable** when:

1. Their Stripe account is activated for Live charges.  
2. Production `.env` has Live keys + webhook secret.  
3. HTTPS + webhook endpoint verified in Dashboard.  
4. One real charge and one refund succeeded.  
5. COD still works as a fallback if they want it.

Until then, keep the orange **Test Mode** banner mindset: safe to demo, not for real customers’ cards.
