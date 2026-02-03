<?php

namespace App\Filament\Resources;

use App\Enums\ParkingStatus;
use App\Filament\Resources\ParkingTicketResource\Pages;
use App\Helpers\NepaliHelper;
use App\Models\ParkingTicket;
use App\Models\VehicleType;
use App\Services\ThermalPrinterService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ParkingTicketResource extends Resource
{
    protected static ?string $model = ParkingTicket::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = 'Parking';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'ticket_number';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::parked()->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vehicle Information')
                    ->schema([
                        TextInput::make('vehicle_no')
                            ->label('Vehicle Number')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('e.g., BA 1 PA 1234')
                            ->autocomplete(false),

                        Select::make('vehicle_type_id')
                            ->label('Vehicle Type')
                            ->relationship('vehicleType', 'name', fn (Builder $query) => $query->active())
                            ->required()
                            ->preload()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $vehicleType = VehicleType::find($state);
                                    if ($vehicleType) {
                                        $set('hourly_rate_display', 'रु. '.number_format($vehicleType->hourly_rate, 2));
                                    }
                                }
                            }),

                        TextInput::make('hourly_rate_display')
                            ->label('Hourly Rate')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(10)
                            ->minLength(10)
                            ->placeholder('98XXXXXXXX')
                            ->rules(['nullable', 'regex:/^(97|98|96)\d{8}$/'])
                            ->validationMessages([
                                'regex' => 'Please enter a valid 10-digit Nepali phone number starting with 97, 98, or 96.',
                            ]),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Ticket Information')
                    ->schema([
                        TextInput::make('ticket_number')
                            ->label('Ticket Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        TextInput::make('check_in')
                            ->label('Check-In Time')
                            ->disabled()
                            ->visibleOn('edit'),

                        TextInput::make('check_out')
                            ->label('Check-Out Time')
                            ->disabled()
                            ->visibleOn('edit'),

                        TextInput::make('total_price')
                            ->label('Total Price')
                            ->prefix('रु.')
                            ->disabled()
                            ->visibleOn('edit'),

                        TextInput::make('status')
                            ->label('Status')
                            ->disabled()
                            ->visibleOn('edit'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('vehicle_no')
                    ->label('Vehicle No.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('vehicleType.name')
                    ->label('Type')
                    ->sortable()
                    ->badge(),

                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => NepaliHelper::formatPhone($state)),

                TextColumn::make('check_in')
                    ->label('Check-In')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn (ParkingTicket $record) => $record->check_in
                        ? NepaliHelper::formatBSDateTime($record->check_in)
                        : null),

                TextColumn::make('check_out')
                    ->label('Check-Out')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->description(fn (ParkingTicket $record) => $record->check_out
                        ? NepaliHelper::formatBSDateTime($record->check_out)
                        : null),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(fn (ParkingTicket $record) => $record->duration_for_display)
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_price')
                    ->label('Price')
                    ->money('NPR')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('checkedInBy.name')
                    ->label('Checked In By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ParkingStatus::class),

                SelectFilter::make('vehicle_type_id')
                    ->label('Vehicle Type')
                    ->relationship('vehicleType', 'name'),

                Filter::make('parked_today')
                    ->label('Today Only')
                    ->query(fn (Builder $query): Builder => $query->today()),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('checkout')
                        ->label('Check Out')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Check Out Vehicle')
                        ->modalDescription(fn (ParkingTicket $record) => "Check out vehicle {$record->vehicle_no}? Duration: {$record->duration_for_display}")
                        ->visible(fn (ParkingTicket $record) => $record->isParked())
                        ->action(function (ParkingTicket $record) {
                            $record->checkOut();

                            Notification::make()
                                ->title('Vehicle Checked Out')
                                ->body('Total: रु. '.number_format($record->total_price, 2))
                                ->success()
                                ->send();
                        }),

                    Action::make('print')
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

                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (ParkingTicket $record) => $record->isParked()),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('delete_any_parking::ticket')),
                ]),
            ])
            ->defaultSort('check_in', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParkingTickets::route('/'),
            'create' => Pages\CreateParkingTicket::route('/create'),
            'view' => Pages\ViewParkingTicket::route('/{record}'),
            'edit' => Pages\EditParkingTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['vehicleType', 'checkedInBy', 'checkedOutBy']);
    }
}
