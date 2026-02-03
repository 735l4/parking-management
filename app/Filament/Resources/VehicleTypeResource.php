<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleTypeResource\Pages;
use App\Models\VehicleType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class VehicleTypeResource extends Resource
{
    protected static ?string $model = VehicleType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vehicle Type Details')
                    ->description('Configure the vehicle type and its pricing.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Vehicle Type Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Two Wheeler, Four Wheeler'),

                        TextInput::make('hourly_rate')
                            ->label('Hourly Rate (रु.)')
                            ->required()
                            ->numeric()
                            ->prefix('रु.')
                            ->minValue(0)
                            ->step(0.01),

                        TextInput::make('minimum_charge')
                            ->label('Minimum Charge (रु.)')
                            ->required()
                            ->numeric()
                            ->prefix('रु.')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Charged when parking duration is less than 1 hour'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive vehicle types will not appear in check-in form'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Vehicle Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hourly_rate')
                    ->label('Hourly Rate')
                    ->money('NPR')
                    ->sortable(),

                TextColumn::make('minimum_charge')
                    ->label('Min. Charge')
                    ->money('NPR')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('parking_tickets_count')
                    ->label('Total Tickets')
                    ->counts('parkingTickets')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListVehicleTypes::route('/'),
            'create' => Pages\CreateVehicleType::route('/create'),
            'edit' => Pages\EditVehicleType::route('/{record}/edit'),
        ];
    }
}
