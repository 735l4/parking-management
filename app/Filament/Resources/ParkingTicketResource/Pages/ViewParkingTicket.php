<?php

namespace App\Filament\Resources\ParkingTicketResource\Pages;

use App\Filament\Resources\ParkingTicketResource;
use App\Models\ParkingTicket;
use App\Services\ThermalPrinterService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewParkingTicket extends ViewRecord
{
    protected static string $resource = ParkingTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('checkout')
                ->label('Check Out')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Check Out Vehicle')
                ->modalDescription(fn (ParkingTicket $record) => "Check out vehicle {$record->vehicle_no}? Current duration: {$record->duration_for_display}")
                ->visible(fn (ParkingTicket $record) => $record->isParked())
                ->action(function (ParkingTicket $record) {
                    $record->checkOut();

                    Notification::make()
                        ->title('Vehicle Checked Out')
                        ->body('Total: रु. '.number_format($record->total_price, 2))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'check_out', 'total_price']);
                }),

            Actions\Action::make('print')
                ->label('Print Slip')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->visible(fn (ParkingTicket $record) => $record->hasExited())
                ->url(fn (ParkingTicket $record) => app(ThermalPrinterService::class)->isBrowserPrintEnabled()
                    ? route('receipts.print', $record)
                    : null)
                ->openUrlInNewTab()
                ->action(function (ParkingTicket $record) {
                    $printer = app(ThermalPrinterService::class);

                    if ($printer->isBrowserPrintEnabled()) {
                        return;
                    }

                    try {
                        $printer->printReceipt($record);

                        Notification::make()
                            ->title('Receipt Printed')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Print Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\EditAction::make()
                ->visible(fn (ParkingTicket $record) => $record->isParked()),
        ];
    }
}
