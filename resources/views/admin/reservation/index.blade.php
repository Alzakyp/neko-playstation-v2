@extends('layouts.app')

@section('title', 'Manage Reservations')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>All Reservations</h6>
                        <p class="text-sm mb-0">Manage all PlayStation reservations</p>
                    </div>
                    <a href="{{ route('admin.reservation.create') }}" class="btn bg-gradient-primary">
                        <i class="fas fa-plus me-2"></i> New Reservation
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="mb-4">
                        <form action="{{ route('admin.reservation.index') }}" method="GET" class="row g-3">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="Search by name or ID">
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">PlayStation Type</label>
                                <select class="form-control" name="ps_type">
                                    <option value="">All Types</option>
                                    @foreach($psTypes as $type)
                                        <option value="{{ $type }}" {{ request('ps_type') == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="date" value="{{ request('date') }}">
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn bg-gradient-info me-2">
                                    <i class="fas fa-filter me-2"></i> Filter
                                </button>
                                <a href="{{ route('admin.reservation.index') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-redo me-2"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Reservations Table -->
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">PlayStation</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Schedule</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Duration/Price</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reservations as $reservation)
                                    <tr>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0 px-3">{{ $reservation->id }}</p>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $reservation->user->name }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $reservation->user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">PS{{ $reservation->playstation->ps_number }}</h6>
                                                    <p class="text-xs text-secondary mb-0">{{ $reservation->playstation->ps_type }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $reservation->start_time->format('M d, Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">
                                                {{ $reservation->start_time->format('h:i A') }} - {{ $reservation->end_time->format('h:i A') }}
                                            </p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $reservation->duration }} hours</p>
                                            <p class="text-xs text-secondary mb-0">{{ $reservation->formatted_price ?? 'Rp ' . number_format($reservation->total_price, 0, ',', '.') }}</p>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{
                                                $reservation->status == 'pending' ? 'warning text-dark' :
                                                ($reservation->status == 'confirmed' ? 'info' :
                                                ($reservation->status == 'completed' ? 'success' :
                                                ($reservation->status == 'cancelled' ? 'danger' : 'secondary')))
                                            }}">
                                                {{ ucfirst($reservation->status) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex">
                                                <a href="{{ route('admin.reservation.show', $reservation->id) }}"
                                                   class="btn btn-link text-info text-gradient px-3 mb-0"
                                                   title="View Details">
                                                    <i class="far fa-eye me-2"></i> View
                                                </a>

                                                @if(!in_array($reservation->status, ['completed', 'cancelled', 'refunded']))
                                                    <a href="{{ route('admin.reservation.edit', $reservation->id) }}"
                                                       class="btn btn-link text-dark px-3 mb-0"
                                                       title="Edit Reservation">
                                                        <i class="fas fa-pencil-alt me-2"></i> Edit
                                                    </a>

                                                    <form action="{{ route('admin.reservation.cancel', $reservation->id) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to cancel this reservation?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-link text-danger text-gradient px-3 mb-0">
                                                            <i class="far fa-times-circle me-2"></i> Cancel
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($reservation->status == 'pending' || $reservation->status == 'confirmed')
                                                    <form action="{{ route('admin.reservation.update-status', $reservation->id) }}"
                                                          method="POST" class="d-inline ms-2">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $reservation->status == 'pending' ? 'confirmed' : 'completed' }}">
                                                        <button type="submit" class="btn btn-link text-success text-gradient px-3 mb-0">
                                                            <i class="fas fa-check me-2"></i>
                                                            {{ $reservation->status == 'pending' ? 'Confirm' : 'Complete' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-sm mb-0">No reservations found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $reservations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade show p-2 bg-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header border-0">
                <i class="fas fa-check text-success me-2"></i>
                <span class="me-auto text-gradient text-success font-weight-bold">Success</span>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="position-fixed bottom-1 end-1 z-index-2">
        <div class="toast fade show p-2 bg-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header border-0">
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                <span class="me-auto text-gradient text-danger font-weight-bold">Error</span>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('error') }}
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    // Auto-hide toasts after 3 seconds
    window.addEventListener('DOMContentLoaded', (event) => {
        const toasts = document.querySelectorAll('.toast');
        if (toasts.length > 0) {
            setTimeout(() => {
                toasts.forEach(toast => {
                    const bsToast = bootstrap.Toast.getInstance(toast);
                    if (bsToast) {
                        bsToast.hide();
                    }
                });
            }, 3000);
        }
    });
</script>
@endsection
