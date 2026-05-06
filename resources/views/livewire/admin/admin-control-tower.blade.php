<div class="container-fluid px-0">
    <div class="row g-4 mb-4">
        <!-- Active Orders Card -->
        <div class="col-md-2">
            <div class="de-card h-100 p-3" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.4), rgba(49, 46, 129, 0.4)); border: 1px solid rgba(59, 130, 246, 0.3);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase small mb-0 font-weight-bold" style="color: #93c5fd; font-size: 0.7rem; letter-spacing: 1px;">Active Orders</p>
                        <h3 class="fw-bold text-white mt-1 mb-0">{{ $activeOrdersCount }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.2); color: #60a5fa;">
                        <i class="fas fa-shopping-bag fs-5"></i>
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center small" style="color: #60a5fa; font-size: 0.75rem;">
                    <i class="fas fa-circle-notch fa-spin me-2"></i>
                    <span>Monitoring</span>
                </div>
            </div>
        </div>

        <!-- Available Riders Card -->
        <div class="col-md-2">
            <div class="de-card h-100 p-3" style="background: linear-gradient(135deg, rgba(6, 78, 59, 0.4), rgba(19, 78, 74, 0.4)); border: 1px solid rgba(16, 185, 129, 0.3);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase small mb-0 font-weight-bold" style="color: #6ee7b7; font-size: 0.7rem; letter-spacing: 1px;">Riders Online</p>
                        <h3 class="fw-bold text-white mt-1 mb-0">{{ $availableRidersCount }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.2); color: #34d399;">
                        <i class="fas fa-motorcycle fs-5"></i>
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center small" style="color: #34d399; font-size: 0.75rem;">
                    <span class="rounded-circle me-2 animate-pulse" style="width: 8px; height: 8px; background: #10b981;"></span>
                    <span>Ready</span>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-md-3">
            <div class="de-card h-100 p-3" style="background: linear-gradient(135deg, rgba(120, 53, 15, 0.4), rgba(124, 45, 18, 0.4)); border: 1px solid rgba(245, 158, 11, 0.3);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase small mb-0 font-weight-bold" style="color: #fcd34d; font-size: 0.7rem; letter-spacing: 1px;">Platform Rev (All Time)</p>
                        <h3 class="fw-bold text-white mt-1 mb-0">LE {{ number_format($totalRevenueAllTime, 2) }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                        <i class="fas fa-coins fs-5"></i>
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center small" style="color: #fbbf24; font-size: 0.75rem;">
                    <i class="fas fa-chart-line me-2"></i>
                    <span>Today: LE {{ number_format($totalRevenueToday, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Profit Card -->
        <div class="col-md-3">
            <div class="de-card h-100 p-3" style="background: linear-gradient(135deg, rgba(78, 14, 112, 0.4), rgba(88, 28, 135, 0.4)); border: 1px solid rgba(167, 139, 250, 0.3);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase small mb-0 font-weight-bold" style="color: #c4b5fd; font-size: 0.7rem; letter-spacing: 1px;">Est. Profit (All Time)</p>
                        <h3 class="fw-bold text-white mt-1 mb-0">LE {{ number_format($estimatedProfitAllTime, 2) }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(139, 92, 246, 0.2); color: #a78bfa;">
                        <i class="fas fa-hand-holding-usd fs-5"></i>
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center small" style="color: #a78bfa; font-size: 0.75rem;">
                    <i class="fas fa-percentage me-2"></i>
                    <span>Net after operations</span>
                </div>
            </div>
        </div>

        <!-- Payment Issues Card -->
        <div class="col-md-2">
            <div class="de-card h-100 p-3" style="background: linear-gradient(135deg, rgba(153, 27, 27, 0.4), rgba(127, 29, 29, 0.4)); border: 1px solid rgba(239, 68, 68, {{ $paymentIssuesCount > 0 ? '0.6' : '0.2' }});">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-uppercase small mb-0 font-weight-bold" style="color: #fca5a5; font-size: 0.7rem; letter-spacing: 1px;">Issues</p>
                        <h3 class="fw-bold text-white mt-1 mb-0">{{ $paymentIssuesCount }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(220, 38, 38, 0.2); color: #f87171;">
                        <i class="fas fa-exclamation-triangle fs-5"></i>
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center small" style="color: #f87171; font-size: 0.75rem;">
                    <i class="fas fa-shield-alt me-2"></i>
                    <span>{{ $paymentIssuesCount > 0 ? 'Urgent' : 'Healthy' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders List -->
    <div class="de-card overflow-hidden">
        <div class="p-4 border-bottom border-white-10 d-flex align-items-center justify-content-between" style="background: rgba(255,255,255,0.05);">
            <h4 class="mb-0 fw-bold text-white">Live Activity Feed</h4>
            <span class="badge bg-white bg-opacity-10 text-white-50 text-uppercase" style="font-size: 10px;">Latest 15 Updates</span>
        </div>
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-dark table-hover mb-0">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr class="small text-uppercase text-white-50" style="background: #1a1c24;">
                        <th class="px-4 py-3">Order</th>
                        <th class="py-3">Restaurant</th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">Status</th>
                        <th class="px-4 py-3 text-end">Total</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($recentOrders as $order)
                        <tr style="transition: background 0.3s;">
                            <td class="px-4 py-3 text-white">#{{ $order->id }}</td>
                            <td class="py-3 text-white-50">{{ $order->restaurant->name }}</td>
                            <td class="py-3 text-white-50">{{ $order->customer->name }}</td>
                            <td class="py-3">
                                @if($order->status === 'pending_payment')
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1 small fw-bold">
                                        <i class="fas fa-exclamation-triangle me-1"></i> PAYMENT ISSUE
                                    </span>
                                @else
                                    <span class="badge-status badge-{{ $order->status }} small text-uppercase">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end text-success fw-bold">LE {{ number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-5 text-center text-white-50 italic">No recent activity found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
