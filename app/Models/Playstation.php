<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playstation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ps_number',
        'ps_type',
        'status',
        'hourly_rate',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Get all games available for this PlayStation.
     */
    public function games()
    {
        return $this->belongsToMany(Game::class, 'ps_games');
    }

    /**
     * Get all reservations for this PlayStation.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get formatted hourly rate
     */
    public function getFormattedRateAttribute()
    {
        return 'Rp ' . number_format($this->hourly_rate, 0, ',', '.');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'available' => 'badge bg-success',
            'in_use' => 'badge bg-warning text-dark',
            'maintenance' => 'badge bg-danger',
        ];

        return '<span class="' . ($badges[$this->status] ?? 'badge bg-secondary') . '">' .
            ucfirst($this->status) . '</span>';
    }

    /**
     * Check if PlayStation is available
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Scope a query to only include available PlayStations.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
