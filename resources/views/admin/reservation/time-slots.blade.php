@extends('layouts.app')

@section('title', 'Available Time Slots')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Available Time Slots for PS{{ $playstation->ps_number }} - {{ $playstation->ps_type }}</h6>
                        <p class="text-sm">Date: {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</p>
                    </div>
                    <div class="card-body">
                        @if (count($availableSlots) > 0)
                            <div class="row">
                                @foreach ($availableSlots as $slot)
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <p class="mb-2 text-lg">{{ $slot['start'] }} - {{ $slot['end'] }}</p>
                                                <a href="{{ route('admin.reservation.create', [
                                                    'playstation_id' => $playstation->id,
                                                    'date' => $date,
                                                    'start_time' => $slot['start_datetime'],
                                                ]) }}"
                                                    class="btn btn-sm bg-gradient-success w-100">
                                                    Select This Time
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info text-white text-center">
                                <i class="fas fa-info-circle me-2"></i> No available time slots for this date
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.reservation.availability') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Availability
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
