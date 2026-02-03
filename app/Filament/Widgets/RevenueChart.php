<?php

namespace App\Filament\Widgets;

use App\Enums\ParkingStatus;
use App\Models\ParkingTicket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Overview';

    protected static ?int $sort = 2;

    public ?string $filter = '15';

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
        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');

            $revenue = ParkingTicket::whereDate('check_out', $date)
                ->where('status', ParkingStatus::Exited)
                ->sum('total_price');

            $data[] = (float) $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Daily Revenue (रु.)',
                    'data' => $data,
                    'fill' => true,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                    'borderColor' => 'rgb(251, 191, 36)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_RevenueChart') ?? false;
    }
}
