# DeliverEats — API Documentation

All API endpoints are prefixed with `/api`. Protected endpoints require a **Sanctum Bearer token** in the `Authorization` header.

```
Authorization: Bearer {your_token_here}
```

---

## Authentication

### Register
```
POST /api/register
```
**Body:**
```json
{
  "name": "John Smith",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password",
  "role": "customer",
  "phone": "+201001234567"
}
```
> `role` accepts: `customer` or `rider`

**Response `201`:**
```json
{
  "user": {
    "id": 7,
    "name": "John Smith",
    "email": "john@example.com",
    "role": "customer"
  },
  "token": "1|abc123tokenstring"
}
```

---

### Login
```
POST /api/login
```
**Body:**
```json
{
  "email": "john@example.com",
  "password": "password"
}
```
**Response `200`:**
```json
{
  "user": {
    "id": 7,
    "name": "John Smith",
    "email": "john@example.com",
    "role": "customer"
  },
  "token": "2|xyz456tokenstring"
}
```

---

### Logout
```
POST /api/logout
Authorization: Bearer {token}
```
**Response `200`:**
```json
{ "message": "Logged out successfully" }
```

---

### Get Current User
```
GET /api/user
Authorization: Bearer {token}
```
**Response `200`:**
```json
{
  "id": 7,
  "name": "John Smith",
  "email": "john@example.com",
  "role": "customer",
  "wallet_balance": "15.50"
}
```

---

## Restaurants

### List All Restaurants
```
GET /api/restaurants
Authorization: Bearer {token}
```
Returns paginated list of active restaurants with full menus.

**Response `200`:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Burger Palace",
      "address": "12 Tahrir Square, Cairo",
      "cuisine_type": "American",
      "is_open": true,
      "rating_avg": 4.3,
      "categories": [
        {
          "id": 1,
          "name": "Burgers",
          "menu_items": [
            {
              "id": 1,
              "name": "Classic Burger",
              "price": "45.00",
              "is_available": true,
              "variants": [
                { "id": 1, "name": "Large", "price_modifier": "10.00" }
              ]
            }
          ]
        }
      ]
    }
  ],
  "current_page": 1,
  "total": 6
}
```

---

### Get Single Restaurant
```
GET /api/restaurants/{id}
Authorization: Bearer {token}
```
**Response `200`:** Full restaurant object with all categories, items, and variants.

---

## Orders

### Place Order
```
POST /api/orders
Authorization: Bearer {token}
```
**Body:**
```json
{
  "restaurant_id": 1,
  "items": [
    { "menu_item_id": 1, "variant_id": null, "quantity": 2 },
    { "menu_item_id": 3, "variant_id": 5, "quantity": 1 }
  ],
  "delivery_address": "45 Nasr City, Cairo",
  "payment_method": "cash",
  "tip": 5.00
}
```
> `payment_method` accepts: `cash` or `card` (card triggers Stripe Checkout redirect)

**Response `201`:**
```json
{
  "message": "Order placed successfully",
  "order": {
    "id": 42,
    "status": "placed",
    "subtotal": "95.00",
    "delivery_fee": "10.00",
    "surge_fee": "0.00",
    "tip": "5.00",
    "total": "110.00",
    "delivery_address": "45 Nasr City, Cairo",
    "payment_method": "cash",
    "created_at": "2024-05-05T14:00:00.000000Z"
  }
}
```

---

### Get Order Details
```
GET /api/orders/{id}
Authorization: Bearer {token}
```
**Response `200`:**
```json
{
  "id": 42,
  "status": "preparing",
  "subtotal": "95.00",
  "delivery_fee": "10.00",
  "surge_fee": "5.00",
  "tip": "5.00",
  "total": "115.00",
  "delivery_address": "45 Nasr City, Cairo",
  "restaurant": { "id": 1, "name": "Burger Palace" },
  "items": [
    {
      "menu_item": { "name": "Classic Burger" },
      "variant": null,
      "quantity": 2,
      "unit_price": "45.00",
      "subtotal": "90.00"
    }
  ]
}
```

---

### Track Order (Live)
```
GET /api/orders/{id}/track
Authorization: Bearer {token}
```
Returns current status plus rider GPS position if assigned.

**Response `200`:**
```json
{
  "order_id": 42,
  "status": "on_the_way",
  "rider": {
    "name": "Rider Ahmed",
    "lat": 30.0520,
    "lng": 31.2400
  },
  "state_logs": [
    {
      "from_state": "placed",
      "to_state": "confirmed",
      "actor_type": "restaurant",
      "transitioned_at": "2024-05-05T14:05:00.000000Z"
    },
    {
      "from_state": "confirmed",
      "to_state": "preparing",
      "actor_type": "chef",
      "transitioned_at": "2024-05-05T14:07:00.000000Z"
    }
  ]
}
```

---

### Order History
```
GET /api/orders/history
Authorization: Bearer {token}
```
**Response `200`:** Paginated list of the authenticated customer's past orders.

---

## Rider API

All rider endpoints require a token for a user with `role = rider`.

### Update GPS Location
```
POST /api/rider/location
Authorization: Bearer {token}
```
**Body:**
```json
{ "lat": 30.0520, "lng": 31.2400 }
```
This fires the `RiderLocationUpdated` broadcast event.

**Response `200`:**
```json
{ "message": "Location updated" }
```

---

### Toggle Online / Offline
```
POST /api/rider/toggle-online
Authorization: Bearer {token}
```
**Response `200`:**
```json
{
  "message": "You are now online",
  "is_online": true
}
```

---

### Accept Dispatch
```
POST /api/rider/dispatch/{dispatch_id}/accept
Authorization: Bearer {token}
```
**Response `200`:**
```json
{
  "message": "Dispatch accepted",
  "order": { "id": 42, "status": "confirmed" }
}
```

---

### Reject Dispatch
```
POST /api/rider/dispatch/{dispatch_id}/reject
Authorization: Bearer {token}
```
**Body:**
```json
{ "reason": "Too far from restaurant" }
```
Automatically re-dispatches to the next nearest available rider.

**Response `200`:**
```json
{ "message": "Dispatch rejected. Reassigning to next rider." }
```

---

### Update Order Status
```
POST /api/rider/order-status
Authorization: Bearer {token}
```
**Body:**
```json
{
  "order_id": 42,
  "status": "on_the_way"
}
```
> Accepted values: `on_the_way`, `delivered`

Fires `OrderStatusUpdated` broadcast event. When `delivered`, triggers `ProcessPaymentJob`.

**Response `200`:**
```json
{
  "message": "Order status updated to on_the_way",
  "order": { "id": 42, "status": "on_the_way" }
}
```

---

## Error Responses

### Validation Error `422`
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Unauthenticated `401`
```json
{ "message": "Unauthenticated." }
```

### Forbidden `403`
```json
{ "message": "This action is unauthorized." }
```

### Invalid State Transition `422`
```json
{
  "message": "Invalid order state transition: delivered → preparing. Allowed transitions from 'delivered': none"
}
```

### Not Found `404`
```json
{ "message": "No query results for model [App\\Models\\Order] 999" }
```

---

## Pusher / Real-Time Channels

After authenticating, clients can subscribe to these private channels via **Laravel Echo**:

| Channel | Who Can Subscribe | Events |
|---|---|---|
| `private-orders.{id}` | Customer, assigned rider, restaurant owner, admin | `OrderStatusUpdated`, `RiderLocationUpdated` |
| `private-admin.orders` | Admin only | `OrderStatusUpdated` |
| `private-admin.riders` | Admin only | `RiderLocationUpdated` |

**Echo setup example:**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
});

Echo.private(`orders.${orderId}`)
    .listen('OrderStatusUpdated', (e) => console.log(e.status))
    .listen('RiderLocationUpdated', (e) => console.log(e.lat, e.lng));
```
