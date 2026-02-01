<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Models\Lesson;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    
    protected static ?string $navigationLabel = 'Уроки';
    
    protected static ?string $modelLabel = 'Урок';
    
    protected static ?string $pluralModelLabel = 'Уроки';
    
    protected static ?string $navigationGroup = 'Контент';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Урок')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название урока')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('modules')
                            ->label('Модули (где показывать урок)')
                            ->relationship('modules', 'title')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Урок будет отображаться во всех выбранных модулях')
                            ->getOptionLabelFromRecordUsing(fn (Module $record) => 
                                ($record->course?->title ?? 'Без курса') . ' → ' . $record->title
                            ),

                        Forms\Components\Select::make('video_source')
                            ->label('Источник видео')
                            ->options([
                                'url' => '🔗 Ссылка (YouTube, Vimeo, VK)',
                                'kinescope' => '🎬 Kinescope (с защитой DRM)',
                                'yandex_disk' => '📁 Яндекс.Диск (с водяным знаком)',
                            ])
                            ->default('url')
                            ->live(),

                        Forms\Components\TextInput::make('video_url')
                            ->label('Ссылка на видео')
                            ->url()
                            ->placeholder('https://youtube.com/watch?v=...')
                            ->helperText('YouTube, Vimeo, VK или прямая ссылка')
                            ->hidden(fn (Forms\Get $get) => !in_array($get('video_source'), ['url', null, ''])),

                        Forms\Components\TextInput::make('video_url')
                            ->label('Ссылка на Kinescope')
                            ->url()
                            ->placeholder('https://kinescope.io/abc123')
                            ->helperText('Скопируйте ссылку из панели Kinescope. DRM защита включена автоматически.')
                            ->hidden(fn (Forms\Get $get) => $get('video_source') !== 'kinescope'),

                        Forms\Components\TextInput::make('video_path')
                            ->label('Публичная ссылка Яндекс.Диска')
                            ->placeholder('https://disk.yandex.ru/i/xxxxx')
                            ->helperText('Создайте публичную ссылку на файл и вставьте сюда. Видео будет защищено водяным знаком.')
                            ->hidden(fn (Forms\Get $get) => $get('video_source') !== 'yandex_disk'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Содержание')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Содержание урока')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'blockquote',
                                'codeBlock',
                            ]),
                    ]),

                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок по умолчанию')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Опубликован')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('modules.title')
                    ->label('Модули')
                    ->badge()
                    ->separator(', ')
                    ->limitList(2)
                    ->expandableLimitedList(),

                Tables\Columns\IconColumn::make('video_url')
                    ->label('Видео')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasVideo()),

                Tables\Columns\IconColumn::make('assignment')
                    ->label('Задание')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasAssignment()),

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
                    ->label('Опубликован'),
                Tables\Filters\SelectFilter::make('modules')
                    ->label('Модуль')
                    ->relationship('modules', 'title'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Просмотр')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('lessons.preview', $record))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
