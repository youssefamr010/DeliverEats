@extends('layouts.app')
@section('title', $restaurant->name . ' Reviews')

@section('content')
<div class="container py-4">
    <h2 style="font-weight: 800;" class="mb-4"><i class="fas fa-star text-warning me-2"></i>{{ $restaurant->name }} — Reviews</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div style="font-size: 3rem; font-weight: 900; color: var(--de-primary);">{{ number_format($restaurant->rating_avg, 1) }}</div>
                <div class="stars mt-1">
                    @for($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= round($restaurant->rating_avg) ? '' : 'empty' }}"></i>@endfor
                </div>
                <div class="stat-label mt-1">{{ $restaurant->rating_count }} reviews</div>
            </div>
        </div>
    </div>
    @foreach($ratings as $rating)
    <div class="de-card mb-3">
        <div class="de-card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>{{ $rating->user->name }}</strong>
                    <div class="stars mt-1">@for($i=1;$i<=5;$i++)<i class="fas fa-star {{ $i<=$rating->score?'':'empty' }}"></i>@endfor</div>
                </div>
                <span class="text-muted small">{{ $rating->created_at->diffForHumans() }}</span>
            </div>
            @if($rating->review)<p class="mt-2 mb-0">{{ $rating->review->comment }}</p>@endif
            @if($rating->review && $rating->review->response)
            <div class="mt-2 p-3 rounded" style="background: rgba(255,255,255,0.05); border-left: 3px solid var(--de-primary);">
                <small class="fw-bold text-white">Restaurant Response:</small>
                <p class="small mb-0 text-muted">{{ $rating->review->response }}</p>
                <small class="text-muted d-block mt-1" style="font-size: 0.65rem;">Responded on {{ $rating->review->responded_at->format('M d, Y') }}</small>
            </div>
            @elseif($rating->review && (auth()->user()->role === 'admin' || (isset($restaurant) && $restaurant->owner_id === auth()->id())))
            <div class="mt-2">
                <button class="btn btn-sm btn-de-outline py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#respond-{{ $rating->review->id }}" style="font-size: 0.7rem;">
                    <i class="fas fa-reply me-1"></i>Respond
                </button>
                <div class="collapse mt-2" id="respond-{{ $rating->review->id }}">
                    <form action="{{ route('ratings.respond', $rating->review) }}" method="POST">
                        @csrf
                        <textarea name="response" class="form-control de-input mb-2" rows="2" placeholder="Write your response..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-de">Submit Response</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach
    {{ $ratings->links() }}
</div>
@endsection
