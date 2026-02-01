<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Enrollment::sum('amount_paid');
        $thisMonthRevenue = Enrollment::whereMonth('enrolled_at', now()->month)
            ->whereYear('enrolled_at', now()->year)
            ->sum('amount_paid');
        
        return [
            Stat::make('Пользователи', User::count())
                ->description('Всего зарегистрировано')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            
            Stat::make('Курсы', Course::count())
                ->description(Course::where('is_published', true)->count() . ' опубликовано')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            
            Stat::make('Покупки', Enrollment::count())
                ->description('Всего продаж')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            
            Stat::make('Выручка', number_format($totalRevenue, 0, ',', ' ') . ' ₽')
                ->description(number_format($thisMonthRevenue, 0, ',', ' ') . ' ₽ за месяц')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
