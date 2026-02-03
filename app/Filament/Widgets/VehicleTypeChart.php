<?php

namespace App\Filament\Widgets;

use App\Models\VehicleType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class VehicleTypeChart extends ChartWidget
{
    protected ?string $heading = 'Vehicles by Type';

    protected static ?int $sort = 3;

    public ?string $filter = '7';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '15' => 'Last 15 days',
            '30' => 'Last 30 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $startDate = Carbon::today()->subDays($days);

        $vehicleTypes = VehicleType::withCount([
            'parkingTickets' => function ($query) use ($startDate) {
                $query->where('check_in', '>=', $startDate);
            },
        ])->get();

        $labels = $vehicleTypes->pluck('name')->toArray();
        $data = $vehicleTypes->pluck('parking_tickets_count')->toArray();

        $colors = [
            'rgba(251, 191, 36, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Vehicles',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_VehicleTypeChart') ?? false;
    }
}
