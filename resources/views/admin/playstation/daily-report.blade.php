@extends('layouts.app')

@section('title', 'PlayStation Daily Report')

@section('styles')
    <style>
        .time-slot {
            padding: 8px;
            text-align: center;
            border-radius: 4px;
            font-weight: 500;
        }

        .time-slot.booked {
            background-color: rgba(13, 202, 240, 0.2);
            color: #0dcaf0;
        }

        .time-slot.available {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .time-slot.past {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        .ps-label {
            display: flex;
            align-items: center;
            height: 100%;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-weight: 600;
        }

        .hour-header {
            text-align: center;
            font-weight: 600;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .report-container {
            overflow-x: auto;
        }

        .date-picker {
            width: 200px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>PlayStation Daily Report</h6>
                        <div>
                            <form method="GET" class="d-flex align-items-center">
                                <input type="date" name="date" class="form-control date-picker me-2"
                                    value="{{ $selectedDate->format('Y-m-d') }}">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    Show Report
                                </button>
                                <a href="{{ route('admin.playstation.calendar') }}"
                                    class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="ni ni-calendar-grid-58 me-1"></i> Calendar View
                                </a>
                                <a href="{{ route('admin.playstation.availability') }}"
                                    class="btn btn-sm btn-outline-info ms-2">
                                    <i class="ni ni-time-alarm me-1"></i> Check Availability
                                </a>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-3">Daily Report for {{ $selectedDate->format('l, F j, Y') }}</h5>

                        <div class="report-container">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                PlayStation</th>
                                            @for ($hour = 10; $hour < 22; $hour++)
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    {{ sprintf('%02d:00', $hour) }} - {{ sprintf('%02d:00', $hour + 1) }}
                                                </th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($hourlyData as $ps)
                                            <tr>
                                                <td>
                                                    <div class="ps-label">
                                                        {{ $ps['ps_number'] }} ({{ $ps['ps_type'] }})
                                                    </div>
                                                </td>
                                                @for ($hour = 10; $hour < 22; $hour++)
                                                    <td>
                                                        @if (isset($ps['hours'][$hour]))
                                                            @php $hourData = $ps['hours'][$hour]; @endphp

                                                            @if ($hourData['is_booked'])
                                                                <div class="time-slot booked">
                                                                    <div class="mb-1">{{ $hourData['time'] }}</div>
                                                                    <div class="small">
                                                                        {{ $hourData['reservation']['user'] }}</div>
                                                                    <a href="{{ route('admin.reservation.show', $hourData['reservation']['id']) }}"
                                                                        class="badge bg-info">View</a>
                                                                </div>
                                                            @elseif($selectedDate->isToday() && now()->format('H') > $hour)
                                                                <div class="time-slot past">
                                                                    {{ $hourData['time'] }}
                                                                </div>
                                                            @else
                                                                <div class="time-slot available">
                                                                    {{ $hourData['time'] }}
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="time-slot">{{ sprintf('%02d:00', $hour) }} -
                                                                {{ sprintf('%02d:00', $hour + 1) }}</div>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6>Legend:</h6>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="d-flex align-items-center">
                                    <div class="time-slot booked me-2" style="width: 20px; height: 20px"></div>
                                    <span>Booked</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="time-slot available me-2" style="width: 20px; height: 20px"></div>
                                    <span>Available</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="time-slot past me-2" style="width: 20px; height: 20px"></div>
                                    <span>Past (Today)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 col-lg-6">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Daily Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            PlayStation</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Booked Hours</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Usage %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($hourlyData as $ps)
                                        @php
                                            $bookedHours = 0;
                                            foreach ($ps['hours'] as $hour => $data) {
                                                if ($data['is_booked']) {
                                                    $bookedHours++;
                                                }
                                            }
                                            $totalHours = 12; // 10:00 - 22:00
                                            $usagePercentage = ($bookedHours / $totalHours) * 100;
                                        @endphp
                                        <tr>
                                            <td>{{ $ps['ps_number'] }} ({{ $ps['ps_type'] }})</td>
                                            <td>{{ $bookedHours }} / {{ $totalHours }} hours</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2">{{ number_format($usagePercentage, 1) }}%</span>
                                                    <div class="progress w-100">
                                                        <div class="progress-bar bg-info" role="progressbar"
                                                            style="width: {{ $usagePercentage }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Peak Hours</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart">
                            <canvas id="peakHoursChart" class="chart-canvas" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Calculate peak hours data
            const hourLabels = [];
            const hourlyBookings = [];

            for (let hour = 10; hour < 22; hour++) {
                hourLabels.push(`${hour}:00 - ${hour+1}:00`);

                // Count bookings for this hour across all PlayStations
                let bookingCount = 0;
                @foreach ($hourlyData as $ps)
                    @foreach ($ps['hours'] as $h => $data)
                        if ({{ $h }} === hour && {{ $data['is_booked'] ? 'true' : 'false' }}) {
                            bookingCount++;
                        }
                    @endforeach
                @endforeach

                hourlyBookings.push(bookingCount);
            }

            // Create the peak hours chart
            const ctx = document.getElementById('peakHoursChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'PlayStation Bookings',
                        data: hourlyBookings,
                        backgroundColor: 'rgba(13, 202, 240, 0.6)',
                        borderColor: '#0dcaf0',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: {{ count($playstations) }},
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Number of PlayStation Bookings by Hour'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y} PlayStation(s)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
