# 🍔 DeliverEats — Multi-Restaurant Food Delivery Platform

> A premium food delivery ecosystem connecting **customers**, **restaurants**, and **delivery riders** — built with Laravel 12, real-time broadcasting, surge pricing, and automated rider dispatch.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Quick Start](#quick-start)
- [Environment Variables](#environment-variables)
- [Test Accounts](#test-accounts)
- [Running Tests](#running-tests)
- [Documentation](#documentation)
- [Project Structure](#project-structure)

---

## Overview

DeliverEats is a full-stack food delivery platform modeled after **Talabat** and **Uber Eats**, featuring:

- **Real-time order tracking** via Pusher broadcasting and Laravel Echo
- **Automated rider dispatch** using GPS + Haversine distance (Google Maps API ready)
- **Dynamic surge pricing** with three interchangeable strategy patterns
- **Finite state machine** for airtight order lifecycle management
- **Stripe Connect** split payments between platform, restaurants, and riders
- **Multi-role dashboards** for Admins, Restaurant Owners, Chefs, and Riders

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 12 (PHP 8.2) |
| **Auth (Web)** | Laravel session-based auth |
| **Auth (API)** | Laravel Sanctum (Bearer tokens) |
| **Real-Time** | Pusher / Laravel Echo |
| **Queues** | Redis / Database driver |
| **Payments** | Stripe Connect |
| **Maps** | Leaflet.js + OpenStreetMap (Google Maps API ready) |
| **Frontend** | Blade templates, Bootstrap 5, Alpine.js |
| **Charts** | Chart.js |
| **Testing** | PHPUnit (31 tests, 40 assertions) |

---

## Features

### 🏪 Restaurant & Menu Management
- Create and manage restaurants with address, coordinates, cuisine type, and open/close toggle
- Organize menus into **categories** (e.g. Burgers, Drinks, Sides)
- Add **menu items** with name, description, price, prep time, and availability toggle
- Add **item variants** (e.g. "Large +20 EGP", "Extra Cheese +10 EGP")
- Staff management — assign Chefs to restaurants

### 🛒 Customer Ordering
- Browse all open restaurants with search and filter
- Place orders with item selection, quantity control, delivery address, tip, and payment method
- Full wallet system with top-up via Stripe Checkout
- Rate restaurants and riders after delivery

### 📦 Order State Machine
- Strict lifecycle: `placed → confirmed → preparing → ready_for_pickup → on_the_way → delivered`
- Guards prevent invalid transitions (e.g. `delivered → preparing` throws immediately)
- Every state change is logged with actor type, actor ID, timestamp, and metadata (event sourcing)
- Terminal states: `delivered`, `cancelled`, `rejected`

### 🏍️ Rider Dispatch
- When an order is confirmed, `DispatchRiderJob` finds the **nearest available online rider**
- Distance calculated using **Google Maps Distance Matrix API** (falls back to Haversine formula)
- Rider can **accept** or **reject** dispatch; rejection re-dispatches to next nearest
- Real-time GPS tracking: riders update their location via API or web panel

### 💰 Surge Pricing Engine
- Three interchangeable strategies via **Strategy Pattern**:
  - **Flat**: fixed fee added when demand exceeds a threshold
  - **Multiplier**: dynamic rate based on demand, weather, and rider availability
  - **Time-Based**: peaks during lunch (12–3 PM) and dinner (7–10 PM)
- Maximum cap: **3.0x**
- Auto-rollback to 1.0x when demand drops
- All surge events logged to `surge_pricing_logs`

### 💳 Payment Splits (Stripe Connect)
- On delivery, `ProcessPaymentJob` executes Stripe Connect transfers:
  - **Restaurant**: food subtotal minus platform commission
  - **Rider**: delivery fee + 100% of customer tip
  - **Platform**: commission amount + surge fee
- Commission rate is configurable per restaurant (default 15%)
- All splits recorded in `payouts` table

### 🗺️ Admin Control Tower
- Real-time **live map** showing all riders and restaurants (Leaflet.js, auto-refresh every 5s)
- Live order feed with status filters
- Platform revenue dashboard with charts
- System simulation panel: volume spike testing, state machine validation, surge testing

### 📡 Real-Time Broadcasting
- `OrderStatusUpdated` event — fired on every FSM transition, broadcasts to:
  - `orders.{id}` (customer + rider + restaurant)
  - `restaurant.{id}` (restaurant owner)
  - `admin.orders` (admin control tower)
- `RiderLocationUpdated` event — fired on every GPS update, broadcasts to:
  - `admin.riders` (admin live map)
  - `orders.{id}` (customer tracking page, if rider has active order)

---

## Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL 8 (or SQLite for local dev)

### Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure your database in .env, then run:
php artisan migrate:fresh --seed

# 6. Build frontend assets
npm run build

# 7. Start all services (server + queue + logs + vite)
composer run dev
```

> **Note**: `composer run dev` starts the Laravel server, queue worker, Pail log viewer, and Vite in one command using `concurrently`.

### Access the App

```
http://127.0.0.1:8000
```

---

## Environment Variables

```env
# Application
APP_NAME=DeliverEats
APP_ENV=local
APP_KEY=                        # Generated by php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

# Database (MySQL recommended for production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delivereats
DB_USERNAME=root
DB_PASSWORD=

# Queue (use 'database' for local, 'redis' for production)
QUEUE_CONNECTION=database

# Broadcasting (Pusher for real-time features)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1

# Stripe (for payments and wallet top-up)
STRIPE_KEY=pk_test_...          # Publishable key
STRIPE_SECRET=sk_test_...       # Secret key
STRIPE_WEBHOOK_SECRET=whsec_... # For webhook signature verification

# Google Maps (Distance Matrix API — optional, Haversine used as fallback)
GOOGLE_MAPS_API_KEY=

# Redis (optional — used for queue and cache in production)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Test Accounts

After running `php artisan migrate:fresh --seed`, these accounts are available:

### Admin
| Email | Password |
|---|---|
| admin@delivereats.com | admin |

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

---

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific suites
php artisan test --filter=OrderStateMachine   # 9 FSM tests
php artisan test --filter=SurgePricing        # 10 pricing tests
php artisan test --filter=RiderDispatch       # 4 dispatch tests
php artisan test --filter=PaymentSplit        # 6 payment tests
```

**Test coverage**: 31 tests, 40 assertions across 4 feature test files.

---

## Documentation

Full documentation is in the `/docs` directory:

| File | Contents |
|---|---|
| [architecture.md](docs/architecture.md) | System architecture, Mermaid diagrams, component overview |
| [state-machine.md](docs/state-machine.md) | FSM states, transitions, actors, and guard rules |
| [realtime-architecture.md](docs/realtime-architecture.md) | Pusher channels, Redis queues, real-time sequence flows |
| [api-docs.md](docs/api-docs.md) | Full Sanctum API reference with request/response examples |
| [user-guides.md](docs/user-guides.md) | Step-by-step guides for Customer, Restaurant, Rider, Admin |
| [setup.md](docs/setup.md) | Detailed setup, routes reference, database schema |

---

## Project Structure

```
DeliverEats/
├── app/
│   ├── Events/
│   │   ├── OrderStatusUpdated.php      # Broadcasts on FSM transition
│   │   └── RiderLocationUpdated.php    # Broadcasts on GPS update
│   ├── Http/Controllers/
│   │   ├── Api/                        # Sanctum API controllers (Auth, Order, Rider)
│   │   ├── AdminController.php
│   │   ├── AdminSimulationController.php
│   │   ├── AuthController.php
│   │   ├── ChefController.php
│   │   ├── DashboardController.php
│   │   ├── FeedbackController.php
│   │   ├── MenuController.php
│   │   ├── OrderController.php
│   │   ├── RatingController.php
│   │   ├── RestaurantController.php
│   │   ├── RiderController.php
│   │   ├── StaffController.php
│   │   ├── StripeController.php
│   │   └── WalletController.php
│   ├── Http/Middleware/
│   │   └── RoleMiddleware.php          # Role-based access control
│   ├── Jobs/
│   │   ├── DispatchRiderJob.php        # Queued: finds nearest rider
│   │   ├── ProcessPaymentJob.php       # Queued: executes Stripe Connect
│   │   ├── RecalculateSurgeJob.php     # Queued: recalculates surge pricing
│   │   └── SendNotificationJob.php     # Queued: sends in-app notifications
│   ├── Models/                         # 15 Eloquent models
│   ├── Pricing/
│   │   ├── Contracts/SurgeStrategyInterface.php
│   │   ├── Strategies/
│   │   │   ├── FlatSurgeStrategy.php
│   │   │   ├── MultiplierSurgeStrategy.php
│   │   │   └── TimeBasedSurgeStrategy.php
│   │   └── SurgePricingEngine.php
│   ├── Services/
│   │   ├── GoogleMapsService.php       # Distance Matrix API wrapper
│   │   ├── PaymentService.php          # Stripe Connect splits
│   │   └── RiderDispatchService.php    # Nearest-rider algorithm
│   └── StateMachine/
│       └── OrderStateMachine.php       # FSM with guards and event sourcing
├── database/
│   ├── migrations/                     # 21 migration files
│   └── seeders/DatabaseSeeder.php
├── resources/views/                    # 30+ Blade templates
├── routes/
│   ├── api.php                         # Sanctum-protected API routes
│   ├── channels.php                    # Pusher channel authorization
│   └── web.php                         # Role-middleware web routes
├── tests/Feature/                      # 4 test files, 31 tests
└── docs/                               # Full documentation suite
```
