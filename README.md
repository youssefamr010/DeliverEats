# DeliverEats - Premium Food Delivery Platform

DeliverEats is a high-end food delivery platform built with Laravel 11, featuring real-time tracking, surge pricing, and automated rider dispatch.

## Features

- **Real-Time Tracking**: Live order status and rider GPS updates powered by Pusher and Livewire 3.
- **Automated Dispatch**: Nearest-rider assignment using Google Maps Distance Matrix API (with Haversine fallback).
- **Dynamic Surge Pricing**: Strategy-based pricing engine that adjusts fees based on demand, weather, and rider availability.
- **State Machine Architecture**: Robust order lifecycle management with guards and event logging.
- **Split Payments**: Stripe Connect integration for automated revenue splitting between platform, restaurants, and riders.
- **Multi-Role Dashboards**: Custom interfaces for Admins, Restaurant Owners, Chefs, and Riders.

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Livewire 3, Blade, Vanilla CSS (Premium Obsidian Theme)
- **Real-Time**: Pusher / Laravel Echo
- **Queues**: Redis / Laravel Horizon ready
- **Maps**: Leaflet.js & Google Maps API
- **Payments**: Stripe Connect

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Setup database & migrations:
   ```bash
   php artisan migrate --seed
   ```
5. Install broadcasting:
   ```bash
   php artisan install:broadcasting
   ```
6. Start the servers:
   ```bash
   php artisan serve
   php artisan queue:work
   npm run dev
   ```

   > [!TIP]
   > If you don't have the Redis PHP extension installed, the system is configured to use the **Database** queue driver by default. Make sure to run `php artisan queue:work` to process background jobs.



## Documentation

Comprehensive documentation can be found in the `docs/` directory:
- [Architecture & State Machine](docs/architecture.md)
- [API Documentation](docs/api-docs.md)
- [User Guides](docs/user-guides.md)

## Testing

Run the feature-rich test suite:
```bash
php artisan test
```
