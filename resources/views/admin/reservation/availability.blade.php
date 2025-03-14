@extends('layouts.app')

@section('title', 'PlayStation Availability')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Check PlayStation Availability</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.reservation.availability') }}" method="GET">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PlayStation Type</label>
                                    <select class="form-control" name="ps_type">
                                        <option value="">All PlayStation Types</option>
                                        @foreach ($psTypes as $type)
                                            <option value="{{ $type }}" {{ $psType == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="date" min="{{ date('Y-m-d') }}"
                                        value="{{ $selectedDate }}">
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn bg-gradient-info">
                                    <i class="fas fa-search me-2"></i> Check Availability
                                </button>
                            </div>
                        </form>

                        <h6 class="mt-4 mb-3">Available PlayStation Units for
                            {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}</h6>

                        @if ($availablePlaystations->count() > 0)
                            <div class="row">
                                @foreach ($availablePlaystations as $ps)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h5 class="card-title mb-0">PS{{ $ps->ps_number }}</h5>
                                                        <p class="text-sm text-muted">{{ $ps->ps_type }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-success">Available</span>
                                                    </div>
                                                </div>
                                                <p class="card-text mt-3">
                                                    <strong>Rate:</strong>
                                                    {{ 'Rp ' . number_format($ps->hourly_rate, 0, ',', '.') }}/hour
                                                </p>
                                                <p class="card-text text-sm">
                                                    {{ $ps->description ?: 'No description available' }}
                                                </p>
                                                <a href="{{ route('admin.reservation.create', ['playstation_id' => $ps->id, 'date' => $selectedDate]) }}"
                                                    class="btn btn-sm bg-gradient-primary w-100">
                                                    Reserve Now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info text-white text-center my-3">
                                <i class="fas fa-info-circle me-2"></i>
                                No PlayStation units available for the selected date
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
