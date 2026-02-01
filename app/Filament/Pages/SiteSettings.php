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
            'branding_display_type' => SiteSetting::get('branding_display_type', 'name'),
            'primary_color' => SiteSetting::get('primary_color', '#4A91CD'),
            'secondary_color' => SiteSetting::get('secondary_color', '#D0E3F4'),
            'heading_font' => SiteSetting::get('heading_font', 'Inter'),
            'body_font' => SiteSetting::get('body_font', 'Inter'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Брендинг')
                    ->description('Настройте внешний вид сайта для пользователей')
                    ->schema([
                        Forms\Components\Radio::make('branding_display_type')
                            ->label('Что отображать в шапке?')
                            ->options([
                                'name' => 'Только название (текст)',
                                'logo' => 'Только логотип (картинка)',
                                'both' => 'И то, и другое',
                            ])
                            ->required()
                            ->inline(),

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
                            ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg', 'image/webp'])
                            ->helperText('PNG, SVG или JPG. Рекомендуемая высота: 40-60px'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Цветовая схема и шрифты')
                    ->description('Настройте цвета и шрифты для страниц обучения')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Основной цвет')
                            ->required(),

                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Дополнительный цвет')
                            ->required(),

                        Forms\Components\Select::make('heading_font')
                            ->label('Шрифт для заголовков')
                            ->options([
                                'Inter' => 'Inter (Современный, чистый)',
                                'Montserrat' => 'Montserrat (Стильный, геометрический)',
                                'Outfit' => 'Outfit (Премиальный, мягкий)',
                                'Playfair Display' => 'Playfair Display (Классический, с засечками)',
                                'Unbounded' => 'Unbounded (Дерзкий, широкий)',
                            ])
                            ->required(),

                        Forms\Components\Select::make('body_font')
                            ->label('Шрифт для текста')
                            ->options([
                                'Inter' => 'Inter (Универсальный)',
                                'Roboto' => 'Roboto (Технологичный)',
                                'Open Sans' => 'Open Sans (Дружелюбный)',
                                'Nunito' => 'Nunito (Округлый, мягкий)',
                            ])
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

        SiteSetting::set('branding_display_type', $data['branding_display_type']);
        SiteSetting::set('site_name', $data['site_name']);
        SiteSetting::set('logo_path', $logoPath);
        SiteSetting::set('primary_color', $data['primary_color']);
        SiteSetting::set('secondary_color', $data['secondary_color']);
        SiteSetting::set('heading_font', $data['heading_font']);
        SiteSetting::set('body_font', $data['body_font']);

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
