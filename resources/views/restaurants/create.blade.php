@extends('layouts.app')
@section('title', 'Create Restaurant - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="de-card">
                <div class="de-card-body p-4">
                    <h3 style="font-weight: 800;" class="mb-4"><i class="fas fa-store me-2" style="color: var(--de-primary);"></i>Register Your Restaurant</h3>
                    <form method="POST" action="{{ route('restaurant.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Restaurant Name</label>
                                <input type="text" name="name" class="form-control de-input" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cuisine Type</label>
                                <input type="text" name="cuisine_type" class="form-control de-input" value="{{ old('cuisine_type') }}" placeholder="e.g. Italian, Chinese, Fast Food">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control de-input" rows="3">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control de-input" value="{{ old('address') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control de-input" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Opens At</label>
                                <input type="time" name="opens_at" class="form-control de-input" value="{{ old('opens_at', '09:00') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Closes At</label>
                                <input type="time" name="closes_at" class="form-control de-input" value="{{ old('closes_at', '23:00') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Cover Photo URL</label>
                                <input type="url" name="image" class="form-control de-input" value="{{ old('image') }}" placeholder="https://images.unsplash.com/photo-...">
                                <small class="text-muted">High-quality Unsplash URLs recommended for a premium look.</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-de mt-4"><i class="fas fa-check me-2"></i>Create Restaurant</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
