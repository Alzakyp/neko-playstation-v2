<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Playstation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlaystationController extends Controller
{
    /**
     * Get all playstations with optional filtering
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Playstation::query();

        // Filter by PS type
        if ($request->has('ps_type')) {
            $query->where('ps_type', $request->ps_type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $playstations = $query->get();

        return response()->json([
            'success' => true,
            'data' => $playstations->map(function ($ps) {
                return [
                    'id' => $ps->id,
                    'ps_number' => $ps->ps_number,
                    'ps_type' => $ps->ps_type,
                    'status' => $ps->status,
                    'hourly_rate' => $ps->hourly_rate,
                    'formatted_rate' => $ps->formatted_rate,
                    'description' => $ps->description,
                ];
            })
        ]);
    }

    /**
     * Get available playstations for reservation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        // Get base query for available playstations
        $query = Playstation::where('status', 'available');

        // Filter by PS type
        if ($request->has('ps_type')) {
            $query->where('ps_type', $request->ps_type);
        }

        // Filter by date and time for available slots
        if ($request->has('date') && $request->has('time')) {
            $requestDate = Carbon::parse($request->date . ' ' . $request->time);

            // We need to exclude playstations that have reservations at the requested time
            $bookedPlaystationIds = Reservation::whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($requestDate) {
                    // Find reservations that overlap with the requested time
                    $query->where(function ($q) use ($requestDate) {
                        $q->where('start_time', '<=', $requestDate)
                            ->where('end_time', '>', $requestDate);
                    });
                })
                ->pluck('playstation_id');

            // Exclude booked playstations
            if ($bookedPlaystationIds->count() > 0) {
                $query->whereNotIn('id', $bookedPlaystationIds);
            }
        }

        $playstations = $query->get();

        return response()->json([
            'success' => true,
            'data' => $playstations->map(function ($ps) {
                return [
                    'id' => $ps->id,
                    'ps_number' => $ps->ps_number,
                    'ps_type' => $ps->ps_type,
                    'hourly_rate' => $ps->hourly_rate,
                    'formatted_rate' => $ps->formatted_rate,
                    'description' => $ps->description,
                ];
            })
        ]);
    }

    /**
     * Get details for a specific playstation
     *
     * @param Playstation $playstation
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Playstation $playstation)
    {
        // Load available games for this playstation
        $playstation->load('games');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $playstation->id,
                'ps_number' => $playstation->ps_number,
                'ps_type' => $playstation->ps_type,
                'status' => $playstation->status,
                'hourly_rate' => $playstation->hourly_rate,
                'formatted_rate' => $playstation->formatted_rate,
                'description' => $playstation->description,
                'games' => $playstation->games->map(function ($game) {
                    return [
                        'id' => $game->id,
                        'title' => $game->title,
                        'genre' => $game->genre,
                        'ps_type' => $game->ps_type,
                        'image_url' => $game->image_url,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get schedule of a playstation for reservation planning
     *
     * @param Playstation $playstation
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schedule(Playstation $playstation, Request $request)
    {
        // Get date range, default to current week
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : $startDate->copy()->addDays(7)->endOfDay();

        // Get all reservations for this playstation in date range
        $reservations = Reservation::where('playstation_id', $playstation->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<=', $endDate)
            ->where('end_time', '>=', $startDate)
            ->get();

        // Format reservations as time slots
        $bookedSlots = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                'status' => $reservation->status
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'playstation' => [
                    'id' => $playstation->id,
                    'ps_number' => $playstation->ps_number,
                    'ps_type' => $playstation->ps_type,
                    'hourly_rate' => $playstation->hourly_rate,
                ],
                'date_range' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                ],
                'booked_slots' => $bookedSlots,
            ]
        ]);
    }

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

        // Define operating hours (e.g., 10:00 - 22:00)
        $operatingStartHour = 10;
        $operatingEndHour = 22;

        // Get all existing reservations for this PlayStation on the selected date
        $existingReservations = Reservation::where('playstation_id', $playstationId)
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'cancelled')
            ->get(['start_time', 'end_time'])
            ->toArray();

        // Generate all possible hourly slots
        $availableSlots = [];
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
            $isAvailable = true;
            foreach ($existingReservations as $reservation) {
                $reservationStart = Carbon::parse($reservation['start_time']);
                $reservationEnd = Carbon::parse($reservation['end_time']);

                if (
                    $slotStart->between($reservationStart, $reservationEnd) ||
                    $slotEnd->between($reservationStart, $reservationEnd) ||
                    ($slotStart->lte($reservationStart) && $slotEnd->gte($reservationEnd))
                ) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableSlots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'start_datetime' => $slotStart->format('Y-m-d H:i:s'),
                    'end_datetime' => $slotEnd->format('Y-m-d H:i:s'),
                ];
            }
        }

        return response()->json([
            'available_slots' => $availableSlots
        ]);
    }
}
