<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Настройки сайта';
    
    protected static ?string $title = 'Настройки сайта';
    
    protected static ?string $navigationGroup = 'Настройки';
    
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => SiteSetting::get('site_name', 'GloboKids Edu'),
            'logo_path' => SiteSetting::get('logo_path'),
            'primary_color' => SiteSetting::get('primary_color', '#7c3aed'),
            'secondary_color' => SiteSetting::get('secondary_color', '#a855f7'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Брендинг')
                    ->description('Настройте внешний вид сайта для пользователей')
                    ->schema([
                        Forms\Components\TextInput::make('site_name')
                            ->label('Название сайта')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Логотип')
                            ->image()
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg'])
                            ->helperText('PNG, SVG или JPG. Рекомендуемая высота: 40-60px'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Цветовая схема')
                    ->description('Эти цвета будут использоваться на страницах для пользователей')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Основной цвет')
                            ->required(),

                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Дополнительный цвет')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Handle logo_path - FileUpload returns array
        $logoPath = $data['logo_path'];
        if (is_array($logoPath)) {
            $logoPath = $logoPath[0] ?? null;
        }

        SiteSetting::set('site_name', $data['site_name']);
        SiteSetting::set('logo_path', $logoPath);
        SiteSetting::set('primary_color', $data['primary_color']);
        SiteSetting::set('secondary_color', $data['secondary_color']);

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
