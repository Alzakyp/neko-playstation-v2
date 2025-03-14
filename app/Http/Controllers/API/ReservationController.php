<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Playstation;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Add this import
use Illuminate\Support\Facades\Log; // Fix capitalization

class ReservationController extends Controller
{
    /**
     * Display all reservations for authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Debug information
        Log::info('Index - Auth check: ' . (Auth::check() ? 'true' : 'false'));
        Log::info('Index - User: ' . ($request->user() ? $request->user()->id : 'null'));
        Log::info('Index - Headers: ' . json_encode($request->headers->all()));

        $user = $request->user();
        Log::info('Index - User ID: ' . ($user ? $user->id : 'null'));

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'unauthorized'
            ], 401);
        }

        $query = Reservation::where('user_id', $user->id)
            ->with(['playstation', 'payment']);

        // Optional filtering by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->orderBy('created_at', 'desc')->get();
        Log::info('Index - Found reservations: ' . $reservations->count());

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                    'duration' => $reservation->duration,
                    'formatted_duration' => $reservation->formatted_duration,
                    'total_price' => $reservation->total_price,
                    'formatted_price' => $reservation->formatted_price,
                    'status' => $reservation->status,
                    'notes' => $reservation->notes,
                    'playstation' => $reservation->playstation ? [
                        'id' => $reservation->playstation->id,
                        'ps_number' => $reservation->playstation->ps_number,
                        'ps_type' => $reservation->playstation->ps_type,
                    ] : null,
                    'is_paid' => $reservation->isPaid(),
                    'payment' => $reservation->payment ? [
                        'id' => $reservation->payment->id,
                        'amount' => $reservation->payment->amount,
                        'payment_status' => $reservation->payment->payment_status,
                    ] : null,
                    'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }



    /**
     * Get active reservations (pending or confirmed)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function active(Request $request)
    {
        $user = $request->user();

        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['playstation'])
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                    'duration' => $reservation->duration,
                    'formatted_duration' => $reservation->formatted_duration,
                    'total_price' => $reservation->total_price,
                    'formatted_price' => $reservation->formatted_price,
                    'status' => $reservation->status,
                    'playstation' => [
                        'id' => $reservation->playstation->id,
                        'ps_number' => $reservation->playstation->ps_number,
                        'ps_type' => $reservation->playstation->ps_type,
                    ],
                    'is_upcoming' => $reservation->isUpcoming(),
                    'is_in_progress' => $reservation->isInProgress(),
                    'time_remaining' => $reservation->time_remaining,
                ];
            })
        ]);
    }

    /**
     * Get reservation history (completed, cancelled, or refunded)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled', 'refunded'])
            ->with(['playstation'])
            ->orderBy('end_time', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                    'duration' => $reservation->duration,
                    'formatted_duration' => $reservation->formatted_duration,
                    'total_price' => $reservation->total_price,
                    'formatted_price' => $reservation->formatted_price,
                    'status' => $reservation->status,
                    'playstation' => [
                        'id' => $reservation->playstation->id,
                        'ps_number' => $reservation->playstation->ps_number,
                        'ps_type' => $reservation->playstation->ps_type,
                    ],
                    'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * Create a new reservation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'playstation_id' => 'required|exists:playstations,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the playstation
        $playstation = Playstation::find($request->playstation_id);
        if (!$playstation->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'PlayStation is not available for reservation'
            ], 422);
        }

        // Parse dates
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);

        // Calculate duration in hours
        $duration = $startTime->diffInMinutes($endTime) / 60;

        // Check if minimum duration is 1 hour
        if ($duration < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum reservation duration is 1 hour'
            ], 422);
        }

        // Check for overlapping reservations
        $overlapping = Reservation::where('playstation_id', $request->playstation_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Start time falls within another reservation
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // End time falls within another reservation
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New reservation fully contains another reservation
                    $q->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'The selected time slot is already booked'
            ], 422);
        }

        // Calculate total price
        $totalPrice = $duration * $playstation->hourly_rate;

        // Create the reservation
        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'playstation_id' => $request->playstation_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration' => $duration,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // Load relationships
        $reservation->load(['playstation']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation created successfully',
            'data' => [
                'id' => $reservation->id,
                'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                'duration' => $reservation->duration,
                'formatted_duration' => $reservation->formatted_duration,
                'total_price' => $reservation->total_price,
                'formatted_price' => $reservation->formatted_price,
                'status' => $reservation->status,
                'notes' => $reservation->notes,
                'playstation' => [
                    'id' => $reservation->playstation->id,
                    'ps_number' => $reservation->playstation->ps_number,
                    'ps_type' => $reservation->playstation->ps_type,
                ],
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    /**
     * Display reservation details
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $user = $request->user();

        $reservation = Reservation::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['playstation', 'payment'])
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found or unauthorized'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reservation->id,
                'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                'duration' => $reservation->duration,
                'formatted_duration' => $reservation->formatted_duration,
                'total_price' => $reservation->total_price,
                'formatted_price' => $reservation->formatted_price,
                'status' => $reservation->status,
                'notes' => $reservation->notes,
                'playstation' => [
                    'id' => $reservation->playstation->id,
                    'ps_number' => $reservation->playstation->ps_number,
                    'ps_type' => $reservation->playstation->ps_type,
                    'hourly_rate' => $reservation->playstation->hourly_rate,
                ],
                'is_paid' => $reservation->isPaid(),
                'payment' => $reservation->payment ? [
                    'id' => $reservation->payment->id,
                    'amount' => $reservation->payment->amount,
                    'payment_status' => $reservation->payment->payment_status,
                ] : null,
                'is_active' => $reservation->isActive(),
                'is_upcoming' => $reservation->isUpcoming(),
                'is_in_progress' => $reservation->isInProgress(),
                'time_remaining' => $reservation->time_remaining,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Update a pending reservation
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $user = $request->user();

        $reservation = Reservation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found or unauthorized'
            ], 404);
        }

        // Only pending reservations can be updated
        if ($reservation->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending reservations can be updated'
            ], 422);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'start_time' => 'sometimes|required|date|after:now',
            'end_time' => 'sometimes|required|date|after:start_time',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update time fields if provided
        $startTime = $request->has('start_time') ? Carbon::parse($request->start_time) : $reservation->start_time;
        $endTime = $request->has('end_time') ? Carbon::parse($request->end_time) : $reservation->end_time;

        // Calculate new duration if times changed
        if ($request->has('start_time') || $request->has('end_time')) {
            $duration = $startTime->diffInMinutes($endTime) / 60;

            // Check minimum duration
            if ($duration < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum reservation duration is 1 hour'
                ], 422);
            }

            // Check for overlapping reservations
            $overlapping = Reservation::where('playstation_id', $reservation->playstation_id)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        // Start time falls within another reservation
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>', $startTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        // End time falls within another reservation
                        $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>=', $endTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        // New reservation fully contains another reservation
                        $q->where('start_time', '>=', $startTime)
                            ->where('end_time', '<=', $endTime);
                    });
                })
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected time slot is already booked'
                ], 422);
            }

            // Calculate new price
            $totalPrice = $duration * $reservation->playstation->hourly_rate;

            // Update time fields and derived values
            $reservation->start_time = $startTime;
            $reservation->end_time = $endTime;
            $reservation->duration = $duration;
            $reservation->total_price = $totalPrice;
        }

        // Update notes if provided
        if ($request->has('notes')) {
            $reservation->notes = $request->notes;
        }

        // Save reservation changes
        $reservation->save();

        // Reload with relationships
        $reservation->load(['playstation']);

        return response()->json([
            'success' => true,
            'message' => 'Reservation updated successfully',
            'data' => [
                'id' => $reservation->id,
                'start_time' => $reservation->start_time->format('Y-m-d H:i:s'),
                'end_time' => $reservation->end_time->format('Y-m-d H:i:s'),
                'duration' => $reservation->duration,
                'formatted_duration' => $reservation->formatted_duration,
                'total_price' => $reservation->total_price,
                'formatted_price' => $reservation->formatted_price,
                'status' => $reservation->status,
                'notes' => $reservation->notes,
                'playstation' => [
                    'id' => $reservation->playstation->id,
                    'ps_number' => $reservation->playstation->ps_number,
                    'ps_type' => $reservation->playstation->ps_type,
                ],
            ]
        ]);
    }

    /**
     * Cancel a reservation
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id, Request $request)
    {
        $user = $request->user();

        $reservation = Reservation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found or unauthorized'
            ], 404);
        }

        // Only pending or confirmed reservations can be cancelled
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or confirmed reservations can be cancelled'
            ], 422);
        }

        // Update status to cancelled
        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Reservation cancelled successfully'
        ]);
    }
}
