<?php

namespace App\Filament\Resources\ParkingTicketResource\Pages;

use App\Filament\Resources\ParkingTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParkingTicket extends EditRecord
{
    protected static string $resource = ParkingTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
