@extends('layouts.app')

@section('title', 'Reservation History')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Reservation History</h6>
                            <p class="text-sm mb-0">Past PlayStation reservations</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($historyReservations->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                PlayStation
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                User
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Date & Time
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Duration/Price
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Status
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($historyReservations as $reservation)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">
                                                                PS{{ $reservation->playstation->ps_number }}</h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                {{ $reservation->playstation->ps_type }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $reservation->user->name }}
                                                    </p>
                                                    <p class="text-xs text-secondary mb-0">{{ $reservation->user->email }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $reservation->start_time->format('M d, Y') }}
                                                    </p>
                                                    <p class="text-xs text-secondary mb-0">
                                                        {{ $reservation->start_time->format('h:i A') }} -
                                                        {{ $reservation->end_time->format('h:i A') }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $reservation->formatted_duration }}</p>
                                                    <p class="text-xs text-secondary mb-0">
                                                        {{ $reservation->formatted_price }}</p>
                                                </td>
                                                <td class="align-middle">
                                                    <span
                                                        class="badge bg-{{ $reservation->status == 'completed'
                                                            ? 'success'
                                                            : ($reservation->status == 'cancelled'
                                                                ? 'danger'
                                                                : 'secondary') }}">
                                                        {{ ucfirst($reservation->status) }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="{{ route('admin.reservation.show', $reservation->id) }}"
                                                        class="btn btn-sm bg-gradient-info">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-white text-center my-3">
                                <i class="fas fa-info-circle me-2"></i> No reservation history found
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
