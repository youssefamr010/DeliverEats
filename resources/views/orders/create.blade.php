@extends('layouts.app')
@section('title', 'Order from ' . $restaurant->name)

@section('content')
<div class="container py-4" x-data="orderForm()">
    <div class="mb-5 animate-enter">
        <h1 class="display-5 fw-bold text-white mb-2">Order from {{ $restaurant->name }}</h1>
        <p class="text-muted fs-5">Select your favorites and let us handle the rest</p>
    </div>

    @if($surgeInfo['multiplier'] > 1)
    <div class="alert de-card border-accent border-opacity-25 py-3 px-4 mb-5 animate-enter">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-accent rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-bolt text-black"></i>
            </div>
            <div>
                <strong class="text-white">Surge Pricing Active</strong>
                <p class="mb-0 small text-muted">Delivery fee is {{ $surgeInfo['multiplier'] }}x due to {{ $surgeInfo['reason'] }}.</p>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('orders.store', $restaurant) }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                @foreach($restaurant->categories as $category)
                @if($category->is_active && $category->menuItems->count() > 0)
                <div class="mb-5 animate-enter">
                    <h3 class="fw-bold text-white mb-4 border-start border-primary border-4 ps-3">{{ $category->name }}</h3>
                    <div class="row g-3">
                        @foreach($category->menuItems->where('is_available', true) as $item)
                        <div class="col-md-12">
                            <div class="de-card" :class="getItemQty({{ $item->id }}) > 0 ? 'border-primary border-opacity-50 shadow-lg' : ''">
                                <div class="de-card-body p-3 d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-white mb-1">{{ $item->name }}</h6>
                                        @if($item->description)<p class="small text-muted mb-2">{{ Str::limit($item->description, 80) }}</p>@endif
                                        <span class="text-primary fw-bold">LE {{ number_format($item->base_price, 2) }}</span>
                                    </div>
                                    <div class="ms-4">
                                        <template x-if="getItemQty({{ $item->id }}) > 0">
                                            <div class="d-flex align-items-center bg-black bg-opacity-20 rounded-pill p-1">
                                                <button type="button" class="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255,255,255,0.05); color: white;" @click.stop="decrementItem({{ $item->id }})"><i class="fas fa-minus small"></i></button>
                                                <span class="px-3 fw-bold text-white" x-text="getItemQty({{ $item->id }})"></span>
                                                <button type="button" class="btn btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: var(--de-primary); color: white;" @click.stop="incrementItem({{ $item->id }})"><i class="fas fa-plus small"></i></button>
                                            </div>
                                        </template>
                                        <template x-if="getItemQty({{ $item->id }}) === 0">
                                            <button type="button" class="btn btn-de-outline py-2 px-4" @click.stop="addItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->base_price }})">
                                                <i class="fas fa-plus me-2 text-primary"></i>Add
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>

            <!-- Order Sidebar -->
            <div class="col-lg-4">
                <div class="de-card sticky-top" style="top: 100px; z-index: 100;">
                    <div class="de-card-body">
                        <h5 class="fw-bold text-white mb-4"><i class="fas fa-shopping-basket text-primary me-2"></i>Order Summary</h5>

                        <template x-if="items.length === 0">
                            <div class="text-center py-5">
                                <i class="fas fa-utensils text-muted fs-1 mb-3 opacity-20"></i>
                                <p class="text-muted small">Your basket is empty.<br>Start adding some delicious food!</p>
                            </div>
                        </template>

                        <div class="mb-4">
                            <template x-for="(item, index) in items" :key="item.id">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="flex-grow-1">
                                        <div class="text-white small fw-bold" x-text="item.name"></div>
                                        <div class="text-muted small" x-text="'x' + item.qty"></div>
                                        <input type="hidden" :name="'items['+index+'][menu_item_id]'" :value="item.id">
                                        <input type="hidden" :name="'items['+index+'][quantity]'" :value="item.qty">
                                    </div>
                                    <span class="text-white small fw-bold" x-text="'LE ' + (item.price * item.qty).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>

                        <template x-if="items.length > 0">
                            <div class="bg-black bg-opacity-10 rounded-4 p-3 mb-4">
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">Subtotal</span>
                                    <span class="text-white" x-text="'LE ' + subtotal.toLocaleString()"></span>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">Delivery</span>
                                    <span class="text-white">LE {{ number_format($restaurant->delivery_fee, 2) }}</span>
                                </div>
                                @if($surgeInfo['multiplier'] > 1)
                                <div class="d-flex justify-content-between small mb-2 text-accent">
                                    <span>Surge ({{ $surgeInfo['multiplier'] }}x)</span>
                                    <span>LE {{ number_format($restaurant->delivery_fee * ($surgeInfo['multiplier'] - 1), 2) }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between small mb-3">
                                    <span class="text-muted">Fees (5%)</span>
                                    <span class="text-white" x-text="'LE ' + (subtotal * 0.05).toLocaleString()"></span>
                                </div>
                                <div class="d-flex justify-content-between fs-5 border-top border-white border-opacity-10 pt-3">
                                    <span class="text-white fw-bold">Total</span>
                                    <span class="text-primary fw-black" x-text="'LE ' + total.toLocaleString()"></span>
                                </div>
                            </div>
                        </template>

                        <div class="mb-4">
                            <label class="form-label">Delivery Destination</label>
                            <input type="text" name="delivery_address" class="form-control de-input" required placeholder="Street address, building...">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select de-input">
                                <option value="cash">Cash on Delivery</option>
                                <option value="card">Credit Card (Stripe)</option>
                                <option value="wallet">Wallet Balance (LE {{ number_format(auth()->user()->wallet_balance, 2) }})</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-de w-100 py-3" :disabled="items.length === 0">
                            <i class="fas fa-check-circle me-2"></i>Confirm Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function orderForm() {
    return {
        items: [],
        deliveryFee: {{ $restaurant->delivery_fee }},
        surgeMultiplier: {{ $surgeInfo['multiplier'] }},

        get subtotal() { return this.items.reduce((sum, i) => sum + (i.price * i.qty), 0); },
        get surgeFee() { return this.surgeMultiplier > 1 ? this.deliveryFee * (this.surgeMultiplier - 1) : 0; },
        get total() { return this.subtotal + this.deliveryFee + this.surgeFee + (this.subtotal * 0.05); },

        getItemQty(id) { const item = this.items.find(i => i.id === id); return item ? item.qty : 0; },
        addItem(id, name, price) { this.items.push({ id, name, price, qty: 1 }); },
        incrementItem(id) { const item = this.items.find(i => i.id === id); if (item) item.qty++; },
        decrementItem(id) {
            const idx = this.items.findIndex(i => i.id === id);
            if (idx !== -1) { this.items[idx].qty--; if (this.items[idx].qty <= 0) this.items.splice(idx, 1); }
        },
    };
}
</script>
@endsection
