<?php

namespace App\Filament\Resources\ParkingTicketResource\Pages;

use App\Filament\Resources\ParkingTicketResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateParkingTicket extends CreateRecord
{
    protected static string $resource = ParkingTicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Vehicle Checked In')
            ->body("Ticket #{$this->record->ticket_number} created for {$this->record->vehicle_no}")
            ->success()
            ->send();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Vehicle checked in successfully';
    }
}
