<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_id',
        'payment_id',
        'amount',
        'reason',
        'refund_percentage',
        'request_date',
        'status',
        'admin_id',
        'processed_date',
        'notes',
        'refund_id',
        'refund_response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'refund_percentage' => 'integer',
        'request_date' => 'datetime',
        'processed_date' => 'datetime',
        'refund_response' => 'array',
    ];

    /**
     * Get the payment associated with this refund.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the reservation associated with this refund.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the admin who processed this refund.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge bg-warning text-dark',
            'approved' => 'badge bg-success',
            'rejected' => 'badge bg-danger',
            'processed' => 'badge bg-info',
        ];

        return '<span class="' . ($badges[$this->status] ?? 'badge bg-secondary') . '">' .
            ucfirst($this->status) . '</span>';
    }
}
