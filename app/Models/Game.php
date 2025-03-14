<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'genre',
        'ps_type',
        'description',
        'image_url',
    ];

    /**
     * Get the PlayStation consoles that have this game.
     */
    public function playstations()
    {
        return $this->belongsToMany(Playstation::class, 'ps_games');
    }

    /**
     * Get full URL for image
     *
     * @return string|null
     */
    public function getImageUrlAttribute($value)
    {
        return $value ? asset('storage/games/'.$value) : null;
    }
}
