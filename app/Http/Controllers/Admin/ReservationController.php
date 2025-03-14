<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Game;
use App\Models\Playstation;
use App\Models\Reservation;
use App\Models\User;
use App\Traits\AlertMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    use AlertMessage;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $status = $request->status ?? '';
        $psType = $request->ps_type ?? '';
        $date = $request->date ?? '';

        $reservations = Reservation::query()
            ->with(['user', 'playstation'])
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->when($status !== '', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($psType !== '', function ($query) use ($psType) {
                return $query->whereHas('playstation', function ($q) use ($psType) {
                    $q->where('ps_type', $psType);
                });
            })
            ->when($date !== '', function ($query) use ($date) {
                $dateObj = Carbon::parse($date);
                return $query->whereDate('start_time', $dateObj);
            })
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        // Get all statuses and PS types for filters
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'refunded'];
        $psTypes = Playstation::select('ps_type')->distinct()->pluck('ps_type');

        return view('admin.reservation.index', compact('reservations', 'statuses', 'psTypes'));
    }

    public function active(Request $request)
    {
        $activeReservations = Reservation::whereIn('status', ['pending', 'confirmed'])
            ->with(['user', 'playstation', 'payment'])
            ->orderBy('start_time')
            ->get();

        return view('admin.reservation.active', compact('activeReservations'));
    }

    /**
     * Display reservation history (completed, cancelled, or refunded)
     */
    public function history(Request $request)
    {
        $historyReservations = Reservation::whereIn('status', ['completed', 'cancelled', 'refunded'])
            ->with(['user', 'playstation', 'payment'])
            ->orderBy('end_time', 'desc')
            ->get();

        return view('admin.reservation.history', compact('historyReservations'));
    }

    /**
     * Check availability of PlayStation units for a specific date
     */
    public function checkAvailability(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        $psType = $request->ps_type;

        $query = Playstation::where('status', '!=', 'maintenance');

        if ($psType) {
            $query->where('ps_type', $psType);
        }

        $playstations = $query->get();

        // Check availability for each PlayStation
        $availablePlaystations = $playstations->filter(function ($playstation) use ($date) {
            $dateObj = Carbon::parse($date);
            $startOfDay = (new Carbon($dateObj))->startOfDay();
            $endOfDay = (new Carbon($dateObj))->endOfDay();

            // Check for overlapping reservations
            return !Reservation::where('playstation_id', $playstation->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($startOfDay, $endOfDay) {
                    $query->whereBetween('start_time', [$startOfDay, $endOfDay])
                        ->orWhereBetween('end_time', [$startOfDay, $endOfDay])
                        ->orWhere(function ($q) use ($startOfDay, $endOfDay) {
                            $q->where('start_time', '<', $startOfDay)
                                ->where('end_time', '>', $endOfDay);
                        });
                })
                ->exists();
        });

        $psTypes = Playstation::select('ps_type')->distinct()->pluck('ps_type');
        $selectedDate = $date;

        return view('admin.reservation.availability', compact('availablePlaystations', 'psTypes', 'selectedDate', 'psType'));
    }

    /**
     * Get available time slots for a specific PlayStation on a specific date
     */
    public function availableTimeSlots(Request $request)
    {
        $request->validate([
            'playstation_id' => 'required|exists:playstations,id',
            'date' => 'required|date',
        ]);

        $playstationId = $request->playstation_id;
        $date = Carbon::parse($request->date)->format('Y-m-d');

        // Get all reservations for this PlayStation on the selected date
        $reservations = Reservation::where('playstation_id', $playstationId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        // Generate available time slots (e.g., hourly from 8 AM to 10 PM)
        $openingTime = Carbon::parse($date . ' 08:00:00');
        $closingTime = Carbon::parse($date . ' 22:00:00');

        $availableSlots = [];
        $currentSlot = clone $openingTime;

        while ($currentSlot < $closingTime) {
            $slotEnd = (clone $currentSlot)->addHour();
            $slotIsAvailable = true;

            // Check if this slot overlaps with any reservation
            foreach ($reservations as $reservation) {
                if (
                    ($currentSlot < $reservation->end_time && $slotEnd > $reservation->start_time)
                ) {
                    $slotIsAvailable = false;
                    break;
                }
            }

            if ($slotIsAvailable) {
                $availableSlots[] = [
                    'start_datetime' => $currentSlot->format('Y-m-d H:i:s'),
                    'start' => $currentSlot->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }

            $currentSlot->addHour();
        }

        $playstation = Playstation::findOrFail($playstationId);

        return view('admin.reservation.time-slots', compact('availableSlots', 'playstation', 'date'));
    }

    /**
     * Cancel a reservation
     */
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);

        // Check if reservation can be cancelled (not completed or already cancelled)
        if (in_array($reservation->status, ['completed', 'cancelled', 'refunded'])) {
            $this->errorMessage('Cannot cancel reservation that is already ' . $reservation->status);
            return redirect()->back();
        }

        $reservation->update(['status' => 'cancelled']);

        // Update PlayStation status
        $this->updatePlaystationStatus($reservation->playstation_id);

        $this->successMessage('Reservation cancelled successfully');
        return redirect()->back();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role', 'customer')->get();
        $playstations = Playstation::where('status', 'available')->get();
        $ps_types = Playstation::select('ps_type')->distinct()->pluck('ps_type');

        return view('admin.reservation.create', compact('users', 'playstations', 'ps_types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReservationRequest $request)
    {
        try {
            $data = $request->validated();

            // Calculate duration and total price
            $startTime = Carbon::parse($data['start_time']);
            $endTime = Carbon::parse($data['end_time']);

            $duration = $endTime->diffInHours($startTime);
            if ($duration < 1) {
                $this->errorMessage('Duration must be at least 1 hour');
                return redirect()->back()->withInput();
            }

            $playstation = Playstation::findOrFail($data['playstation_id']);
            $totalPrice = $duration * $playstation->hourly_rate;

            // Handle walk-in customer
            $userId = null;
            if (!empty($data['is_walkin'])) {
                // Create temporary user for walk-in customer
                $tempUser = User::create([
                    'name' => $data['walkin_name'],
                    'fullname' => $data['walkin_name'],
                    'email' => 'walkin_' . time() . '@temp.com', // Temporary email
                    'password' => bcrypt(Str::random(16)), // Random password
                    'phone' => $data['walkin_phone'],
                    'role' => 'customer',
                    'is_walkin' => true
                ]);

                $userId = $tempUser->id;
            } else {
                $userId = $data['user_id'];
            }

            // Prepare reservation data
            $reservationData = [
                'user_id' => $userId,
                'playstation_id' => $data['playstation_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration' => $duration,
                'total_price' => $totalPrice,
                'status' => 'confirmed', // Auto-confirm admin-created reservations
                'notes' => $data['notes'] ?? null,
            ];

            // Create reservation
            $reservation = Reservation::create($reservationData);

            // Update PlayStation status to in_use
            $playstation->update(['status' => 'in_use']);

            $this->successMessage('Reservation successfully created');
            return redirect()->route('admin.reservation.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error creating reservation: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['user', 'playstation', 'payment', 'payment.refund']);
        return view('admin.reservation.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        $users = User::where('role', 'customer')->get();
        $playstations = Playstation::all();
        $ps_types = Playstation::select('ps_type')->distinct()->pluck('ps_type');
        return view('admin.reservation.edit', compact('reservation', 'users', 'playstations', 'ps_types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservationRequest $request, Reservation $reservation)
    {
        try {
            $data = $request->validated();

            // Check if reservation can be updated (not completed or cancelled)
            if (in_array($reservation->status, ['completed', 'cancelled', 'refunded'])) {
                $this->errorMessage('Reservasi yang sudah ' . $reservation->status . ' tidak dapat diubah');
                return redirect()->back();
            }

            // Calculate duration and total price
            $startTime = Carbon::parse($data['start_time']);
            $endTime = Carbon::parse($data['end_time']);

            $duration = $endTime->diffInHours($startTime);
            if ($duration < 1) {
                $this->errorMessage('Durasi minimal 1 jam');
                return redirect()->back()->withInput();
            }

            $playstation = Playstation::findOrFail($data['playstation_id']);
            $totalPrice = $duration * $playstation->hourly_rate;

            // Check if PlayStation is available if different from current
            if (
                $reservation->playstation_id != $data['playstation_id'] &&
                !$this->isPlaystationAvailable($data['playstation_id'], $data['start_time'], $data['end_time'], $reservation->id)
            ) {
                $this->errorMessage('PlayStation tidak tersedia untuk jadwal ini');
                return redirect()->back()->withInput();
            }

            // Update reservation data
            $reservationData = [
                'user_id' => $data['user_id'],
                'playstation_id' => $data['playstation_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration' => $duration,
                'total_price' => $totalPrice,
                'status' => $data['status'] ?? $reservation->status,
                'notes' => $data['notes'] ?? null,
            ];

            // If PlayStation changed, update status of old and new PlayStation
            $oldPlaystationId = $reservation->playstation_id;
            $reservation->update($reservationData);

            // Update PlayStation statuses
            if ($oldPlaystationId != $data['playstation_id']) {
                $this->updatePlaystationStatus($oldPlaystationId);
                $this->updatePlaystationStatus($data['playstation_id'], $reservation->status == 'confirmed');
            } else if ($reservation->status == 'confirmed') {
                $playstation->update(['status' => 'in_use']);
            }

            $this->successMessage('Reservasi berhasil diperbarui');
            return redirect()->route('admin.reservation.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error memperbarui reservasi: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        try {
            // Check if reservation has payments
            if ($reservation->payment) {
                $this->errorMessage('Reservasi dengan pembayaran tidak dapat dihapus');
                return redirect()->back();
            }

            $playstationId = $reservation->playstation_id;


            // Delete reservation
            $reservation->delete();

            // Update PlayStation status
            $this->updatePlaystationStatus($playstationId);

            $this->successMessage('Reservasi berhasil dihapus');
            return redirect()->route('admin.reservation.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error menghapus reservasi: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update reservation status
     */
    public function updateStatus(Request $request, Reservation $reservation)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,confirmed,completed,cancelled,refunded'
            ]);

            $oldStatus = $reservation->status;
            $newStatus = $request->status;

            // Prevent updating if already completed or refunded
            if (in_array($oldStatus, ['completed', 'refunded']) && $oldStatus != $newStatus) {
                $this->errorMessage('Status reservasi yang sudah ' . $oldStatus . ' tidak dapat diubah');
                return redirect()->back();
            }

            $reservation->update(['status' => $newStatus]);

            // Update PlayStation status based on reservation status
            if ($oldStatus != $newStatus) {
                if ($newStatus == 'confirmed') {
                    // Set PlayStation to in_use when reservation is confirmed
                    Playstation::where('id', $reservation->playstation_id)
                        ->update(['status' => 'in_use']);
                } else if (in_array($newStatus, ['completed', 'cancelled']) && $oldStatus == 'confirmed') {
                    // When reservation completed or cancelled, update PlayStation availability
                    $this->updatePlaystationStatus($reservation->playstation_id);
                }

                // If has payment and status is completed, mark payment as paid
                if ($newStatus == 'completed' && $reservation->payment) {
                    $reservation->payment->update(['payment_status' => 'paid']);
                }
            }

            $this->successMessage('Status reservasi berhasil diperbarui');
            return redirect()->back();
        } catch (\Exception $e) {
            $this->errorMessage('Error memperbarui status: ' . $e->getMessage());
            return redirect()->back();
        }
    }


    /**
     * Check if PlayStation is available for the time slot
     */
    private function isPlaystationAvailable($playstationId, $startTime, $endTime, $excludeReservationId = null)
    {
        $playstation = Playstation::find($playstationId);

        if (!$playstation || $playstation->status == 'maintenance') {
            return false;
        }

        // Check for overlapping reservations
        $query = Reservation::where('playstation_id', $playstationId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($startTime, $endTime) {
                // Start time is between existing reservation
                $query->whereBetween('start_time', [$startTime, $endTime])
                    // End time is between existing reservation
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    // Reservation fully contains the requested period
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        // Exclude current reservation when editing
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    /**
     * Update PlayStation status based on active reservations
     */
    private function updatePlaystationStatus($playstationId, $forceInUse = false)
    {
        $playstation = Playstation::find($playstationId);

        if (!$playstation || $playstation->status == 'maintenance') {
            return;
        }

        // If forcing in_use status (for confirmed reservations)
        if ($forceInUse) {
            $playstation->update(['status' => 'in_use']);
            return;
        }

        // Check if there are any active confirmed reservations for this PlayStation
        $activeReservations = Reservation::where('playstation_id', $playstationId)
            ->where('status', 'confirmed')
            ->where(function ($query) {
                $now = Carbon::now();
                $query->where('start_time', '<=', $now)
                    ->where('end_time', '>=', $now);
            })
            ->exists();

        $playstation->update(['status' => $activeReservations ? 'in_use' : 'available']);
    }

    /**
     * Get color for reservation status
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'pending':
                return '#FFC107'; // Warning yellow
            case 'confirmed':
                return '#0dcaf0'; // Info blue
            case 'completed':
                return '#198754'; // Success green
            case 'cancelled':
                return '#dc3545'; // Danger red
            case 'refunded':
                return '#6c757d'; // Secondary gray
            default:
                return '#6c757d'; // Secondary gray
        }
    }
}
