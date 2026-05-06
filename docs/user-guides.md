# DeliverEats — User Guides

---

## 👤 Customer Guide

### Getting Started

1. Visit the homepage at `http://127.0.0.1:8000`
2. Click **Sign Up** in the top navigation
3. Fill in your name, email, and password
4. Select **"Order Food"** as your role
5. Click **Register** — you are logged in automatically

---

### Browsing Restaurants

1. Click **Restaurants** in the navigation bar
2. Browse the grid of available restaurants — each card shows:
   - Restaurant name and cuisine type
   - Average rating (star score)
   - Current open/closed status
3. Click any restaurant card to view its full menu

---

### Placing an Order

1. On the restaurant page, click **Start Order**
2. Browse the menu categories (e.g. Burgers, Drinks, Sides)
3. Click **Add** on any item to add it to your cart
4. Use the **+** and **−** buttons to adjust quantities
5. Select a **variant** if available (e.g. Large, Extra Cheese)
6. Scroll down to the order summary panel
7. Enter your **delivery address**
8. Choose **payment method**:
   - **Cash on Delivery** — pay when rider arrives
   - **Card** — pay securely via Stripe Checkout
9. Optionally add a **tip** for the rider
10. Click **Place Order**

> If you selected Card, you will be redirected to Stripe's secure payment page. After payment, you return to the tracking page automatically.

---

### Tracking Your Order

After placing an order, you are redirected to the **order tracking page** which shows:

- **Status bar** — visual progress: Placed → Confirmed → Preparing → On the Way → Delivered
- **Rider location** — a live map pin that moves as the rider approaches
- **Order timeline** — every status change with exact timestamps
- **Order summary** — items, prices, and totals

The page auto-updates every 10 seconds (or instantly if Pusher is configured).

---

### Wallet

1. Click your name in the top navigation → **My Wallet**
2. View your current balance
3. Click **Top Up** to add funds via Stripe Checkout
4. Wallet balance can be used to pay for future orders

---

### Rating & Leaving Reviews

After your order is delivered:

1. Go to **Order History** → find the delivered order → click **Rate**
2. Give a **star rating (1–5)** for the restaurant
3. Leave an optional **written review**
4. Rate the **rider** separately (star rating)
5. Click **Submit Review**

---

### Order History

1. Click **Order History** in the navigation
2. See all past and active orders sorted by date
3. Click any order to view full details and timeline

---

## 🏪 Restaurant Owner Guide

### Setting Up Your Restaurant

1. Register with role **"Own Restaurant"**
2. After login, click **Create Restaurant**
3. Fill in:
   - Restaurant name and description
   - Full address
   - Cuisine type (e.g. Egyptian, Italian, Fast Food)
   - GPS coordinates (lat/lng) for dispatch accuracy
4. Click **Save** — your restaurant dashboard is now active

---

### Managing Your Menu

#### Add a Category
1. Go to **Dashboard → Manage Menu → Categories**
2. Click **Add Category** and enter a name (e.g. "Appetizers")
3. Click **Save** — the category appears in your menu list

#### Add Menu Items
1. Click **View Items** next to a category
2. Click **Add Item** and fill in:
   - Name, description, and price
   - Prep time (minutes)
3. Click **Save**

#### Add Variants
1. On the item list, click **Add Variant** next to an item
2. Enter the variant name (e.g. "Large") and price modifier (e.g. `+15.00`)
3. Click **Save** — customers can now select this option when ordering

#### Toggle Item Availability
- Click the **Available / Unavailable** toggle on any item to instantly hide or show it to customers — without deleting it

---

### Managing Incoming Orders

Your **Restaurant Dashboard** shows all active orders in real time:

| Status | Your Action |
|---|---|
| **Placed** | Click **Confirm** to accept or **Reject** to decline |
| **Confirmed** | A rider is automatically dispatched. Click **Start Preparing** when kitchen begins |
| **Preparing** | Click **Ready for Pickup** when food is packaged |
| **Ready for Pickup** | Rider will arrive to collect |
| **On the Way** | Rider is delivering — no action needed |
| **Delivered** | Order complete — earnings recorded automatically |

---

### Revenue Dashboard

1. Go to **My Restaurant → Revenue**
2. View:
   - **Total orders** and **total revenue** for the selected period
   - **Your earnings** after platform commission is deducted
   - **7-day bar chart** showing daily earnings trend
3. Toggle between **Today**, **7 Days**, and **30 Days**

---

### Staff Management

1. Go to **My Restaurant → Staff**
2. Click **Add Staff Member**
3. Create an account for a Chef:
   - Name, email, temporary password
4. The Chef can log in and see the **Chef Dashboard** — a simplified view showing only active orders and their preparation status

---

## 🏍️ Rider Guide

### Getting Started

1. Register with role **"Deliver Food"**
2. After login, you are taken to your **Rider Panel**
3. Your rider profile is created automatically

---

### Going Online

1. On the Rider Panel, click the **Go Online** toggle (turns green)
2. Your GPS location dot appears on the admin map
3. You will now receive dispatch notifications for nearby orders

> You must be **Online** to receive any dispatch requests. Going offline removes you from the dispatch pool immediately.

---

### Updating Your Location

**Via web panel:**
- Click **Update Location** and allow browser GPS access
- Your coordinates are sent to the server and broadcast to the admin map

**Via mobile API:**
```
POST /api/rider/location
Authorization: Bearer {token}
Body: { "lat": 30.0520, "lng": 31.2400 }
```

---

### Accepting a Dispatch

When an order is assigned to you:

1. The order appears in your **Incoming Dispatches** section
2. You see: restaurant name, estimated distance, and your potential earning
3. Click **Accept** to take the delivery
4. Click **Reject** if you cannot — the system automatically finds the next nearest rider

---

### Making a Delivery

1. Accepted orders appear in **Active Deliveries**
2. Click **Manage** to view the full order:
   - Customer name and delivery address
   - All items in the order
   - Order total and your earnings
3. Update status as you progress:
   - **On the Way** → after picking up food from restaurant
   - **Delivered** → after successfully dropping off to customer
4. Each status update is broadcast live to the customer's tracking page

---

### Earnings

1. Go to **Rider Panel → Earnings**
2. View:
   - **Total deliveries** and **total earnings** for selected period
   - **Average earning per delivery**
   - **Per-delivery breakdown** with restaurant name and amounts
3. Toggle between **Today**, **7 Days**, and **30 Days**

**How earnings are calculated:**

```
Your earning per delivery = Delivery Fee + Customer Tip
```

The platform never deducts from the delivery fee or tip — 100% goes to you.

---

## 🛡️ Admin Guide

### Control Tower Dashboard

Navigate to `/admin/dashboard` to see:

- **Platform stats**: total orders today, active riders, online restaurants, revenue
- **Live order feed**: all active orders with current status and timestamps
- **Online riders**: list of riders currently available
- **Quick filters**: filter orders by status (placed, confirmed, preparing, etc.)

---

### Live Map

Go to **Admin → Live Map** for a full-screen interactive map:

- 🟠 **Orange markers** = Restaurants
- 🟢 **Green markers** = Available riders (online, not busy)
- ⚫ **Dark markers** = Busy riders (currently on a delivery)
- Map auto-refreshes every **5 seconds**
- Hover over any marker to see name and status

---

### Order Management

Go to **Admin → All Orders**:

1. Browse the full order list with filters:
   - Filter by **status** (placed, confirmed, preparing, etc.)
   - Filter by **date range**
2. Click any order to view:
   - Full order details and items
   - Complete state transition timeline with actors and timestamps
   - Assigned rider info and dispatch history

---

### System Simulations

Go to **Admin → Simulations** to run built-in test scenarios:

| Simulation | What It Does |
|---|---|
| **Volume Spike** | Creates 50 simultaneous orders dispatched to available riders |
| **State Machine Test** | Validates FSM guards — attempts invalid transitions and confirms they throw |
| **Surge Pricing Test** | Triggers all three surge strategies and displays multiplier results |
| **Payment Split Test** | Runs payment calculations at varying commission rates and displays splits |
| **Cleanup** | Removes simulation data to restore a clean state |

---

### Revenue Dashboard

Go to **Admin → Revenue**:

- **Platform total revenue** = all commission + surge fees collected
- **Restaurant payouts** = total paid out to restaurant partners
- **Rider payouts** = total paid out to riders
- **Period filter**: Today, 7 Days, 30 Days

---

### Payments & Payouts

Go to **Admin → Payments** to see a full log of every `Payout` record:

| Column | Description |
|---|---|
| Order ID | The order this payout relates to |
| Order Total | Full amount paid by customer |
| Restaurant Amount | Their share after commission |
| Rider Amount | Delivery fee + tip |
| Platform Amount | Commission + surge fee |
| Status | `processed` or `failed` |
| Processed At | Timestamp of Stripe transfer |

---

### Feedbacks & Reviews

- **Admin → Feedbacks**: View and resolve customer feedback submitted via the public feedback form
- **Admin → Reviews**: Browse all restaurant reviews left by customers, including ratings and written comments
