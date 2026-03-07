<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journey extends Model
{
    protected $fillable = [
        'dog_id',
        'wish',
        'departure_lat',
        'departure_lng',
        'departure_name',
        'distance_km',
        'estimated_days',
        'departed_at',
        'story',
        'route_places',
    ];

    protected function casts(): array
    {
        return [
            'departure_lat' => 'decimal:7',
            'departure_lng' => 'decimal:7',
            'distance_km' => 'decimal:1',
            'departed_at' => 'date',
            'story' => 'array',
            'route_places' => 'array',
        ];
    }

    public function dog()
    {
        return $this->belongsTo(Dog::class);
    }
}
