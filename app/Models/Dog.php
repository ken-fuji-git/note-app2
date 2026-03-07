<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dog extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'photo_path',
        'gender',
        'age',
        'breed',
        'height',
        'personality',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function journeys()
    {
        return $this->hasMany(Journey::class);
    }

    public function walkingSpeedKmPerHour(): float
    {
        if ($this->height <= 25) {
            return 2.5;
        } elseif ($this->height <= 40) {
            return 3.0;
        } elseif ($this->height <= 55) {
            return 3.5;
        } else {
            return 4.0;
        }
    }

    public function estimateDays(float $distanceKm): int
    {
        $speed = $this->walkingSpeedKmPerHour();
        $activeHoursPerDay = 7;
        $kmPerDay = $speed * $activeHoursPerDay;
        $baseDays = (int) ceil($distanceKm / $kmPerDay);
        $eventDays = (int) ceil($baseDays * 0.2);

        return max(3, min($baseDays + $eventDays, 25));
    }
}
