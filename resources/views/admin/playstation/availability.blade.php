@extends('layouts.app')

@section('title', 'PlayStation Availability')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>PlayStation Availability</h6>
                    <p class="text-sm">Check available time slots for each PlayStation unit</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="p-4">
                        <form class="row g-3" id="availabilityForm">
                            <div class="col-md-5">
                                <label class="form-label">PlayStation</label>
                                <select class="form-select" id="playstation_id" required>
                                    <option value="">Select PlayStation</option>
                                    @foreach($playstations as $ps)
                                        <option value="{{ $ps->id }}">
                                            {{ $ps->ps_number }} - {{ $ps->ps_type }} ({{ $ps->formatted_rate }}/hour)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="check_date"
                                    min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn bg-gradient-primary mb-0 w-100">Check Availability</button>
                            </div>
                        </form>
                    </div>

                    <!-- Time slots display -->
                    <div class="px-4 pb-4">
                        <div class="row" id="availability-results">
                            <div class="col-12 text-center py-4 text-secondary">
                                <p>Select a PlayStation and date to check availability</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Reservations -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Pending Reservations</h6>
                        <p class="text-sm">Reservations waiting for approval</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.playstation.calendar') }}" class="btn btn-outline-primary btn-sm mb-0">Calendar View</a>
                        <a href="{{ route('admin.playstation.daily-report') }}" class="btn btn-outline-info btn-sm mb-0">Daily Report</a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Customer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">PlayStation</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Time Slot</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingReservations as $reservation)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $reservation->user->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $reservation->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $reservation->playstation->ps_number }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $reservation->playstation->ps_type }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $reservation->start_time->format('d M Y') }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $reservation->formatted_price ?? number_format($reservation->total_price, 0, ',', '.') }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $reservation->duration ?? $reservation->end_time->diffInHours($reservation->start_time) }} hours</p>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-center">
                                            <form action="{{ route('admin.reservation.update-status', $reservation) }}" method="POST" class="me-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn btn-sm bg-gradient-success mb-0">
                                                    Accept
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.reservation.update-status', $reservation) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm bg-gradient-danger mb-0">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No pending reservations</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 pt-4">
                        {{ $pendingReservations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle availability form submit
    document.getElementById('availabilityForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const playstationId = document.getElementById('playstation_id').value;
        const date = document.getElementById('check_date').value;
        const resultsDiv = document.getElementById('availability-results');

        if (!playstationId || !date) {
            resultsDiv.innerHTML = '<div class="col-12 text-center py-4 text-danger">Please select both a PlayStation and date</div>';
            return;
        }

        // Show loading state
        resultsDiv.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        // Fetch available slots
        fetch(`/admin/api/available-slots?playstation_id=${playstationId}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                const availableSlots = data.available_slots || [];
                const bookedSlots = data.booked_slots || [];

                if (availableSlots.length === 0 && bookedSlots.length === 0) {
                    resultsDiv.innerHTML = `
                        <div class="col-12 text-center py-4">
                            <div class="alert alert-warning" role="alert">
                                ${data.message || 'No available slots for this date'}
                            </div>
                        </div>`;
                    return;
                }

                // Display date and PlayStation info
                let html = `
                    <div class="col-12 mb-3">
                        <h5 class="mb-1">Time Slots for ${new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h5>
                        <p class="text-sm mb-0">${data.playstation.ps_number} - ${data.playstation.ps_type} (${data.playstation.formatted_rate}/hour)</p>
                    </div>`;

                // Create hour grid from 10:00 to 22:00
                html += '<div class="col-12 mb-4"><div class="card shadow-none border"><div class="card-body p-0">';
                html += '<div class="table-responsive"><table class="table align-items-center mb-0">';
                html += '<thead><tr><th>Hour</th><th class="text-center">Status</th><th>Information</th></tr></thead><tbody>';

                // Generate all possible hours
                for (let hour = 10; hour < 22; hour++) {
                    const hourStart = `${hour}:00`;
                    const hourEnd = `${hour + 1}:00`;

                    // Check if slot is available or booked
                    const availableSlot = availableSlots.find(slot => slot.start === hourStart);
                    const bookedSlot = bookedSlots.find(slot => slot.start === hourStart);

                    if (availableSlot) {
                        html += `
                            <tr>
                                <td>${hourStart} - ${hourEnd}</td>
                                <td class="text-center"><span class="badge bg-success">Available</span></td>
                                <td>This slot is available for booking</td>
                            </tr>
                        `;
                    } else if (bookedSlot) {
                        const statusBadge = bookedSlot.status === 'pending' ?
                            '<span class="badge bg-warning text-dark">Pending</span>' :
                            '<span class="badge bg-info">Confirmed</span>';

                        html += `
                            <tr>
                                <td>${hourStart} - ${hourEnd}</td>
                                <td class="text-center">${statusBadge}</td>
                                <td>
                                    <a href="/admin/reservation/${bookedSlot.reservation_id}" class="text-primary">
                                        View reservation details
                                    </a>
                                </td>
                            </tr>
                        `;
                    } else {
                        // Skip hours that aren't in either array (possibly past hours)
                        const now = new Date();
                        const slotDate = new Date(`${date}T${hour}:00:00`);

                        if (now > slotDate) {
                            html += `
                                <tr>
                                    <td>${hourStart} - ${hourEnd}</td>
                                    <td class="text-center"><span class="badge bg-secondary">Past</span></td>
                                    <td>This time slot has already passed</td>
                                </tr>
                            `;
                        } else {
                            html += `
                                <tr>
                                    <td>${hourStart} - ${hourEnd}</td>
                                    <td class="text-center"><span class="badge bg-secondary">Unavailable</span></td>
                                    <td>This time slot is not available</td>
                                </tr>
                            `;
                        }
                    }
                }

                html += '</tbody></table></div></div></div></div>';

                resultsDiv.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching available slots:', error);
                resultsDiv.innerHTML = '<div class="col-12 text-center py-4 text-danger">Error fetching available time slots</div>';
            });
    });
});
</script>
@endsection
