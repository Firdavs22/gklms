<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static ?string $navigationLabel = 'Курсы';
    
    protected static ?string $modelLabel = 'Курс';
    
    protected static ?string $pluralModelLabel = 'Курсы';
    
    protected static ?string $navigationGroup = 'Контент';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => 
                                $set('slug', Str::slug($state))
                            ),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('URL-адрес')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Автоматически генерируется из названия'),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Обложка')
                            ->image()
                            ->disk('public')
                            ->directory('courses')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1280')
                            ->imageResizeTargetHeight('720')
                            ->helperText('Рекомендуемый размер: 1280x720px (16:9). Макс. 5MB. JPG, PNG или WebP.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Цена (₽)')
                            ->numeric()
                            ->default(0)
                            ->prefix('₽')
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_published')
                            ->label('Опубликован')
                            ->default(false)
                            ->helperText('Курс будет виден на сайте'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Обложка')
                    ->disk('public')
                    ->square()
                    ->size(60),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('modules_count')
                    ->label('Модулей')
                    ->counts('modules')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Покупок')
                    ->counts('enrollments')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубликован')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Статус публикации'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_site')
                    ->label('На сайте')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('catalog.show', $record))
                    ->openUrlInNewTab(),
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
            RelationManagers\ModulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
