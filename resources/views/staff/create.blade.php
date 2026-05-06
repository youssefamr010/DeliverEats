@extends('layouts.app')
@section('title', 'Add Staff - DeliverEats')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="de-card">
                <div class="de-card-body">
                    <h4 style="font-weight: 800;" class="mb-4"><i class="fas fa-user-plus me-2" style="color: var(--de-gold);"></i>Add New Staff</h4>
                    <form method="POST" action="{{ auth()->user()->role === 'admin' ? route('admin.staff.store') : route('restaurant.staff.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="name" class="form-control de-input" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control de-input" value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control de-input" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role</label>
                            <select name="role" class="form-select de-input" id="roleSelect" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control de-input" value="{{ old('phone') }}">
                        </div>
                        @if($restaurants->count())
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign to Restaurant</label>
                            <select name="restaurant_id" class="form-select de-input">
                                <option value="">— None —</option>
                                @foreach($restaurants as $r)
                                    <option value="{{ $r->id }}" {{ old('restaurant_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="mb-3" id="vehicleField" style="display:none;">
                            <label class="form-label fw-semibold">Vehicle Type</label>
                            <select name="vehicle_type" class="form-select de-input">
                                <option value="motorcycle">Motorcycle</option>
                                <option value="car">Car</option>
                                <option value="bicycle">Bicycle</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-de w-100"><i class="fas fa-save me-2"></i>Create Staff Member</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    document.getElementById('vehicleField').style.display = this.value === 'rider' ? 'block' : 'none';
});
document.getElementById('roleSelect').dispatchEvent(new Event('change'));
</script>
@endsection
