@extends('layouts.app')

@section('title', 'Active Reservations')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Active & Upcoming Reservations</h6>
                            <p class="text-sm mb-0">Manage current PlayStation bookings</p>
                        </div>
                        <a href="{{ route('admin.reservation.create') }}" class="btn bg-gradient-primary">
                            <i class="fas fa-plus me-2"></i> New Reservation
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($activeReservations->count() > 0)
                            <div class="row">
                                @foreach ($activeReservations as $reservation)
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card">
                                            <div class="card-header p-3 pb-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">PS{{ $reservation->playstation->ps_number }} -
                                                            {{ $reservation->playstation->ps_type }}</h6>
                                                    </div>
                                                    <div
                                                        class="badge {{ $reservation->status == 'pending' ? 'bg-warning' : 'bg-info' }} text-white">
                                                        {{ ucfirst($reservation->status) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body p-3 pt-1">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <p class="text-sm mb-1">
                                                            <i class="far fa-user me-1"></i> {{ $reservation->user->name }}
                                                        </p>
                                                        <p class="text-sm mb-1">
                                                            <i class="far fa-calendar me-1"></i>
                                                            {{ $reservation->start_time->format('D, M d, Y') }}
                                                        </p>
                                                        <p class="text-sm mb-2">
                                                            <i class="far fa-clock me-1"></i>
                                                            {{ $reservation->start_time->format('h:i A') }} -
                                                            {{ $reservation->end_time->format('h:i A') }}
                                                        </p>
                                                        <p class="text-sm mb-2">
                                                            <i class="fas fa-coins me-1"></i>
                                                            {{ $reservation->formatted_price }}
                                                        </p>
                                                        @if ($reservation->isInProgress())
                                                            <p class="text-sm mb-0 text-success">
                                                                <i class="fas fa-hourglass-half me-1"></i>
                                                                Time remaining: {{ $reservation->time_remaining }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-3">
                                                    <a href="{{ route('admin.reservation.show', $reservation->id) }}"
                                                        class="btn btn-sm bg-gradient-info">
                                                        View Details
                                                    </a>
                                                    @if ($reservation->isUpcoming())
                                                        <form
                                                            action="{{ route('admin.reservation.cancel', $reservation->id) }}"
                                                            method="POST" class="ms-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm bg-gradient-danger"
                                                                onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info text-white text-center my-3">
                                <i class="fas fa-info-circle me-2"></i> No active or upcoming reservations
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
