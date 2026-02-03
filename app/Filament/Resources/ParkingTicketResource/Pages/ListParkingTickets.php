<?php

namespace App\Filament\Resources\ParkingTicketResource\Pages;

use App\Enums\ParkingStatus;
use App\Filament\Resources\ParkingTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListParkingTickets extends ListRecords
{
    protected static string $resource = ParkingTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Check In Vehicle')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Tickets')
                ->badge(fn () => $this->getModel()::count()),

            'parked' => Tab::make('Currently Parked')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ParkingStatus::Parked))
                ->badge(fn () => $this->getModel()::parked()->count())
                ->badgeColor('warning'),

            'exited' => Tab::make('Exited')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ParkingStatus::Exited))
                ->badge(fn () => $this->getModel()::exited()->count())
                ->badgeColor('success'),

            'today' => Tab::make("Today's Tickets")
                ->modifyQueryUsing(fn (Builder $query) => $query->today())
                ->badge(fn () => $this->getModel()::today()->count())
                ->badgeColor('info'),
        ];
    }
}
