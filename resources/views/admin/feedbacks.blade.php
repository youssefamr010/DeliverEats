@extends('layouts.app')
@section('title', 'Feedbacks & Complaints - Admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800;"><i class="fas fa-comment-dots me-2" style="color: var(--de-gold);"></i>Feedbacks & Complaints</h2>
            <p class="text-muted mb-0">Manage user feedback and issues</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; border-radius: 12px; padding: 1rem;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="de-card">
        <div class="de-card-body">
            @if($feedbacks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-color: rgba(255,255,255,0.05);">
                        <thead class="bg-black bg-opacity-10">
                            <tr>
                                <th class="py-3 px-4" style="color: #475569;">#</th>
                                <th class="py-3" style="color: #475569;">Sender</th>
                                <th class="py-3" style="color: #475569;">Type</th>
                                <th class="py-3" style="color: #475569;">Message</th>
                                <th class="py-3" style="color: #475569;">Status</th>
                                <th class="py-3" style="color: #475569;">Received</th>
                                <th class="py-3 px-4" style="color: #475569;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedbacks as $fb)
                            <tr>
                                <td class="px-4"><strong class="text-white opacity-25">#{{ $fb->id }}</strong></td>
                                <td>
                                    <div class="fw-bold text-white small">{{ $fb->name }}</div>
                                    <div class="x-small text-muted" style="font-size: 0.7rem;">{{ $fb->email }}</div>
                                    @if($fb->user)
                                        <span class="badge bg-white bg-opacity-5 text-muted x-small mt-1" style="font-size: 0.6rem;">{{ strtoupper(str_replace('_', ' ', $fb->user->role)) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $fb->subject === 'Complaint' ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 {{ $fb->subject === 'Complaint' ? 'text-danger' : 'text-primary' }} x-small" style="font-size: 0.65rem; border: 1px solid currentColor;">
                                        {{ strtoupper($fb->subject) }}
                                    </span>
                                </td>
                                <td style="max-width: 300px;">
                                    <p class="mb-0 text-muted small" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $fb->message }}">
                                        {{ $fb->message }}
                                    </p>
                                </td>
                                <td>
                                    @if($fb->status === 'pending')
                                        <span class="badge-status badge-preparing">Pending</span>
                                    @else
                                        <span class="badge-status badge-delivered">Resolved</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $fb->created_at->format('M d, H:i') }}</td>
                                <td class="px-4">
                                    @if($fb->status === 'pending')
                                        <form action="{{ route('admin.feedbacks.resolve', $fb) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-de py-1 px-2" style="font-size: 0.7rem;">
                                                <i class="fas fa-check me-1"></i> Resolve
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-success small fw-bold"><i class="fas fa-check-double me-1"></i>Done</div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">{{ $feedbacks->links() }}</div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox mb-3" style="font-size: 3rem; color: rgba(255,255,255,0.1);"></i>
                    <h5 class="text-white">No feedbacks yet</h5>
                    <p class="text-muted">You're all caught up!</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
