@extends('layouts.app')

@section('title', 'Create New Reservation')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between">
                            <div>
                                <h5 class="mb-0">Create New Reservation</h5>
                            </div>
                            <a href="{{ route('admin.reservation.index') }}" class="btn bg-gradient-secondary btn-sm mb-0">
                                <i class="fas fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.reservation.store') }}" method="POST" onsubmit="console.log('Form submitted'); return true;">
                            @csrf
                            <div class="row">
                                <!-- Customer Selection Type -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="customerTypeSwitch"
                                            name="is_walkin" value="1" {{ old('is_walkin') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="customerTypeSwitch">Walk-in Customer</label>
                                    </div>
                                </div>

                                <!-- Registered Customer Selection (shown when not walk-in) -->
                                <div class="col-md-12" id="registeredCustomerSection"
                                    style="{{ old('is_walkin') ? 'display: none;' : '' }}">
                                    <div class="form-group">
                                        <label for="user_id" class="form-control-label">Customer</label>
                                        <select class="form-control @error('user_id') is-invalid @enderror" id="user_id"
                                            name="user_id">
                                            <option value="">Select Customer</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Walk-in Customer Info (shown when is walk-in) -->
                                <div class="col-md-12" id="walkinCustomerSection"
                                    style="{{ old('is_walkin') ? '' : 'display: none;' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="walkin_name" class="form-control-label">Customer Name</label>
                                                <input type="text"
                                                    class="form-control @error('walkin_name') is-invalid @enderror"
                                                    id="walkin_name" name="walkin_name" value="{{ old('walkin_name') }}">
                                                @error('walkin_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="walkin_phone" class="form-control-label">Phone Number</label>
                                                <input type="text"
                                                    class="form-control @error('walkin_phone') is-invalid @enderror"
                                                    id="walkin_phone" name="walkin_phone"
                                                    value="{{ old('walkin_phone') }}">
                                                @error('walkin_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PlayStation Selection -->
                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <label for="playstation_id" class="form-control-label">PlayStation</label>
                                        <select class="form-control @error('playstation_id') is-invalid @enderror"
                                            id="playstation_id" name="playstation_id" required>
                                            <option value="">Select PlayStation</option>
                                            @foreach ($playstations as $ps)
                                                <option value="{{ $ps->id }}"
                                                    {{ old('playstation_id') == $ps->id ? 'selected' : '' }}>
                                                    PS{{ $ps->ps_number }} ({{ $ps->ps_type }}) -
                                                    {{ number_format($ps->hourly_rate, 0, ',', '.') }}/hour
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('playstation_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Replace the current time selection section with this -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Duration (hours)</label>
                                        <select class="form-control" id="duration_hours">
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Start Time (WIB)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="time_display" readonly>
                                            <span class="input-group-text">WIB</span>
                                            <!-- Hidden field for actual datetime -->
                                            <input type="hidden" id="start_time" name="start_time"
                                                value="{{ old('start_time') }}">
                                        </div>
                                        @error('start_time')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-control-label">End Time (WIB)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="end_time_display" readonly>
                                            <span class="input-group-text">WIB</span>
                                            <!-- Hidden field for actual datetime -->
                                            <input type="hidden" id="end_time" name="end_time"
                                                value="{{ old('end_time') }}">
                                        </div>
                                        @error('end_time')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!-- Notes -->
                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <label for="notes" class="form-control-label">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <button type="submit" class="btn bg-gradient-primary mt-3 btn-lg w-100">
                                        <i class="fas fa-calendar-plus mr-1"></i> Create Reservation
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log("Form script initialized"); // Debug: script loaded

                // Customer type switching functionality
                const customerTypeSwitch = document.getElementById('customerTypeSwitch');
                const registeredCustomerSection = document.getElementById('registeredCustomerSection');
                const walkinCustomerSection = document.getElementById('walkinCustomerSection');
                const userIdField = document.getElementById('user_id');
                const walkinNameField = document.getElementById('walkin_name');
                const walkinPhoneField = document.getElementById('walkin_phone');

                // Initialize form state - call once when page loads
                function updateFormState() {
                    const isWalkin = customerTypeSwitch.checked;
                    console.log("Form state updated - Walk-in:", isWalkin); // Debug: form state

                    if (isWalkin) {
                        registeredCustomerSection.style.display = 'none';
                        walkinCustomerSection.style.display = 'block';

                        // Set required attributes
                        if (userIdField) userIdField.required = false;
                        if (walkinNameField) walkinNameField.required = true;
                        if (walkinPhoneField) walkinPhoneField.required = true;
                    } else {
                        registeredCustomerSection.style.display = 'block';
                        walkinCustomerSection.style.display = 'none';

                        // Set required attributes
                        if (userIdField) userIdField.required = true;
                        if (walkinNameField) walkinNameField.required = false;
                        if (walkinPhoneField) walkinPhoneField.required = false;
                    }
                }

                // Handle customer type switching
                if (customerTypeSwitch) {
                    customerTypeSwitch.addEventListener('change', updateFormState);
                    // Call once on page load to set initial state
                    updateFormState();
                }

                // Time calculation functionality
                const durationSelect = document.getElementById('duration_hours');
                const timeDisplay = document.getElementById('time_display');
                const endTimeDisplay = document.getElementById('end_time_display');
                const startTimeHidden = document.getElementById('start_time');
                const endTimeHidden = document.getElementById('end_time');

                // Format time for display in 24-hour format
                function formatTimeDisplay(date) {
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${hours}:${minutes}`;
                }

                // Calculate end time based on current time and duration
                function updateTimes() {
                    console.log("Updating times with duration:", durationSelect.value); // Debug: time updates

                    // Get current time
                    const now = new Date();
                    console.log("Current time:", now.toLocaleTimeString());

                    // Round minutes to nearest 15
                    const minutes = now.getMinutes();
                    const remainder = minutes % 15;
                    let roundedMinutes;

                    // If remainder is less than 7.5 minutes, round down, else round up
                    if (remainder < 7.5) {
                        roundedMinutes = minutes - remainder;
                    } else {
                        roundedMinutes = minutes + (15 - remainder);
                    }

                    console.log("Minutes rounded from", minutes, "to", roundedMinutes);

                    now.setMinutes(roundedMinutes);
                    now.setSeconds(0);
                    now.setMilliseconds(0);

                    // Calculate end time based on duration
                    const duration = parseInt(durationSelect.value);
                    const endTime = new Date(now);
                    endTime.setHours(now.getHours() + duration);

                    console.log("Start time set to:", formatTimeDisplay(now));
                    console.log("End time set to:", formatTimeDisplay(endTime));

                    // Update displays
                    timeDisplay.value = formatTimeDisplay(now);
                    endTimeDisplay.value = formatTimeDisplay(endTime);

                    // Update hidden fields
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const hours = String(now.getHours()).padStart(2, '0');
                    const mins = String(now.getMinutes()).padStart(2, '0');

                    const endHours = String(endTime.getHours()).padStart(2, '0');
                    const endMins = String(endTime.getMinutes()).padStart(2, '0');

                    const startTimeValue = `${year}-${month}-${day}T${hours}:${mins}:00`;
                    const endTimeValue = `${year}-${month}-${day}T${endHours}:${endMins}:00`;

                    console.log("Setting hidden start_time to:", startTimeValue);
                    console.log("Setting hidden end_time to:", endTimeValue);

                    startTimeHidden.value = startTimeValue;
                    endTimeHidden.value = endTimeValue;
                }

                // Update times when duration changes
                if (durationSelect) {
                    durationSelect.addEventListener('change', updateTimes);
                    // Initialize times on page load
                    updateTimes();
                }

                const form = document.querySelector('form');
                if (form) {
                    console.log("Form found and adding submit listener");

                    // Add submit event with e.preventDefault() to ensure we see logs
                    form.addEventListener('submit', function(e) {
                        // Temporarily prevent form submission to see logs
                        e.preventDefault();

                        console.log("======== FORM SUBMISSION DETECTED ========");
                        console.log("Form is being submitted now");

                        // Log form data
                        const formData = new FormData(this);
                        for (const [key, value] of formData.entries()) {
                            console.log(`${key}: ${value}`);
                        }

                        // Continue submission after a short delay to see logs
                        setTimeout(() => {
                            console.log("Continuing form submission now");
                            this.submit();
                        }, 500);
                    });
                } else {
                    console.error("Form element not found!");
                }
                console.log("script reached end" + new Date().toLocaleTimeString());
            });
        </script>
    @endpush
@endsection
