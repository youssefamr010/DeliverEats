@extends('layouts.app')
@section('title', 'Live Map - DeliverEats Admin')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-white mb-0"><i class="fas fa-satellite-dish me-2 text-primary"></i>Platform Live View</h3>
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-dark border border-success border-opacity-25 text-success pulse px-3 py-2">
                <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i> LIVE
            </div>
            <button onclick="refreshMap()" class="btn-de-outline py-1 px-3 fs-6"><i class="fas fa-sync-alt"></i></button>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-9">
            <livewire:admin.admin-live-map />
        </div>
        <div class="col-lg-3">
            <div class="de-card h-100">
                <div class="de-card-body">
                    <h5 class="fw-bold text-white mb-4">Legend</h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fas fa-store text-white small"></i></div>
                            <div>
                                <div class="text-white small fw-bold">Restaurant</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Active kitchen</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fas fa-motorcycle text-white small"></i></div>
                            <div>
                                <div class="text-white small fw-bold">Rider (Available)</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Ready for orders</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="fas fa-motorcycle text-black small"></i></div>
                            <div>
                                <div class="text-white small fw-bold">Rider (Busy)</div>
                                <div class="text-muted" style="font-size: 0.7rem;">On delivery</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .map-marker {
        width: 30px; height: 30px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        border: 2px solid white;
        transition: all 0.3s ease;
    }
    .res-marker { background: #3b82f6; }
    .res-marker.inactive { filter: grayscale(1); opacity: 0.7; }
    .rider-marker { width: 26px; height: 26px; font-size: 0.8rem; }
    .rider-marker.available { background: #10b981; }
    .rider-marker.busy { background: #f59e0b; color: black; }
    
    .leaflet-popup-content-wrapper {
        background: #1e293b;
        color: white;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .leaflet-popup-tip { background: #1e293b; }
</style>
@endsection
