@extends('layouts.app')

@section('title', 'PlayStation Calendar')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    .fc-event-time {
        font-weight: bold;
    }
    .fc-event.pending {
        background-color: #ffc107;
        border-color: #ffc107;
    }
    .fc-event.confirmed {
        background-color: #0dcaf0;
        border-color: #0dcaf0;
    }
    .fc-event.completed {
        background-color: #198754;
        border-color: #198754;
    }
    .fc-event.cancelled {
        background-color: #dc3545;
        border-color: #dc3545;
        text-decoration: line-through;
    }
    .filter-container {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .filter-container label {
        margin-right: 10px;
        font-weight: 600;
    }
    .filter-container select {
        width: auto;
        margin-right: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>PlayStation Reservation Calendar</h6>
                    <div class="filter-container">
                        <label for="playstationFilter">PlayStation:</label>
                        <select id="playstationFilter" class="form-select form-select-sm">
                            <option value="all">All PlayStation Units</option>
                            @foreach($playstations as $ps)
                                <option value="{{ $ps->id }}" @if(isset($selectedPlaystationId) && $selectedPlaystationId == $ps->id) selected @endif>
                                    {{ $ps->ps_number }} - {{ $ps->ps_type }}
                                </option>
                            @endforeach
                        </select>

                        <a href="{{ route('admin.playstation.availability') }}" class="btn btn-sm btn-outline-primary">
                            <i class="ni ni-time-alarm me-1"></i> Check Availability
                        </a>
                        <a href="{{ route('admin.playstation.daily-report') }}" class="btn btn-sm btn-outline-info ms-2">
                            <i class="ni ni-calendar-grid-58 me-1"></i> Daily Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const playstationFilter = document.getElementById('playstationFilter');

    // Initialize the calendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '10:00:00',
        slotMaxTime: '22:00:00',
        allDaySlot: false,
        height: 'auto',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        eventClick: function(info) {
            if (info.event.url) {
                window.location.href = info.event.url;
                return false;
            }
        },
        eventDidMount: function(info) {
            // Add status classes
            if (info.event.extendedProps.status) {
                info.el.classList.add(info.event.extendedProps.status);
            }

            // Add tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'fc-tooltip';
            tooltip.innerHTML = `
                <div><strong>PlayStation:</strong> ${info.event.extendedProps.playstation || ''}</div>
                <div><strong>Customer:</strong> ${info.event.extendedProps.customer || ''}</div>
                <div><strong>Status:</strong> ${info.event.extendedProps.status || ''}</div>
                <div><strong>Price:</strong> ${info.event.extendedProps.price || ''}</div>
            `;

            info.el.setAttribute('title', tooltip.textContent.replace(/\s+/g, ' ').trim());
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            const psId = playstationFilter.value;
            const url = '/admin/api/calendar-events' +
                '?start=' + fetchInfo.startStr +
                '&end=' + fetchInfo.endStr +
                (psId !== 'all' ? '&playstation_id=' + psId : '');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching calendar data:', error);
                    failureCallback(error);
                });
        }
    });

    calendar.render();

    // Listen for PlayStation filter changes
    playstationFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });

    // Handle window resize to ensure calendar adjusts
    window.addEventListener('resize', function() {
        calendar.updateSize();
    });
});
</script>
@endsection
