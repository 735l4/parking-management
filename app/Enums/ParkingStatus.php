<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ParkingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Parked = 'parked';
    case Exited = 'exited';

    public function getLabel(): string
    {
        return match ($this) {
            self::Parked => 'Parked',
            self::Exited => 'Exited',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Parked => 'warning',
            self::Exited => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Parked => 'heroicon-o-truck',
            self::Exited => 'heroicon-o-check-circle',
        };
    }
}
