# DeliverEats — Real-Time Architecture

## Overview

DeliverEats uses a **hybrid real-time approach** combining **Pusher broadcasting** (for instant push to clients) and **Redis/Database queues** (for background job processing). Together they enable live order tracking, rider GPS updates, and asynchronous surge recalculation — all without blocking web requests.

---

## 1. Broadcasting (Pusher / Laravel Echo)

### How It Works

1. An event occurs in the application (order status changes, rider moves)
2. Laravel dispatches a broadcast event that implements `ShouldBroadcast`
3. The event is sent to Pusher via the configured broadcast driver
4. Frontend clients subscribed via **Laravel Echo** receive the update instantly
5. The UI updates without any page refresh

### Broadcast Events

#### `OrderStatusUpdated`
Fired every time `OrderStateMachine::transition()` is called.

```
Channels:
  private-orders.{order_id}           → customer + rider + restaurant
  private-restaurant.{restaurant_id}  → restaurant owner dashboard
  private-admin.orders                → admin control tower

Payload:
{
  "order_id": 42,
  "status": "on_the_way",
  "updated_at": "2024-05-05 14:32:10"
}
```

#### `RiderLocationUpdated`
Fired every time a rider calls `POST /api/rider/location`.

```
Channels:
  private-admin.riders                → admin live map
  private-orders.{order_id}           → customer tracking page (if rider has active order)

Payload:
{
  "rider_id": 3,
  "lat": 30.0520,
  "lng": 31.2400,
  "updated_at": "2024-05-05 14:32:15"
}
```

### Channel Authorization (`routes/channels.php`)

| Channel | Authorization Rule |
|---|---|
| `orders.{orderId}` | Customer who placed it, assigned rider, restaurant owner, or admin |
| `admin.orders` | Admin role only |
| `admin.riders` | Admin role only |
| `App.Models.User.{id}` | User's own private notification channel |

### Frontend Subscription Example (Laravel Echo)

```javascript
// Customer tracking page — listens for order status and rider location
Echo.private(`orders.${orderId}`)
    .listen('OrderStatusUpdated', (event) => {
        updateStatusBadge(event.status);
    })
    .listen('RiderLocationUpdated', (event) => {
        riderMarker.setLatLng([event.lat, event.lng]);
    });

// Admin — listens to all riders
Echo.private('admin.riders')
    .listen('RiderLocationUpdated', (event) => {
        updateRiderMarker(event.rider_id, event.lat, event.lng);
    });
```

---

## 2. Queue Architecture (Redis / Database)

Background jobs are used for all operations that are too slow or too unreliable to run synchronously in a web request.

### Queue Jobs

| Job | Trigger | What It Does |
|---|---|---|
| `DispatchRiderJob` | Order confirmed | Calls `RiderDispatchService::dispatchRider()` to find and assign nearest rider |
| `ProcessPaymentJob` | Order delivered | Calls `PaymentService::processPayment()` to execute Stripe Connect transfers |
| `RecalculateSurgeJob` | Order placed / delivered / cancelled | Calls `SurgePricingEngine::calculateAndLog()` to update area surge multiplier |
| `SendNotificationJob` | Every FSM transition | Inserts notification record + can send push/email |

### Queue Flow Diagram

```mermaid
sequenceDiagram
    participant Web as Web Request
    participant FSM as OrderStateMachine
    participant Queue as Queue (Redis/DB)
    participant Worker as Queue Worker
    participant Pusher as Pusher
    participant Stripe as Stripe

    Web->>FSM: transition(order, 'confirmed', 'restaurant')
    FSM->>FSM: Validate transition + actor
    FSM->>FSM: Update order.status in DB
    FSM->>FSM: Create order_state_log record
    FSM->>Pusher: Dispatch OrderStatusUpdated event
    FSM->>Queue: Dispatch DispatchRiderJob
    FSM->>Queue: Dispatch RecalculateSurgeJob
    FSM->>Queue: Dispatch SendNotificationJob
    FSM-->>Web: Return updated Order (fast)

    Queue->>Worker: DispatchRiderJob picked up
    Worker->>Worker: Find nearest available rider (Haversine / Google Maps)
    Worker->>Worker: Create RiderDispatch record
    Worker->>Worker: Update order.rider_id

    Queue->>Worker: RecalculateSurgeJob picked up
    Worker->>Worker: Calculate new surge multiplier
    Worker->>Worker: Log to surge_pricing_logs
```

### Queue Configuration

```env
# Local development (no Redis needed)
QUEUE_CONNECTION=database

# Production (faster, more reliable)
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Start the queue worker:
```bash
# Development
php artisan queue:work

# Production (with retry and timeout)
php artisan queue:work --tries=3 --timeout=60

# Using Laravel Horizon (Redis only)
php artisan horizon
```

---

## 3. GPS Tracking Flow

Riders update their location via the mobile API. Each update triggers both a database write and a real-time broadcast.

```mermaid
sequenceDiagram
    participant App as Rider Mobile App
    participant API as Laravel API
    participant DB as Database
    participant Pusher as Pusher
    participant Admin as Admin Live Map
    participant Customer as Customer Tracking

    App->>API: POST /api/rider/location { lat, lng }
    API->>DB: UPDATE riders SET current_lat, current_lng
    API->>Pusher: Dispatch RiderLocationUpdated event
    Pusher-->>Admin: private-admin.riders channel update
    Pusher-->>Customer: private-orders.{id} channel update (if active delivery)
    Admin->>Admin: Move rider marker on map
    Customer->>Customer: Move rider pin on tracking page
```

---

## 4. Real-Time Order Tracking Flow (Full Sequence)

```mermaid
sequenceDiagram
    participant C as Customer
    participant Web as Laravel Web
    participant FSM as State Machine
    participant Queue as Queue Worker
    participant Pusher as Pusher
    participant Rider as Rider App

    C->>Web: Place order (POST /restaurants/{id}/order)
    Web->>FSM: transition(order, 'placed', 'customer')
    FSM-->>Pusher: OrderStatusUpdated [placed]
    Pusher-->>C: Status: Placed ✓

    Note over Web,Queue: Restaurant confirms via dashboard
    Web->>FSM: transition(order, 'confirmed', 'restaurant')
    FSM->>Queue: DispatchRiderJob.dispatch(order)
    FSM-->>Pusher: OrderStatusUpdated [confirmed]
    Pusher-->>C: Status: Confirmed ✓

    Queue->>Queue: Find nearest rider → assign Rider #3
    Note over Web,Queue: Rider goes to pick up food

    Rider->>Web: POST /api/rider/order-status { status: on_the_way }
    Web->>FSM: transition(order, 'on_the_way', 'rider')
    FSM-->>Pusher: OrderStatusUpdated [on_the_way]
    Pusher-->>C: Status: On the Way 🏍️

    loop Every GPS update
        Rider->>Web: POST /api/rider/location { lat, lng }
        Web-->>Pusher: RiderLocationUpdated
        Pusher-->>C: Rider location updated 📍
    end

    Rider->>Web: POST /api/rider/order-status { status: delivered }
    Web->>FSM: transition(order, 'delivered', 'rider')
    FSM->>Queue: ProcessPaymentJob.dispatch(order)
    FSM-->>Pusher: OrderStatusUpdated [delivered]
    Pusher-->>C: Order Delivered! 🎉

    Queue->>Queue: Execute Stripe Connect transfers
```

---

## 5. Surge Pricing Recalculation Flow

```mermaid
flowchart TD
    A[Order placed / delivered / cancelled] --> B[RecalculateSurgeJob dispatched]
    B --> C[SurgePricingEngine::calculateAndLog]
    C --> D{Gather Factors}
    D --> E[Active orders in last hour]
    D --> F[Available rider count]
    D --> G[Current hour / day]
    D --> H[Weather condition]
    E & F & G & H --> I[Strategy::calculate factors]
    I --> J{Raw multiplier > 3.0?}
    J -- Yes --> K[Cap at 3.0x, mark capped=true]
    J -- No --> L[Use raw value]
    K & L --> M{Multiplier > 1.0?}
    M -- Yes --> N[Mark previous surges inactive]
    M -- No --> O[Log as normal demand]
    N --> P[Create surge_pricing_log record active=true]
    O --> P
```

---

## 6. Admin Live Map (Polling Fallback)

When Pusher is not configured, the Admin Live Map falls back to **JavaScript polling**:

```javascript
// Refreshes every 5 seconds
setInterval(refreshMap, 5000);

function refreshMap() {
    fetch('/admin/live-data')          // GET /admin/live-data
        .then(r => r.json())
        .then(data => {
            data.riders.forEach(rider => {
                if (markers.riders[rider.id]) {
                    markers.riders[rider.id].setLatLng([rider.lat, rider.lng]);
                }
            });
        });
}
```

The `/admin/live-data` endpoint returns current rider positions and restaurant statuses. This provides a working live map even without Pusher credentials configured.
