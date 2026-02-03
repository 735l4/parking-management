<?php

namespace App\Models;

use App\Enums\ParkingStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingTicket extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'vehicle_no',
        'vehicle_type_id',
        'phone_number',
        'check_in',
        'check_out',
        'total_price',
        'status',
        'notes',
        'checked_in_by',
        'checked_out_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'total_price' => 'decimal:2',
            'status' => ParkingStatus::class,
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ParkingTicket $ticket) {
            if (empty($ticket->check_in)) {
                $ticket->check_in = now();
            }
            if (empty($ticket->ticket_number)) {
                // Use the check_in date for ticket numbering
                $checkInDate = $ticket->check_in instanceof Carbon
                    ? $ticket->check_in
                    : Carbon::parse($ticket->check_in);
                $ticket->ticket_number = self::generateTicketNumber($checkInDate);
            }
            if (empty($ticket->status)) {
                $ticket->status = ParkingStatus::Parked;
            }
            if (empty($ticket->checked_in_by) && auth()->check()) {
                $ticket->checked_in_by = auth()->id();
            }
        });
    }

    /**
     * Generate a unique ticket number.
     */
    public static function generateTicketNumber(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefix = 'PKT';
        $dateStr = $date->format('Ymd');

        $lastTicket = self::where('ticket_number', 'like', "{$prefix}-{$dateStr}-%")
            ->orderByDesc('ticket_number')
            ->first();

        $sequence = $lastTicket
            ? (int) substr($lastTicket->ticket_number, -4) + 1
            : 1;

        return sprintf('%s-%s-%04d', $prefix, $dateStr, $sequence);
    }

    /**
     * Get the vehicle type.
     */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    /**
     * Get the user who checked in the vehicle.
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Get the user who checked out the vehicle.
     */
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * Scope for parked vehicles.
     */
    public function scopeParked($query)
    {
        return $query->where('status', ParkingStatus::Parked);
    }

    /**
     * Scope for exited vehicles.
     */
    public function scopeExited($query)
    {
        return $query->where('status', ParkingStatus::Exited);
    }

    /**
     * Scope for today's tickets.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('check_in', today());
    }

    /**
     * Calculate parking duration in hours.
     */
    public function getDurationInHoursAttribute(): float
    {
        $endTime = $this->check_out ?? now();

        return round($this->check_in->diffInMinutes($endTime) / 60, 2);
    }

    /**
     * Calculate parking duration for display.
     */
    public function getDurationForDisplayAttribute(): string
    {
        $endTime = $this->check_out ?? now();
        $diff = $this->check_in->diff($endTime);

        $parts = [];
        if ($diff->days > 0) {
            $parts[] = $diff->days.'d';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h.'h';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i.'m';
        }

        return implode(' ', $parts) ?: '0m';
    }

    /**
     * Calculate total price based on duration and rates.
     */
    public function calculatePrice(): float
    {
        $vehicleType = $this->vehicleType;

        if (! $vehicleType) {
            return 0;
        }

        $durationHours = $this->duration_in_hours;

        // If less than 1 hour, apply minimum charge
        if ($durationHours < 1) {
            return (float) $vehicleType->minimum_charge;
        }

        // Standard hourly rate calculation (round up to nearest hour)
        $totalPrice = ceil($durationHours) * (float) $vehicleType->hourly_rate;

        // Ensure minimum charge
        return max($totalPrice, (float) $vehicleType->minimum_charge);
    }

    /**
     * Perform check-out operation.
     */
    public function checkOut(): self
    {
        $this->check_out = now();
        $this->total_price = $this->calculatePrice();
        $this->status = ParkingStatus::Exited;

        if (auth()->check()) {
            $this->checked_out_by = auth()->id();
        }

        $this->save();

        return $this;
    }

    /**
     * Check if ticket is currently parked.
     */
    public function isParked(): bool
    {
        return $this->status === ParkingStatus::Parked;
    }

    /**
     * Check if ticket has exited.
     */
    public function hasExited(): bool
    {
        return $this->status === ParkingStatus::Exited;
    }
}
