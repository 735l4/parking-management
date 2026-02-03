<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleType extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'hourly_rate',
        'minimum_charge',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'minimum_charge' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get parking tickets for this vehicle type.
     */
    public function parkingTickets(): HasMany
    {
        return $this->hasMany(ParkingTicket::class);
    }

    /**
     * Scope for active vehicle types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
