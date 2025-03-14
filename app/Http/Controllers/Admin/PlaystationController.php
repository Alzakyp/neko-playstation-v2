<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaystationRequest;
use App\Models\Game;
use App\Models\Playstation;
use App\Models\Reservation;
use App\Traits\AlertMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlaystationController extends Controller
{
    use AlertMessage;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $status = $request->status ?? '';
        $ps_type = $request->ps_type ?? '';

        $playstations = Playstation::query()
            ->when($search, function ($query) use ($search) {
                return $query->where('ps_number', 'like', "%{$search}%")
                    ->orWhere('ps_type', 'like', "%{$search}%");
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($ps_type, function ($query) use ($ps_type) {
                return $query->where('ps_type', $ps_type);
            })
            ->orderBy('ps_type')
            ->orderBy('ps_number')
            ->paginate(10);

        // Get unique statuses and PS types for filter dropdowns
        $statuses = ['available', 'in_use', 'maintenance'];
        $ps_types = Playstation::select('ps_type')->distinct()->pluck('ps_type');

        return view('admin.playstation.index', compact('playstations', 'statuses', 'ps_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $games = Game::orderBy('title')->get();
        return view('admin.playstation.create', compact('games'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlaystationRequest $request)
    {
        try {
            $data = $request->validated();

            // Remove game_ids from data before creating playstation
            $gameIds = $data['game_ids'] ?? [];
            unset($data['game_ids']);

            // Create PlayStation
            $playstation = Playstation::create($data);

            // Attach games
            if (!empty($gameIds)) {
                $playstation->games()->attach($gameIds);
            }

            $this->successMessage('PlayStation berhasil ditambahkan');
            return redirect()->route('admin.playstation.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error menambahkan PlayStation: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Playstation $playstation)
    {
        $playstation->load(['games', 'reservations' => function ($query) {
            $query->latest()->take(5);
        }]);

        return view('admin.playstation.show', compact('playstation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Playstation $playstation)
    {
        $games = Game::orderBy('title')->get();
        $selectedGames = $playstation->games->pluck('id')->toArray();

        return view('admin.playstation.edit', compact('playstation', 'games', 'selectedGames'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlaystationRequest $request, Playstation $playstation)
    {
        try {
            $data = $request->validated();

            // Remove game_ids from data before updating playstation
            $gameIds = $data['game_ids'] ?? [];
            unset($data['game_ids']);

            // Update playstation
            $playstation->update($data);

            // Sync games
            $playstation->games()->sync($gameIds);

            $this->successMessage('PlayStation berhasil diperbarui');
            return redirect()->route('admin.playstation.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error memperbarui PlayStation: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Playstation $playstation)
    {
        try {
            // Check if playstation has active reservations
            $hasActiveReservations = $playstation->reservations()
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->exists();

            if ($hasActiveReservations) {
                $this->errorMessage('PlayStation tidak dapat dihapus karena memiliki reservasi aktif');
                return redirect()->back();
            }

            // Detach all games first
            $playstation->games()->detach();

            $playstation->delete();

            $this->successMessage('PlayStation berhasil dihapus');
            return redirect()->route('admin.playstation.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error menghapus PlayStation: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Update status of PlayStation
     */
    public function updateStatus(Request $request, Playstation $playstation)
    {
        try {
            $request->validate([
                'status' => 'required|in:available,in_use,maintenance',
            ]);

            $playstation->update(['status' => $request->status]);

            $this->successMessage('Status PlayStation berhasil diperbarui');
            return redirect()->back();
        } catch (\Exception $e) {
            $this->errorMessage('Error memperbarui status PlayStation: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Show availability dashboard for hourly booking
     */
    public function availability()
    {
        $playstations = Playstation::where('status', '!=', 'maintenance')
            ->orderBy('ps_type')
            ->orderBy('ps_number')
            ->get();

        // Get pending reservations that need action
        $pendingReservations = Reservation::where('status', 'pending')
            ->with(['user', 'playstation'])
            ->orderBy('start_time')
            ->paginate(10);

        return view('admin.playstation.availability', compact('playstations', 'pendingReservations'));
    }

    /**
     * Get available time slots for a specific PlayStation and date (AJAX endpoint)
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'playstation_id' => 'required|exists:playstations,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $playstationId = $request->input('playstation_id');
        $date = $request->input('date');

        $playstation = Playstation::findOrFail($playstationId);

        // Check if PlayStation is in maintenance
        if ($playstation->status === 'maintenance') {
            return response()->json([
                'available_slots' => [],
                'message' => 'PlayStation sedang dalam maintenance'
            ]);
        }

        // Define operating hours (10:00 - 22:00)
        $operatingStartHour = 10;
        $operatingEndHour = 22;

        // Get all existing reservations for this PlayStation on the selected date
        $existingReservations = Reservation::where('playstation_id', $playstationId)
            ->whereDate('start_time', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['id', 'start_time', 'end_time', 'status'])
            ->toArray();

        // Generate all possible hourly slots
        $availableSlots = [];
        $bookedSlots = [];
        $selectedDate = Carbon::parse($date);
        $currentTime = Carbon::now();

        for ($hour = $operatingStartHour; $hour < $operatingEndHour; $hour++) {
            $slotStart = Carbon::parse($date)->setHour($hour)->setMinute(0)->setSecond(0);
            $slotEnd = Carbon::parse($date)->setHour($hour + 1)->setMinute(0)->setSecond(0);

            // Skip past slots for today
            if ($selectedDate->isToday() && $slotStart->isPast()) {
                continue;
            }

            // Check if slot overlaps with any existing reservation
            $isBooked = false;
            $reservationId = null;
            $reservationStatus = null;

            foreach ($existingReservations as $reservation) {
                $reservationStart = Carbon::parse($reservation['start_time']);
                $reservationEnd = Carbon::parse($reservation['end_time']);

                if (
                    $slotStart->between($reservationStart, $reservationEnd) ||
                    $slotEnd->between($reservationStart, $reservationEnd) ||
                    ($slotStart->lte($reservationStart) && $slotEnd->gte($reservationEnd))
                ) {
                    $isBooked = true;
                    $reservationId = $reservation['id'];
                    $reservationStatus = $reservation['status'];
                    break;
                }
            }

            $timeSlot = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'start_datetime' => $slotStart->format('Y-m-d H:i:s'),
                'end_datetime' => $slotEnd->format('Y-m-d H:i:s'),
            ];

            if ($isBooked) {
                $timeSlot['reservation_id'] = $reservationId;
                $timeSlot['status'] = $reservationStatus;
                $bookedSlots[] = $timeSlot;
            } else {
                $availableSlots[] = $timeSlot;
            }
        }

        return response()->json([
            'available_slots' => $availableSlots,
            'booked_slots' => $bookedSlots,
            'playstation' => [
                'id' => $playstation->id,
                'ps_number' => $playstation->ps_number,
                'ps_type' => $playstation->ps_type,
                'hourly_rate' => $playstation->hourly_rate,
                'formatted_rate' => $playstation->formatted_rate
            ]
        ]);
    }

    /**
     * Show calendar view for a PlayStation's reservations
     */
    public function calendar(Request $request)
    {
        $playstations = Playstation::all();
        $selectedPlaystationId = $request->input('playstation_id');

        return view('admin.playstation.calendar', compact('playstations', 'selectedPlaystationId'));
    }

    /**
     * Get calendar events for reservations (AJAX endpoint)
     */
    public function getCalendarEvents(Request $request)
    {
        // Default view is for current month
        $start = $request->input('start', Carbon::now()->startOfMonth()->toDateString());
        $end = $request->input('end', Carbon::now()->endOfMonth()->toDateString());

        // Get PlayStation filter if any
        $playstationId = $request->input('playstation_id');

        // Get all relevant reservations
        $query = Reservation::whereBetween('start_time', [$start, $end])
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->with(['user', 'playstation']);

        // Filter by PlayStation if specified
        if ($playstationId && $playstationId !== 'all') {
            $query->where('playstation_id', $playstationId);
        }

        $reservations = $query->get();

        // Format for FullCalendar
        $events = [];

        foreach ($reservations as $reservation) {
            $color = '#6c757d'; // default gray

            // Set color based on status
            switch ($reservation->status) {
                case 'pending':
                    $color = '#ffc107'; // warning yellow
                    break;
                case 'confirmed':
                    $color = '#0dcaf0'; // info blue
                    break;
                case 'completed':
                    $color = '#198754'; // success green
                    break;
            }

            $events[] = [
                'id' => $reservation->id,
                'title' => "{$reservation->playstation->ps_number} - {$reservation->user->name}",
                'start' => $reservation->start_time->format('Y-m-d\TH:i:s'),
                'end' => $reservation->end_time->format('Y-m-d\TH:i:s'),
                'url' => route('admin.reservation.show', $reservation->id),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'playstation' => $reservation->playstation->ps_number,
                    'customer' => $reservation->user->name,
                    'status' => $reservation->status,
                    'price' => $reservation->formatted_price ?? number_format($reservation->total_price, 0, ',', '.'),
                    'duration' => $reservation->end_time->diffInHours($reservation->start_time) . ' jam'
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Check if PlayStation is available for the given time range
     * Used by Reservation system
     */
    public function isPlaystationAvailable($playstationId, $startTime, $endTime, $excludeReservationId = null)
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
     * Display daily availability report view
     */
    public function dailyReport(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // Get all PlayStations
        $playstations = Playstation::all();

        // Get all reservations for the selected date
        $reservations = Reservation::whereDate('start_time', $date)
            ->orWhere(function ($query) use ($date) {
                $query->whereDate('end_time', $date);
            })
            ->whereIn('status', ['confirmed', 'completed'])
            ->with('playstation', 'user')
            ->get();

        // Operating hours (10:00 - 22:00)
        $operatingStartHour = 10;
        $operatingEndHour = 22;

        // Prepare hourly slots for each PlayStation
        $hourlyData = [];

        foreach ($playstations as $ps) {
            $psData = [
                'id' => $ps->id,
                'ps_number' => $ps->ps_number,
                'ps_type' => $ps->ps_type,
                'hours' => []
            ];

            for ($hour = $operatingStartHour; $hour < $operatingEndHour; $hour++) {
                $slotStart = Carbon::parse($date)->setHour($hour)->setMinute(0)->setSecond(0);
                $slotEnd = Carbon::parse($date)->setHour($hour + 1)->setMinute(0)->setSecond(0);

                // Find reservation for this hour if any
                $reservation = $reservations->first(function ($reservation) use ($ps, $slotStart, $slotEnd) {
                    // Check if this PlayStation is booked during this hour
                    if ($reservation->playstation_id != $ps->id) {
                        return false;
                    }

                    $reservationStart = $reservation->start_time;
                    $reservationEnd = $reservation->end_time;

                    return $slotStart->between($reservationStart, $reservationEnd) ||
                        $slotEnd->between($reservationStart, $reservationEnd) ||
                        ($slotStart->lte($reservationStart) && $slotEnd->gte($reservationEnd));
                });

                $psData['hours'][$hour] = [
                    'time' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'is_booked' => !is_null($reservation),
                    'reservation' => $reservation ? [
                        'id' => $reservation->id,
                        'user' => $reservation->user->name,
                        'status' => $reservation->status
                    ] : null
                ];
            }

            $hourlyData[] = $psData;
        }

        return view('admin.playstation.daily-report', compact('hourlyData', 'selectedDate', 'playstations'));
    }
}
