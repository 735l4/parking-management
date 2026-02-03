<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ManageGeneralSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Business Settings';

    protected static ?string $title = 'Business Settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.manage-general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);

        $this->form->fill([
            'business_name' => $settings->business_name,
            'pan_number' => $settings->pan_number,
            'address' => $settings->address,
            'phone_number' => $settings->phone_number,
            'logo' => $settings->logo,
        ]);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('page_ManageGeneralSettings') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Form::make([
                    Section::make('Business Information')
                        ->description('Configure your business details that will appear on receipts.')
                        ->schema([
                            TextInput::make('business_name')
                                ->label('Business Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('pan_number')
                                ->label('PAN Number')
                                ->maxLength(20)
                                ->placeholder('Enter your PAN number'),

                            Textarea::make('address')
                                ->label('Address')
                                ->rows(3)
                                ->maxLength(500),

                            TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->tel()
                                ->maxLength(10)
                                ->placeholder('98XXXXXXXX'),

                            FileUpload::make('logo')
                                ->label('Business Logo')
                                ->image()
                                ->directory('logos')
                                ->imageResizeMode('cover')
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeTargetWidth('200')
                                ->imageResizeTargetHeight('200'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save Settings')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(GeneralSettings::class);

        $settings->business_name = $data['business_name'];
        $settings->pan_number = $data['pan_number'] ?? '';
        $settings->address = $data['address'] ?? '';
        $settings->phone_number = $data['phone_number'] ?? '';
        $settings->logo = $data['logo'] ?? null;

        $settings->save();

        Notification::make()
            ->title('Settings Saved')
            ->success()
            ->send();
    }
}
