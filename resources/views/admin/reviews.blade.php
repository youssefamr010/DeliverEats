@extends('layouts.app')
@section('title', 'All Reviews - Admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;"><i class="fas fa-star me-2 text-warning"></i>Ratings & Reviews</h2>
            <p class="text-muted mb-0">Monitor all platform reviews</p>
        </div>
    </div>

    <div class="de-card">
        <div class="de-card-body">
            @if($ratings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-color: rgba(255,255,255,0.05);">
                        <thead class="bg-black bg-opacity-10">
                            <tr>
                                <th class="py-3 px-4" style="color: #475569;">Order</th>
                                <th class="py-3" style="color: #475569;">User</th>
                                <th class="py-3" style="color: #475569;">Target</th>
                                <th class="py-3" style="color: #475569;">Score</th>
                                <th class="py-3" style="color: #475569;">Comment</th>
                                <th class="py-3 px-4" style="color: #475569;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ratings as $rating)
                            <tr>
                                <td class="px-4"><a href="{{ route('orders.track', $rating->order_id) }}" class="text-primary text-decoration-none fw-bold">#{{ $rating->order_id }}</a></td>
                                <td class="text-white">{{ $rating->user->name }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-white bg-opacity-5 text-muted x-small" style="font-size: 0.6rem;">{{ strtoupper(class_basename($rating->rateable_type)) }}</span>
                                        <div class="fw-bold text-white small">{{ $rating->rateable->name ?? ($rating->rateable->user->name ?? 'Unknown') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-accent small d-flex gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $rating->score ? '' : 'text-muted opacity-25' }}"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td style="max-width: 300px;">
                                    @if($rating->review)
                                        <p class="mb-0 text-muted small" title="{{ $rating->review->comment }}">
                                            {{ Str::limit($rating->review->comment, 80) }}
                                        </p>
                                    @else
                                        <span class="text-muted small opacity-50">No comment</span>
                                    @endif
                                </td>
                                <td class="px-4 text-muted small">{{ $rating->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">{{ $ratings->links() }}</div>
            @else
                <div class="text-center py-5">
                    <h5 class="text-muted">No ratings or reviews yet.</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
