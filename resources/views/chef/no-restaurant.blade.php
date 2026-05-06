@extends('layouts.app')
@section('title', 'No Restaurant Assigned')
@section('content')
<div class="container py-5 text-center">
    <div class="de-card" style="max-width: 500px; margin: 0 auto;">
        <div class="de-card-body py-5">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--de-gold);"></i>
            <h3 class="mt-3" style="font-weight: 800;">No Restaurant Assigned</h3>
            <p class="text-muted">You haven't been assigned to a restaurant yet. Please contact your manager or admin.</p>
        </div>
    </div>
</div>
@endsection
