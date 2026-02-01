<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationLabel = 'Покупки';
    
    protected static ?string $modelLabel = 'Покупка';
    
    protected static ?string $pluralModelLabel = 'Покупки';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о покупке')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('course_id')
                            ->label('Курс')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('payment_id')
                            ->label('ID платежа')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Сумма')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),
                        
                        Forms\Components\Select::make('payment_provider')
                            ->label('Платежная система')
                            ->options([
                                'tilda' => 'Tilda',
                                'yokassa' => 'ЮKassa',
                                'cloudpayments' => 'CloudPayments',
                                'tinkoff' => 'Тинькофф',
                                'manual' => 'Вручную',
                            ])
                            ->default('tilda')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('enrolled_at')
                            ->label('Дата покупки')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('course.title')
                    ->label('Курс')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_provider')
                    ->label('Система')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'tilda' => 'Tilda',
                        'yokassa' => 'ЮKassa',
                        'cloudpayments' => 'CloudPayments',
                        'tinkoff' => 'Тинькофф',
                        'manual' => 'Вручную',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('enrolled_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->label('Курс')
                    ->relationship('course', 'title'),
                
                Tables\Filters\SelectFilter::make('payment_provider')
                    ->label('Платежная система')
                    ->options([
                        'tilda' => 'Tilda',
                        'yokassa' => 'ЮKassa',
                        'cloudpayments' => 'CloudPayments',
                        'tinkoff' => 'Тинькофф',
                        'manual' => 'Вручную',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
