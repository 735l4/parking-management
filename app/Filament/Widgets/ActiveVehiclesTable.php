<?php

namespace App\Filament\Widgets;

use App\Enums\ParkingStatus;
use App\Models\ParkingTicket;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveVehiclesTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Currently Parked Vehicles';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ParkingTicket::query()
                    ->where('status', ParkingStatus::Parked)
                    ->with(['vehicleType'])
                    ->orderBy('check_in', 'desc')
            )
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('vehicle_no')
                    ->label('Vehicle No.')
                    ->searchable(),

                TextColumn::make('vehicleType.name')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('check_in')
                    ->label('Parked Since')
                    ->dateTime('M d, H:i')
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(fn (ParkingTicket $record) => $record->duration_for_display)
                    ->badge()
                    ->color('warning'),

                TextColumn::make('estimated_price')
                    ->label('Est. Price')
                    ->state(fn (ParkingTicket $record) => 'रु. '.number_format($record->calculatePrice(), 2)),
            ])
            ->actions([
                Action::make('checkout')
                    ->label('Check Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (ParkingTicket $record) {
                        $record->checkOut();
                    }),
            ])
            ->poll('30s');
    }
}
