@extends('layouts.app')
@section('title', 'Edit Restaurant - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="de-card">
                <div class="de-card-body p-4">
                    <h3 style="font-weight: 800;" class="mb-4"><i class="fas fa-edit me-2" style="color: var(--de-primary);"></i>Edit Restaurant</h3>
                    <form method="POST" action="{{ route('restaurant.update', $restaurant) }}">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Restaurant Name</label>
                                <input type="text" name="name" class="form-control de-input" value="{{ old('name', $restaurant->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cuisine Type</label>
                                <input type="text" name="cuisine_type" class="form-control de-input" value="{{ old('cuisine_type', $restaurant->cuisine_type) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control de-input" rows="3">{{ old('description', $restaurant->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control de-input" value="{{ old('address', $restaurant->address) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control de-input" value="{{ old('phone', $restaurant->phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Opens At</label>
                                <input type="time" name="opens_at" class="form-control de-input" value="{{ old('opens_at', $restaurant->opens_at ? \Carbon\Carbon::parse($restaurant->opens_at)->format('H:i') : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Closes At</label>
                                <input type="time" name="closes_at" class="form-control de-input" value="{{ old('closes_at', $restaurant->closes_at ? \Carbon\Carbon::parse($restaurant->closes_at)->format('H:i') : '') }}">
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-de"><i class="fas fa-save me-2"></i>Save Changes</button>
                            <a href="{{ route('restaurant.dashboard') }}" class="btn btn-de-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
