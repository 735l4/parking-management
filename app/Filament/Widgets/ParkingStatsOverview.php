<?php

namespace App\Filament\Widgets;

use App\Enums\ParkingStatus;
use App\Models\ParkingTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParkingStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenue = ParkingTicket::whereDate('check_out', today())
            ->where('status', ParkingStatus::Exited)
            ->sum('total_price');

        $activeVehicles = ParkingTicket::parked()->count();

        $todayCheckIns = ParkingTicket::today()->count();

        $todayCheckOuts = ParkingTicket::whereDate('check_out', today())
            ->where('status', ParkingStatus::Exited)
            ->count();

        $yesterdayRevenue = ParkingTicket::whereDate('check_out', today()->subDay())
            ->where('status', ParkingStatus::Exited)
            ->sum('total_price');

        $revenueTrend = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : 0;

        return [
            Stat::make("Today's Revenue", 'रु. '.number_format($todayRevenue, 2))
                ->description($revenueTrend >= 0 ? "+{$revenueTrend}% from yesterday" : "{$revenueTrend}% from yesterday")
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),

            Stat::make('Active Vehicles', $activeVehicles)
                ->description('Currently parked')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning'),

            Stat::make("Today's Check-Ins", $todayCheckIns)
                ->description('Vehicles parked today')
                ->descriptionIcon('heroicon-m-arrow-down-on-square')
                ->color('info'),

            Stat::make("Today's Check-Outs", $todayCheckOuts)
                ->description('Vehicles exited today')
                ->descriptionIcon('heroicon-m-arrow-up-on-square')
                ->color('success'),
        ];
    }

    /**
     * @return array<int>
     */
    protected function getRevenueChart(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $revenue = ParkingTicket::whereDate('check_out', $date)
                ->where('status', ParkingStatus::Exited)
                ->sum('total_price');

            $data[] = (int) $revenue;
        }

        return $data;
    }
}
