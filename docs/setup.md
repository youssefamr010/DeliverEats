# DeliverEats — Setup & Reference Guide

---

## Quick Start

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# 3. Configure your database in .env (see below)

# 4. Run migrations and seed sample data
php artisan migrate:fresh --seed

# 5. Install JS dependencies and build assets
npm install && npm run build

# 6. Start all services in one command
composer run dev
```

> `composer run dev` launches four processes simultaneously:
> - `php artisan serve` — Laravel development server
> - `php artisan queue:listen --tries=1 --timeout=0` — Queue worker
> - `php artisan pail --timeout=0` — Real-time log viewer
> - `npm run dev` — Vite asset bundler with HMR

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Environment Configuration

### Database (choose one)

**SQLite (simplest for local dev):**
```env
DB_CONNECTION=sqlite
# No other DB_ variables needed — uses database/database.sqlite
```

**MySQL (recommended):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delivereats
DB_USERNAME=root
DB_PASSWORD=
```

---

### Queue Driver

```env
# Local — uses database, no Redis needed
QUEUE_CONNECTION=database

# Production — faster, supports Horizon
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Start the queue worker:
```bash
php artisan queue:work
```

---

### Pusher (Real-Time Broadcasting)

Create a free account at [pusher.com](https://pusher.com), create an app, then:

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

> Without Pusher credentials, order tracking and admin live map fall back to polling every 5–10 seconds automatically.

---

### Stripe (Payments)

Create an account at [stripe.com](https://stripe.com) and get your test keys:

```env
STRIPE_KEY=pk_test_...          # Publishable key (frontend)
STRIPE_SECRET=sk_test_...       # Secret key (backend)
STRIPE_WEBHOOK_SECRET=whsec_... # For webhook signature validation
```

**For Stripe Connect (restaurant/rider payouts):**
- Each restaurant and rider needs their own connected Stripe account
- Store their `stripe_connect_id` in the respective model

> Without Stripe credentials, the system records payment splits in the database but skips the actual Stripe API transfer. All other functionality works normally.

---

### Google Maps (Optional)

```env
GOOGLE_MAPS_API_KEY=your_key_here
```

Enable the **Distance Matrix API** in your Google Cloud Console. If this key is blank, the dispatch service automatically falls back to the Haversine formula — which is accurate enough for typical delivery distances.

---

## Test Accounts (after seeding)

### Admin
| Email | Password | Access |
|---|---|---|
| admin@delivereats.com | admin | Full platform access |

### Restaurant Owners
| Restaurant | Email | Password |
|---|---|---|
| Burger Palace | chef@burgerpalace.com | password |
| Pizza Roma | maria@pizzaroma.com | password |
| Dragon Wok | li@dragonwok.com | password |
| Sushi Zen | yuki@sushizen.com | password |
| Falafel King | omar@falafelking.com | password |
| Spice Garden | priya@spicegarden.com | password |

### Riders
| Name | Email | Password |
|---|---|---|
| Rider Ahmed | rider1@delivereats.com | password |
| Rider Sara | rider2@delivereats.com | password |
| Rider Mike | rider3@delivereats.com | password |
| Rider Fatima | rider4@delivereats.com | password |
| Rider Carlos | rider5@delivereats.com | password |

### Customers
| Name | Email | Password |
|---|---|---|
| John Smith | john@customer.com | password |
| Emma Wilson | emma@customer.com | password |
| Liam Johnson | liam@customer.com | password |
| Sophia Brown | sophia@customer.com | password |
| Noah Davis | noah@customer.com | password |
| Olivia Martinez | olivia@customer.com | password |

---

## Running Tests

```bash
# Run all 31 tests
php artisan test

# Run by feature area
php artisan test --filter=OrderStateMachine     # 9 tests — FSM transitions and guards
php artisan test --filter=SurgePricing          # 10 tests — pricing strategies and engine
php artisan test --filter=RiderDispatch         # 4 tests — nearest-rider algorithm
php artisan test --filter=PaymentSplit          # 6 tests — commission and split math
```

**Test database:** Tests use `RefreshDatabase` with an in-memory SQLite database — no separate test DB setup needed.

---

## Route Reference

### Public Routes
| Method | URL | Description |
|---|---|---|
| GET | `/` | Landing page |
| GET | `/restaurants` | Browse all open restaurants |
| GET | `/restaurants/{id}` | Restaurant menu page |
| GET | `/restaurants/{id}/reviews` | Restaurant reviews |
| GET | `/login` | Login page |
| GET | `/register` | Registration page |
| GET | `/help` | Help / FAQ page |
| GET | `/privacy-policy` | Privacy policy |
| GET | `/terms-of-service` | Terms of service |
| GET | `/feedback` | Public feedback form |

### Customer Routes (auth required)
| Method | URL | Description |
|---|---|---|
| GET | `/restaurants/{id}/order` | Start ordering from a restaurant |
| POST | `/restaurants/{id}/order` | Submit the order |
| GET | `/orders/{id}/track` | Real-time order tracking |
| GET | `/orders/history` | Past and active orders |
| GET | `/orders/{id}/status` | JSON status endpoint (polling) |
| POST | `/orders/{order}/rate` | Submit rating and review |
| GET | `/wallet` | Wallet balance |
| POST | `/wallet/topup` | Top up wallet via Stripe |

### Restaurant Owner Routes (`role:restaurant_owner,admin`)
| Method | URL | Description |
|---|---|---|
| GET | `/my-restaurant/dashboard` | Orders and stats |
| GET | `/my-restaurant/create` | Create restaurant form |
| POST | `/my-restaurant/store` | Save new restaurant |
| GET | `/my-restaurant/{id}/edit` | Edit restaurant |
| PUT | `/my-restaurant/{id}` | Update restaurant |
| POST | `/my-restaurant/{id}/toggle-open` | Open/close restaurant |
| GET | `/my-restaurant/{id}/categories` | Manage menu categories |
| POST | `/my-restaurant/{id}/categories` | Add category |
| GET | `/my-restaurant/categories/{id}/items` | Manage items in category |
| POST | `/my-restaurant/categories/{id}/items` | Add item |
| PUT | `/my-restaurant/items/{id}` | Update item |
| POST | `/my-restaurant/items/{id}/toggle` | Toggle availability |
| POST | `/my-restaurant/items/{id}/variants` | Add variant |
| GET | `/my-restaurant/revenue` | Revenue dashboard |
| GET | `/my-restaurant/staff` | Staff management |

### Rider Routes (`role:rider,admin`)
| Method | URL | Description |
|---|---|---|
| GET | `/rider/dashboard` | Rider panel |
| POST | `/rider/toggle-online` | Go online/offline |
| POST | `/rider/update-location` | Update GPS (web) |
| POST | `/rider/dispatch/{id}/accept` | Accept dispatch |
| POST | `/rider/dispatch/{id}/reject` | Reject dispatch |
| GET | `/rider/earnings` | Earnings dashboard |

### Admin Routes (`role:admin`)
| Method | URL | Description |
|---|---|---|
| GET | `/admin/dashboard` | Control tower |
| GET | `/admin/orders` | All orders with filters |
| GET | `/admin/revenue` | Platform revenue |
| GET | `/admin/live-map` | Live rider and restaurant map |
| GET | `/admin/live-data` | JSON endpoint for live map data |
| GET | `/admin/payments` | All payout records |
| GET | `/admin/feedbacks` | Customer feedbacks |
| GET | `/admin/reviews` | All reviews |
| GET | `/admin/simulations` | System simulation panel |
| POST | `/admin/simulations/spike` | Trigger 50-order volume spike |
| POST | `/admin/simulations/state` | Test FSM guard rules |
| POST | `/admin/simulations/surge` | Test surge pricing |
| POST | `/admin/simulations/payment` | Test payment splits |
| POST | `/admin/simulations/cleanup` | Remove simulation data |

### API Routes (Sanctum — Bearer token)
| Method | URL | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | None | Register customer or rider |
| POST | `/api/login` | None | Login and get token |
| POST | `/api/logout` | Required | Revoke current token |
| GET | `/api/user` | Required | Current user info |
| POST | `/api/orders` | Required | Place order |
| GET | `/api/orders/{id}` | Required | Order details |
| GET | `/api/orders/{id}/track` | Required | Track order with rider GPS |
| GET | `/api/orders/history` | Required | Customer order history |
| POST | `/api/rider/location` | Required | Update rider GPS |
| POST | `/api/rider/toggle-online` | Required | Go online/offline |
| POST | `/api/rider/dispatch/{id}/accept` | Required | Accept dispatch |
| POST | `/api/rider/dispatch/{id}/reject` | Required | Reject dispatch |
| POST | `/api/rider/order-status` | Required | Update order status |
| GET | `/api/restaurants` | Required | List all restaurants |
| GET | `/api/restaurants/{id}` | Required | Restaurant with full menu |

---

## Database Tables (21 tables)

| Table | Purpose | Seeded Records |
|---|---|---|
| `users` | All users with role field | 18 |
| `restaurants` | Restaurant profiles with GPS | 6 |
| `categories` | Menu categories per restaurant | ~15 |
| `menu_items` | Individual food items | ~40 |
| `item_variants` | Size/add-on variants | ~15 |
| `orders` | Orders with FSM status | 30 |
| `order_items` | Line items per order | ~70 |
| `order_state_logs` | Event-sourced state change audit trail | ~30 |
| `riders` | Rider profiles with GPS, availability | 5 |
| `rider_dispatches` | Rider-to-order assignment records | varies |
| `ratings` | Polymorphic ratings (restaurant & rider) | ~15 |
| `reviews` | Text reviews with restaurant responses | ~15 |
| `surge_pricing_logs` | Surge multiplier history | varies |
| `payouts` | Split payment records per order | ~15 |
| `notifications` | In-app notifications per user | 0 |
| `transactions` | Wallet top-up and spend records | 0 |
| `feedback` | Public feedback form submissions | 0 |
| `personal_access_tokens` | Sanctum API tokens | 0 |
| `cache` / `cache_locks` | Application cache | 0 |
| `jobs` / `failed_jobs` | Queue tables | 0 |
| `sessions` | Session storage | varies |

---

## Common Artisan Commands

```bash
# Full reset and reseed
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# Start queue worker
php artisan queue:work

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Run tests
php artisan test

# Tinker (interactive REPL)
php artisan tinker
```
