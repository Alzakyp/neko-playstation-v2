<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'amount',
        'order_id',
        'payment_status',
        'payment_date',
        'payment_method',
        'transaction_id',
        'payment_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payment_data' => 'array',
    ];

    /**
     * Get the reservation associated with this payment.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the refund associated with this payment.
     */
    public function refund()
    {
        return $this->hasOne(Refund::class);
    }

    /**
     * Check if payment has been refunded
     */
    public function isRefunded()
    {
        return $this->payment_status === 'refunded' || $this->refund()->exists();
    }

    /**
     * Format amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get payment status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge bg-warning text-dark',
            'paid' => 'badge bg-success',
            'failed' => 'badge bg-danger',
            'refunded' => 'badge bg-info',
            'expire' => 'badge bg-secondary',
        ];

        return '<span class="' . ($badges[$this->payment_status] ?? 'badge bg-secondary') . '">' .
            ucfirst($this->payment_status) . '</span>';
    }
}
