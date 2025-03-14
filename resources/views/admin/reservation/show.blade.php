@extends('layouts.app')

@section('title', 'Reservation Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">Reservation Details</h5>
                            <p class="text-sm mb-0">Reservation #{{ $reservation->id }}</p>
                        </div>
                        <div>
                            @if(!in_array($reservation->status, ['completed', 'cancelled', 'refunded']))
                                <a href="{{ route('admin.reservation.edit', $reservation->id) }}" class="btn bg-gradient-info btn-sm mb-0">
                                    <i class="fas fa-edit"></i>&nbsp;&nbsp;Edit
                                </a>
                            @endif
                            <a href="{{ route('admin.reservation.index') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                                <i class="fas fa-arrow-left"></i>&nbsp;&nbsp;Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Status:</h6>
                            <span class="badge bg-gradient-{{
                                $reservation->status == 'pending' ? 'warning text-dark' :
                                ($reservation->status == 'confirmed' ? 'info' :
                                ($reservation->status == 'completed' ? 'success' :
                                ($reservation->status == 'cancelled' ? 'danger' : 'secondary')))
                            }}">{{ ucfirst($reservation->status) }}</span>

                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4">Customer:</h6>
                            <p class="text-sm mb-2">{{ $reservation->user->name }}</p>
                            <p class="text-sm mb-2"><i class="fas fa-envelope me-2"></i>{{ $reservation->user->email }}</p>

                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4">PlayStation:</h6>
                            <p class="text-sm mb-2">PS{{ $reservation->playstation->ps_number }} - {{ $reservation->playstation->ps_type }}</p>
                            <p class="text-sm mb-2"><i class="fas fa-coins me-2"></i>{{ number_format($reservation->playstation->hourly_rate, 0, ',', '.') }} / hour</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder">Schedule:</h6>
                            <p class="text-sm mb-2"><i class="fas fa-calendar me-2"></i>{{ $reservation->start_time->format('F d, Y') }}</p>
                            <p class="text-sm mb-2"><i class="fas fa-clock me-2"></i>{{ $reservation->start_time->format('h:i A') }} - {{ $reservation->end_time->format('h:i A') }}</p>

                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4">Duration & Price:</h6>
                            <p class="text-sm mb-2"><i class="fas fa-hourglass-half me-2"></i>{{ $reservation->duration }} hours</p>
                            <p class="text-sm mb-2"><i class="fas fa-tag me-2"></i>{{ $reservation->formatted_price ?? 'Rp ' . number_format($reservation->total_price, 0, ',', '.') }}</p>

                            @if($reservation->notes)
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mt-4">Notes:</h6>
                                <p class="text-sm mb-2">{{ $reservation->notes }}</p>
                            @endif
                        </div>
                    </div>

                    @if($reservation->isInProgress())
                        <div class="alert alert-info text-white mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            This reservation is currently in progress. Time remaining: {{ $reservation->time_remaining }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    @if($reservation->payment)
                        <div class="d-flex">
                            <div>
                                <h6 class="text-sm mb-0">Amount:</h6>
                                <p class="text-sm font-weight-bold mb-0">{{ 'Rp ' . number_format($reservation->payment->amount, 0, ',', '.') }}</p>
                            </div>
                            <div class="ms-auto">
                                <span class="badge badge-pill bg-gradient-{{
                                    $reservation->payment->payment_status == 'paid' ? 'success' :
                                    ($reservation->payment->payment_status == 'pending' ? 'warning text-dark' : 'secondary')
                                }}">
                                    {{ ucfirst($reservation->payment->payment_status) }}
                                </span>
                            </div>
                        </div>

                        <hr class="horizontal dark my-3">

                        <div class="mb-2">
                            <h6 class="text-sm mb-0">Payment Date:</h6>
                            <p class="text-sm font-weight-bold mb-0">
                                {{ $reservation->payment->payment_date ? $reservation->payment->payment_date->format('F d, Y H:i') : 'Not paid yet' }}
                            </p>
                        </div>

                        @if($reservation->payment->payment_method)
                            <div class="mb-2">
                                <h6 class="text-sm mb-0">Payment Method:</h6>
                                <p class="text-sm font-weight-bold mb-0">{{ ucfirst($reservation->payment->payment_method) }}</p>
                            </div>
                        @endif

                        @if($reservation->payment->refund)
                            <hr class="horizontal dark my-3">
                            <div class="alert alert-warning text-white">
                                <i class="fas fa-undo me-2"></i>
                                <strong>Refunded:</strong> {{ 'Rp ' . number_format($reservation->payment->refund->amount, 0, ',', '.') }}
                                <p class="text-sm mb-0 mt-2">Refund date: {{ $reservation->payment->refund->created_at->format('F d, Y') }}</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary text-white" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            No payment information available
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($reservation->status == 'pending')
                            <form action="{{ route('admin.reservation.update-status', $reservation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn bg-gradient-info w-100">
                                    <i class="fas fa-check me-2"></i> Confirm Reservation
                                </button>
                            </form>
                        @endif

                        @if($reservation->status == 'confirmed')
                            <form action="{{ route('admin.reservation.update-status', $reservation->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn bg-gradient-success w-100">
                                    <i class="fas fa-check-double me-2"></i> Mark as Completed
                                </button>
                            </form>
                        @endif

                        @if(!in_array($reservation->status, ['completed', 'cancelled', 'refunded']))
                            <form action="{{ route('admin.reservation.cancel', $reservation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn bg-gradient-danger w-100"
                                        onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                    <i class="fas fa-times me-2"></i> Cancel Reservation
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="mb-0">Created & Updated Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-sm mb-0">
                                <strong>Created at:</strong> {{ $reservation->created_at->format('F d, Y H:i:s') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-sm mb-0">
                                <strong>Last updated:</strong> {{ $reservation->updated_at->format('F d, Y H:i:s') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
