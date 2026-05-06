# DeliverEats — System Architecture

## Overview

DeliverEats is a multi-restaurant food delivery platform built on **Laravel 12**, following clean architecture principles with domain services, a finite state machine, strategy pattern pricing, and an event-driven real-time layer.

---

## System Component Diagram

```mermaid
graph TD
    subgraph Client Layer
        CW[Customer Web Browser]
        RA[Rider Mobile App]
        AW[Admin Web Browser]
        RW[Restaurant Web Browser]
    end

    subgraph Laravel Application
        subgraph Routing
            WR[Web Routes<br/>role middleware]
            AR[API Routes<br/>Sanctum auth]
            CH[Channel Auth<br/>routes/channels.php]
        end

        subgraph Controllers
            AC[AuthController]
            OC[OrderController]
            RC[RiderController]
            MC[MenuController]
            ADC[AdminController]
            STC[StripeController]
            WC[WalletController]
            API_AC[Api/AuthApiController]
            API_OC[Api/OrderApiController]
            API_RC[Api/RiderApiController]
        end

        subgraph Domain
            FSM[OrderStateMachine<br/>FSM + Guards + Event Log]
            SPE[SurgePricingEngine<br/>Strategy Pattern]
            RDS[RiderDispatchService<br/>Haversine / Google Maps]
            PS[PaymentService<br/>Stripe Connect Splits]
            GMS[GoogleMapsService<br/>Distance Matrix API]
        end

        subgraph Jobs
            DRJ[DispatchRiderJob]
            PPJ[ProcessPaymentJob]
            RSJ[RecalculateSurgeJob]
            SNJ[SendNotificationJob]
        end

        subgraph Events
            OSU[OrderStatusUpdated]
            RLU[RiderLocationUpdated]
        end
    end

    subgraph Infrastructure
        DB[(MySQL Database)]
        RD[(Redis Queue)]
        PU((Pusher Broadcast))
        ST((Stripe API))
        GM((Google Maps API))
    end

    CW & RA & AW & RW --> WR & AR
    WR & AR --> Controllers
    Controllers --> Domain
    FSM --> OSU --> PU
    RC --> RLU --> PU
    Domain --> Jobs --> RD
    RD --> Domain
    PS --> ST
    GMS --> GM
    Controllers & Domain --> DB
    PU --> CW & AW & RW & RA
```

---

## Order Flow — End to End

```mermaid
sequenceDiagram
    participant C as Customer
    participant App as Laravel App
    participant FSM as State Machine
    participant Q as Queue Worker
    participant R as Rider App
    participant Pusher as Pusher

    C->>App: POST /restaurants/{id}/order
    App->>App: Calculate surge pricing
    App->>App: Create Order (status: placed)
    App->>FSM: transition(placed → placed via customer)
    FSM->>Pusher: OrderStatusUpdated [placed]
    FSM->>Q: SendNotificationJob
    App-->>C: Redirect to tracking page

    Note over App: Restaurant confirms via dashboard
    App->>FSM: transition(placed → confirmed, actor: restaurant)
    FSM->>Q: DispatchRiderJob
    FSM->>Q: RecalculateSurgeJob
    FSM->>Pusher: OrderStatusUpdated [confirmed]
    Pusher-->>C: Status: Confirmed ✓

    Q->>Q: Find nearest available rider (Haversine / Google Maps)
    Q->>App: Assign Rider #3 to Order #42

    Note over App: Kitchen starts cooking
    App->>FSM: transition(confirmed → preparing, actor: chef)
    FSM->>Pusher: OrderStatusUpdated [preparing]
    Pusher-->>C: Status: Preparing 🍳

    Note over App: Kitchen done
    App->>FSM: transition(preparing → ready_for_pickup, actor: chef)
    FSM->>Pusher: OrderStatusUpdated [ready_for_pickup]

    R->>App: POST /api/rider/order-status {on_the_way}
    App->>FSM: transition(ready_for_pickup → on_the_way, actor: rider)
    FSM->>Pusher: OrderStatusUpdated [on_the_way]
    Pusher-->>C: Status: On the Way 🏍️

    loop GPS updates
        R->>App: POST /api/rider/location {lat, lng}
        App->>Pusher: RiderLocationUpdated
        Pusher-->>C: Rider pin moves on map 📍
    end

    R->>App: POST /api/rider/order-status {delivered}
    App->>FSM: transition(on_the_way → delivered, actor: rider)
    FSM->>Q: ProcessPaymentJob
    FSM->>Pusher: OrderStatusUpdated [delivered]
    Pusher-->>C: Order Delivered! 🎉

    Q->>Q: Stripe Connect — transfer to restaurant + rider
    Q->>App: Create Payout record
```

---

## Database Schema

### Core Tables (21 total)

```
users                       id, name, email, password, role, phone, wallet_balance, restaurant_id
restaurants                 id, owner_id, name, address, lat, lng, cuisine_type, is_open, is_active, commission_rate, stripe_connect_id
categories                  id, restaurant_id, name, display_order, is_active
menu_items                  id, category_id, name, description, price, prep_time, is_available
item_variants               id, menu_item_id, name, price_modifier
orders                      id, customer_id, restaurant_id, rider_id, status, subtotal, delivery_fee, surge_fee, tip, total, delivery_address, payment_method, payment_status, delivered_at
order_items                 id, order_id, menu_item_id, variant_id, quantity, unit_price, subtotal
order_state_logs            id, order_id, from_state, to_state, actor_type, actor_id, metadata, transitioned_at
riders                      id, user_id, current_lat, current_lng, is_online, is_available, total_deliveries, total_earnings, stripe_connect_id
rider_dispatches            id, order_id, rider_id, distance_km, dispatched_at, accepted_at, rejected_at, rejection_reason
ratings                     id, rateable_type, rateable_id, user_id, score, created_at
reviews                     id, restaurant_id, user_id, order_id, content, response, created_at
surge_pricing_logs          id, restaurant_id, multiplier, strategy, reason, factors, triggered_at, expired_at, is_active
payouts                     id, order_id, order_total, restaurant_amount, rider_amount, platform_amount, platform_commission_pct, status, processed_at
notifications               id, user_id, title, body, type, data, read_at, created_at
transactions                id, user_id, type, amount, reference, description, created_at
feedback                    id, name, email, subject, message, status, created_at
personal_access_tokens      (Sanctum standard)
cache / jobs / sessions     (Laravel standard)
```

---

## Surge Pricing Engine

```mermaid
flowchart LR
    subgraph Strategies
        F[FlatSurgeStrategy<br/>Fixed fee if demand > threshold]
        M[MultiplierSurgeStrategy<br/>Dynamic rate: demand + weather + riders]
        T[TimeBasedSurgeStrategy<br/>Peak: lunch 12-3PM, dinner 7-10PM]
    end

    subgraph Engine
        GF[Gather Factors<br/>hour, demand, weather, riders]
        GF --> S{Active Strategy}
        S --> F & M & T
        F & M & T --> R[Raw Multiplier]
        R --> CAP{> 3.0x?}
        CAP -- Yes --> C[Cap at 3.0x]
        CAP -- No --> USE[Use raw value]
        C & USE --> LOG[Log to surge_pricing_logs]
    end
```

**Factors evaluated:**
- `demand` — active orders placed in the last 60 minutes
- `available_riders` — riders currently online and not busy
- `weather` — `clear | rain | storm | snow | extreme`
- `hour` — current hour (0–23), day of week

**Strategy selection** is done at runtime via `SurgePricingEngine::setStrategy()` or `getStrategyByName()`.

---

## Rider Dispatch Algorithm

```mermaid
flowchart TD
    A[Order Confirmed] --> B[DispatchRiderJob dispatched to queue]
    B --> C[Load all online + available riders<br/>excluding already-rejected for this order]
    C --> D{Any riders found?}
    D -- No --> E[Return null — no dispatch created]
    D -- Yes --> F[For each rider: calculate distance to restaurant]
    F --> G{Google Maps API key set?}
    G -- Yes --> H[Call Distance Matrix API<br/>get road distance in km]
    G -- No --> I[Haversine formula<br/>straight-line distance in km]
    H & I --> J[Sort riders by distance ascending]
    J --> K[Select nearest rider]
    K --> L[Create RiderDispatch record]
    L --> M[Update order.rider_id]
    M --> N[Mark rider is_available = false]
    N --> O[Rider receives dispatch notification]
    O --> P{Rider accepts?}
    P -- Yes --> Q[Update dispatch.accepted_at]
    P -- No --> R[Update dispatch.rejected_at<br/>Free rider — is_available = true<br/>Remove rider_id from order]
    R --> C
```

---

## Payment Split Architecture

```mermaid
flowchart LR
    OT[Order Total] --> REST[Restaurant Share<br/>subtotal − subtotal × commission%]
    OT --> RIDER[Rider Share<br/>delivery_fee + tip]
    OT --> PLAT[Platform Share<br/>commission + surge_fee]

    REST --> SC1[Stripe Transfer<br/>→ restaurant.stripe_connect_id]
    RIDER --> SC2[Stripe Transfer<br/>→ rider.stripe_connect_id]
    PLAT --> KEEP[Stays in platform account]

    SC1 & SC2 & KEEP --> PAY[Payout record created<br/>status: processed]
```

**Example split at 15% commission:**

| Amount | Formula | Result |
|---|---|---|
| Subtotal | (food items) | 80.00 |
| Commission (15%) | 80 × 0.15 | 12.00 |
| Delivery fee | fixed | 10.00 |
| Surge fee | dynamic | 5.00 |
| Tip | customer set | 5.00 |
| **Order total** | | **112.00** |
| → Restaurant | 80 − 12 | **68.00** |
| → Rider | 10 + 5 | **15.00** |
| → Platform | 12 + 5 | **17.00** |
| **Total allocated** | | **100.00** ✓ |

---

## Role & Middleware System

| Role | Access |
|---|---|
| `customer` | Browse, order, track, rate, wallet |
| `restaurant_owner` | Dashboard, menu, orders, revenue, staff |
| `chef` | Chef dashboard, order preparation status |
| `rider` | Rider panel, dispatch accept/reject, GPS, earnings |
| `admin` | All of the above + simulations, live map, platform revenue |

Route protection is handled by `RoleMiddleware` — e.g. `middleware('role:restaurant_owner,admin')`.

---

## Technology Stack

| Component | Technology | Version |
|---|---|---|
| Backend framework | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Authentication (web) | Laravel session | — |
| Authentication (API) | Laravel Sanctum | 4.x |
| Real-time broadcasting | Pusher + Laravel Echo | 7.x |
| Queue processing | Redis / Database | — |
| Payments | Stripe Connect | 20.x SDK |
| Distance calculation | Google Maps Distance Matrix / Haversine | — |
| Maps (frontend) | Leaflet.js + OpenStreetMap | — |
| Frontend components | Blade + Bootstrap 5 + Alpine.js | — |
| Charts | Chart.js | CDN |
| Icons | Font Awesome 6 | CDN |
| Testing | PHPUnit | 11.x |
| Package management | Composer + npm | — |
