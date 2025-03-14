<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'playstation_id',
        'start_time',
        'end_time',
        'duration',
        'total_price',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the playstation associated with the reservation.
     */
    public function playstation()
    {
        return $this->belongsTo(Playstation::class);
    }

    /**
     * Get the games attached to this reservation.
     */

    /**
     * Get the payment record associated with this reservation.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the refund associated with this reservation.
     */
    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    /**
     * Check if reservation is active (confirmed and not ended yet)
     */
    public function isActive()
    {
        return $this->status === 'confirmed' && $this->end_time > Carbon::now();
    }

    /**
     * Check if reservation is upcoming (confirmed and hasn't started yet)
     */
    public function isUpcoming()
    {
        return $this->status === 'confirmed' && $this->start_time > Carbon::now();
    }

    /**
     * Check if reservation is in progress
     */
    public function isInProgress()
    {
        $now = Carbon::now();
        return $this->status === 'confirmed' &&
            $this->start_time <= $now &&
            $this->end_time >= $now;
    }

    /**
     * Check if reservation has payment
     */
    public function isPaid()
    {
        return $this->payment && $this->payment->payment_status === 'paid';
    }

    /**
     * Get formatted total price
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge bg-warning text-dark',
            'confirmed' => 'badge bg-info',
            'completed' => 'badge bg-success',
            'cancelled' => 'badge bg-danger',
            'refunded' => 'badge bg-secondary',
        ];

        return '<span class="' . ($badges[$this->status] ?? 'badge bg-secondary') . '">' .
            ucfirst($this->status) . '</span>';
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        return $this->duration . ' ' . ($this->duration > 1 ? 'hours' : 'hour');
    }

    /**
     * Get time remaining for reservation
     */
    public function getTimeRemainingAttribute()
    {
        if ($this->status !== 'confirmed' || !$this->isInProgress()) {
            return null;
        }

        $now = Carbon::now();
        $minutesLeft = $now->diffInMinutes($this->end_time);
        $hoursLeft = floor($minutesLeft / 60);
        $mins = $minutesLeft % 60;

        return $hoursLeft . 'h ' . $mins . 'm';
    }
}
