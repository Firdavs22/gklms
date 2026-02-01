<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Панель управления';
    
    protected static ?string $title = 'Панель управления';
    
    protected static ?int $navigationSort = -2;
    
    public function getHeading(): string
    {
        return 'Панель управления';
    }
    
    public function getSubheading(): ?string
    {
        return 'Добро пожаловать в административную панель';
    }
}
